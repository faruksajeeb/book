<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Webspice;
use App\Models\Book;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// use Image;

class PurchaseController extends Controller
{
    public $webspice;
    protected $purchase;
    protected $purchases;
    protected $purchaseid;
    public $tableName;

    public function __construct(purchase $purchase, Webspice $webspice)
    {
        $this->webspice = $webspice;
        $this->purchases = $purchase;
        $this->tableName = 'purchases';
        $this->middleware('JWT');
    }

    public function index()
    {
        #permission verfy
        $this->webspice->permissionVerify('purchase.manage');
        try {
            $paginate = request('paginate', 5);
            $searchTerm = request('search', '');

            $sortField = request('sort_field', 'created_at');
            if (!in_array($sortField, [
                'id',
                'purchase_date',
                'total_amount',
                'discount_percentage',
                'discount_amount',
                'vat_percentage',
                'vat_amount',
                'net_amount',
                'pay_amount',
                'due_amount',
                'paid_by',
            ])) {
                $sortField = 'created_at';
            }
            $sortDirection = request('sort_direction', 'created_at');
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }

            $filled = array_filter(request([
                'id',
                'purchase_date',
                'supplier_id',
                'total_amount',
                'discount_percentage',
            ]));

            $purchases = Purchase::with(['supplier'])->when(count($filled) > 0, function ($query) use ($filled) {
                foreach ($filled as $column => $value) {
                    if ($column == 'purchase_date') {
                        $dateExplode = explode(" to ", $value);
                        $startDate = $dateExplode[0];
                        $endDate = $dateExplode[1];
                        $query->whereBetween($column, [$startDate, $endDate]);
                    } else {
                        $query->where($column, 'LIKE', '%' . $value . '%');
                    }

                }

            })->when(request('search', '') != '', function ($query) use ($searchTerm) {
                $query->search(trim($searchTerm));
            })->orderBy($sortField, $sortDirection)->paginate($paginate);

            return response()->json($purchases);
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    public function store(Request $request)
    {
        // dd($request->all());
        #permission verfy
        $this->webspice->permissionVerify('purchase.create');
        $validator = $request->validate(
            [
                'supplier_id' => 'required',
                'purchase_date' => 'required',
                'pay_amount' => 'numeric|min:0', // Ensure pay_amount is a number and greater than or equal to 0
                // 'payment_method' => 'required_if:pay_amount,0', // payment_method is required if pay_amount is greater than 0
                'payment_method' => Rule::requiredIf($request->pay_amount > 0), // payment_method is required if pay_amount is greater than 0
                'payment_description' => Rule::requiredIf($request->pay_amount > 0), // payment_method is required if pay_amount is greater than 0
                'paid_by' => Rule::requiredIf($request->pay_amount > 0), // payment_method is required if pay_amount is greater than 0
                'attach_file' => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            ],
            [
                // 'cart_items.required' => 'The cart must contain at least one item.',
            ]
        );

        try {

            if (($request->cart_items == null || count($request->cart_items) == 0) && ($request->courtesy_cart_items == null || count($request->courtesy_cart_items) <= 0)) {
                return response()->json(
                    [
                        'error' => 'The cart/ courtesy cart must contain at least one item.',
                    ], 401);
            }
            // Begin a database transaction
            \DB::beginTransaction();
            $input = $request->all();

            if ($request->hasFile('attach_file')) {
                // $image = Image::make($request->file('attach_file'));
                // $imageName = time() . '-' . $request->file('attach_file')->getClientOriginalName();

                $destinationPath = 'assets/img/purchase/';

                $file = $request->file('attach_file');
                $filename = time() . '-' . $file->getClientOriginalName();
                $uploadSuccess = $file->move(public_path($destinationPath), $filename);

                // $uploadSuccess = $image->save($destinationPath . $imageName);

                /**
                 * Generate Thumbnail Image Upload on Folder Code
                 */
                // $destinationPathThumbnail = public_path('assets/img/purchase/thumbnail/');
                // $image->resize(50, 50);
                // $image->save($destinationPathThumbnail . $imageName);

                if ($uploadSuccess) {
                    $input['attach_file'] = $filename;
                }
            }
            $input['created_by'] = $this->webspice->getUserId();

            $inserted = Purchase::create($input);
            $purchaseId = $inserted->id;
            if ($purchaseId) {
                // Update the balance of the corresponding supplier
                $supplier = Supplier::find($request->supplier_id);
                if ($supplier) {
                    // Ensure the balance is not negative
                    $newBalance = $supplier->balance + $request->due_amount;
                    if ($newBalance >= 0) {
                        $supplier->balance = $newBalance;
                        $supplier->save();
                    }
                }
                // Insert into supplier payment if pay-amount is grater than 0
                if ($request->pay_amount > 0) {
                    SupplierPayment::create([
                        'supplier_id' => $request->supplier_id,
                        'purchase_id' => $purchaseId,
                        'payment_date' => $request->purchase_date,
                        'payment_amount' => $request->pay_amount,
                        'payment_method' => $request->payment_method,
                        'paid_by' => $request->paid_by,
                        'payment_description' => $request->payment_description,
                        // 'file' => ,
                        'transaction_type' => 'invoice_create',
                        'created_by' => $this->webspice->getUserId(),
                    ]);
                }
            }

            // dd($input['cart_items']);
            # if Cart Items
            if ($request->cart_items != null && count($request->cart_items) > 0) {
                foreach ($input['cart_items'] as $item) {
                    if ($item['quantity'] <= 0) {continue;}
                    PurchaseDetail::create([
                        'purchase_id' => $purchaseId,
                        'book_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'sub_total' => $item['quantity'] * $item['price'],
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                        'vat_percentage' => 0,
                        'vat_amount' => 0,
                        'net_sub_total' => $item['quantity'] * $item['price'],
                        'flag' => 'regular_item',
                    ]);
                    // Update the quantity of the corresponding book
                    $book = Book::find($item['id']);

                    if ($book) {
                        // Ensure the quantity is not negative
                        $newQuantity = $book->stock_quantity + $item['quantity'];
                        if ($newQuantity >= 0) {
                            $book->stock_quantity = $newQuantity;
                            $book->save();
                        } else {
                            // Handle insufficient quantity error
                            return response()->json(['error' => 'Insufficient quantity in stock.']);
                        }
                    }
                }
            }
            if ($request->courtesy_cart_items != null && count($request->courtesy_cart_items) > 0) {
                foreach ($input['courtesy_cart_items'] as $item) {
                    if ($item['courtesy_quantity'] <= 0) {continue;}
                    PurchaseDetail::create([
                        'purchase_id' => $purchaseId,
                        'book_id' => $item['id'],
                        'quantity' => $item['courtesy_quantity'],
                        'unit_price' => $item['unit_price'],
                        'sub_total' => $item['courtesy_quantity'] * $item['unit_price'],
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                        'vat_percentage' => 0,
                        'vat_amount' => 0,
                        'net_sub_total' => $item['courtesy_quantity'] * $item['unit_price'],
                        'flag' => 'courtesy_copy',
                    ]);
                    // Update the quantity of the corresponding book
                    $book = Book::find($item['id']);

                    if ($book) {
                        // Ensure the quantity is not negative
                        $newQuantity = $book->stock_quantity + $item['courtesy_quantity'];
                        if ($newQuantity >= 0) {
                            $book->stock_quantity = $newQuantity;
                            $book->save();
                        } else {
                            // Handle insufficient quantity error
                            return response()->json(['error' => 'Insufficient quantity in stock.']);
                        }
                    }
                }
            }
            // Commit the database transaction
            \DB::commit();

        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            // Handle errors
            \DB::rollback();
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }

        // return redirect()->back();
    }

    public function show($id)
    {
        try {
            $purchase = Purchase::with('supplier')->find($id);
            // $purchaseRegularDetails = PurchaseDetail::with(['book'])->where('purchase_id', $id)->where('flag', 'regular_item')->get();
            $purchaseRegularDetails = PurchaseDetail::leftJoin('books', 'purchase_details.book_id', '=', 'books.id')
                ->select('books.id', 'books.title', 'purchase_details.unit_price as price', 'purchase_details.quantity', 'purchase_details.sub_total')
                ->where('purchase_details.purchase_id', $id)->where('purchase_details.flag', 'regular_item')->get();
            $purchaseCourtesyDetails = PurchaseDetail::leftJoin('books', 'purchase_details.book_id', '=', 'books.id')
                ->select('books.id', 'books.title', 'purchase_details.unit_price', 'purchase_details.quantity as courtesy_quantity', 'purchase_details.sub_total')->where('purchase_id', $id)->where('flag', 'courtesy_copy')->get();

            $paymentInfo = SupplierPayment::where('purchase_id', $id)->get();
            $data = [
                'purchase' => $purchase,
                'purchase_regular_details' => $purchaseRegularDetails,
                'purchase_courtesy_details' => $purchaseCourtesyDetails,
                'payment_details' => $paymentInfo,
            ];
            return $data;
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    public function update(Request $request, $id)
    {

        // dd($request->all());
        // dd($request->file('attach_file'));
        // dd($request->isMethod('put'));
        #permission verfy
        $this->webspice->permissionVerify('purchase.edit');

        # decrypt value
        // $id = $this->webspice->encryptDecrypt('decrypt', $id);

        $request->validate(
            [
                'supplier_id' => 'required',
                'purchase_date' => 'required',
                'pay_amount' => 'numeric|min:0', // Ensure pay_amount is a number and greater than or equal to 0
                // 'payment_method' => 'required_if:pay_amount,0', // payment_method is required if pay_amount is greater than 0
                'payment_method' => Rule::requiredIf($request->pay_amount > 0), // payment_method is required if pay_amount is greater than 0
                'payment_description' => Rule::requiredIf($request->pay_amount > 0), // payment_method is required if pay_amount is greater than 0
                'paid_by' => Rule::requiredIf($request->pay_amount > 0), // payment_method is required if pay_amount is greater than 0
                'attach_file' => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            ]
        );
        if (($request->cart_items == null || count($request->cart_items) == 0) && ($request->courtesy_cart_items == null || count($request->courtesy_cart_items) <= 0)) {
            return response()->json(
                [
                    'error' => 'The cart/ courtesy cart must contain at least one item.',
                ], 401);
        }

        try {
            $input = $request->all();
            if ($request->hasFile('attach_file')) {

                $destinationPath = 'assets/img/purchase/';
                // $uploadSuccess = $image->save($destinationPath . $imageName);

                $file = $request->file('attach_file');
                $filename = time() . '-' . $file->getClientOriginalName();
                $uploadSuccess = $file->move(public_path($destinationPath), $filename);
                if ($uploadSuccess) {
                    //Delete Old File
                    $imgExist = Purchase::where('id', $id)->first();
                    $existingImage = $imgExist->attach_file;
                    if ($existingImage) {

                        if (Storage::disk('local')->exists($destinationPath . $existingImage)) {
                            unlink($destinationPath . $existingImage);
                        }
                    }
                    $input['attach_file'] = $filename;
                }
            }

            $input['updated_by'] = $this->webspice->getUserId();

            // Begin a database transaction
            \DB::beginTransaction();

            $oldInvoice = Purchase::find($id);
            if (!$oldInvoice) {
                // Handle if the invoice does not exist
                \DB::rollBack();
                return response()->json(
                    [
                        'error' => 'Invoice not found',
                    ], 401);
            }

            $previousSupplierID = $oldInvoice->supplier_id;
            $newSupplierID = $request->supplier_id;
            # Check Supplier Changes
            if ($previousSupplierID != $newSupplierID) {

                // Update the previous supplier's balance
                Supplier::where('id', $previousSupplierID)->decrement('balance', $oldInvoice->due_amount);

                // Update the new supplier's balance
                Supplier::where('id', $newSupplierID)->increment('balance', $request->due_amount);

            } else {
                // Calculate the changes in the invoice amount
                $invoiceChanges = $this->calculateInvoiceChanges($oldInvoice, $request);
                # Update the balance of the corresponding supplier
                $supplier = Supplier::find($previousSupplierID);
                if (!$supplier) {
                    // Handle if the supplier does not exist
                    \DB::rollBack();
                    return response()->json(
                        [
                            'error' => 'Supplier not found',
                        ], 401);
                }
                $supplier->balance += $invoiceChanges;
                $supplier->save();
            }

            #  Update the payment history (if exist) of the corresponding supplier

            $supplierPayment = SupplierPayment::where('purchase_id', $id)->latest()->first();

            if ($supplierPayment) {
                if ($request->pay_amount != 0) {

                    $supplierPayment->supplier_id = $request->supplier_id;
                    $supplierPayment->payment_date = $request->purchase_date;
                    $supplierPayment->payment_amount = $request->pay_amount;
                    $supplierPayment->payment_method = $request->payment_method;
                    $supplierPayment->paid_by = $request->paid_by;
                    $supplierPayment->payment_description = $request->payment_description;
                    $supplierPayment->transaction_type = 'invoice_update';
                    $supplierPayment->updated_by = $this->webspice->getUserId();
                    $supplierPayment->save();
                } else {
                    // SupplierPayment::where('id', $supplierPayment->id)->delete();
                    $supplierPayment->delete();
                }

            } else {
                // Insert into supplier payment if pay-amount is grater than 0
                if ($request->pay_amount > 0) {
                    $paymentTransaction = new SupplierPayment([
                        'supplier_id' => $request->supplier_id,
                        'purchase_id' => $id,
                        'payment_date' => $request->purchase_date,
                        'payment_amount' => $request->pay_amount,
                        'payment_method' => $request->payment_method,
                        'paid_by' => $request->paid_by,
                        'payment_description' => $request->payment_description,
                        // 'file' => ,
                        'transaction_type' => 'invoice_update',
                        'created_by' => $this->webspice->getUserId(),
                    ]);
                    $paymentTransaction->save();
                }
            }

            $oldInvoice->update($input);

            $this->updateSaleDetails($request, $id);

            // Commit the database transaction
            \DB::commit();
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            // Handle errors
            \DB::rollback();
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
        // return redirect()->route('purchases.index');
    }

    public function updateSaleDetails($request, $id)
    {

# Get the existing purchase details for the invoice
        $regularItemExistingPurchaseDetails = PurchaseDetail::where('purchase_id', $id)->where('flag', 'regular_item')->get();
        $regularExistingIds = $regularItemExistingPurchaseDetails->pluck('book_id')->toArray();
        # Identify items to remove (existing items not in the update request)
        if (!empty($request->cart_items)) {
            // Use array_column on $items
            $regColumnValues = array_column($request->cart_items, 'id');
        } else {
            // Handle the case where $items is empty or null
            $regColumnValues = [];
        }
        $regularItemsToRemoveBookIds = array_diff($regularExistingIds, $regColumnValues);
        #adjust with coresponding book before remove
        $removeItems = $regularItemExistingPurchaseDetails->whereIn('book_id', $regularItemsToRemoveBookIds);
        foreach ($removeItems as $removeItem) {
            $book = Book::find($removeItem->book_id);
            if ($book) {
                // Ensure the quantity is not negative
                $newQuantity = $book->stock_quantity - $removeItem->quantity;
                if ($newQuantity >= 0) {
                    $book->stock_quantity = $newQuantity;
                    $book->save();
                } else {
                    \DB::rollBack();
                    return response()->json(
                        [
                            'error' => 'Removal Item Stock Quantity Insufficient!',
                        ], 401);
                }
            }
        }
        $regularItemsToRemoveIds = $regularItemExistingPurchaseDetails->whereIn('book_id', $regularItemsToRemoveBookIds)->pluck('id')->toArray();
#   dd($regularItemsToRemoveIds);
        PurchaseDetail::whereIn('id', $regularItemsToRemoveIds)->delete();

        if ($request->cart_items) {
            foreach ($request->cart_items as $detailData) {

                $purchaseDetail = PurchaseDetail::where('purchase_id', $id)->where('flag', 'regular_item')->where('book_id', $detailData['id'])->first();
                //dd($purchaseDetail->toArray());
                $book = Book::find($detailData['id']);

                if (!$purchaseDetail) {
                    // Update the quantity of the corresponding book
                    if ($book) {
                        // Ensure the quantity is not negative
                        $newQuantity = $book->stock_quantity + $detailData['quantity'];
                        if ($newQuantity >= 0) {
                            $book->stock_quantity = $newQuantity;
                            $book->save();
                        }
                    }
                    // If the item doesn't have an ID, it's a new item, so create it
                    PurchaseDetail::create([
                        'purchase_id' => $id,
                        'book_id' => $detailData['id'],
                        'quantity' => $detailData['quantity'],
                        'unit_price' => $detailData['price'],
                        'sub_total' => $detailData['quantity'] * $detailData['price'],
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                        'vat_percentage' => 0,
                        'vat_amount' => 0,
                        'net_sub_total' => $detailData['quantity'] * $detailData['price'],
                        'flag' => 'regular_item',
                    ]);

                } else {
                    // Update the quantity of the corresponding book
                    if ($book) {
                        // Ensure the quantity is not negative
                        $itemQtyChanges = $detailData['quantity'] - $purchaseDetail->quantity;
                        // $newQuantity = $book->stock_quantity + $detailData['quantity'];
                        // if ($newQuantity >= 0) {
                        $book->stock_quantity += $itemQtyChanges;
                        $book->save();
                        // }
                    }
                    // If the item has an ID, it's an existing item, so update it
                    $purchaseDetail->update([
                        'quantity' => $detailData['quantity'],
                        'unit_price' => $detailData['price'],
                        'sub_total' => $detailData['quantity'] * $detailData['price'],
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                        'vat_percentage' => 0,
                        'vat_amount' => 0,
                        'net_sub_total' => $detailData['quantity'] * $detailData['price'],
                    ]);
                }

            }
        }

        # Get the existing purchase details for the invoice
        $courtesyItemExistingPurchaseDetails = PurchaseDetail::where('purchase_id', $id)->where('flag', 'courtesy_copy')->get();
        $courtesyExistingIds = $courtesyItemExistingPurchaseDetails->pluck('book_id')->toArray();
        if (!empty($request->courtesy_cart_items)) {
            // Use array_column on $items
            $columnValues = array_column($request->courtesy_cart_items, 'id');
        } else {
            // Handle the case where $items is empty or null
            $columnValues = [];
        }
        # Identify items to remove (existing items not in the update request)
        $courtesyItemsToRemoveBookIds = array_diff($courtesyExistingIds, $columnValues);

        $removeCourtesyItems = $courtesyItemExistingPurchaseDetails->whereIn('book_id', $courtesyItemsToRemoveBookIds);
        foreach ($removeCourtesyItems as $removeItem) {
            $book = Book::find($removeItem->book_id);
            if ($book) {
                // Ensure the quantity is not negative
                $newQuantity = $book->stock_quantity - $removeItem->quantity;
                if ($newQuantity >= 0) {
                    $book->stock_quantity = $newQuantity;
                    $book->save();
                } else {
                    \DB::rollBack();
                    return response()->json(
                        [
                            'error' => 'Removal Item Stock Quantity Insufficient!',
                        ], 401);
                }
            }
        }
        $courtesyItemsToRemoveIds = $courtesyItemExistingPurchaseDetails->whereIn('book_id', $courtesyItemsToRemoveBookIds)->pluck('id')->toArray();
#       dd($courtesyItemsToRemoveIds);
        PurchaseDetail::whereIn('id', $courtesyItemsToRemoveIds)->delete();
        if ($request->courtesy_cart_items) {
            foreach ($request->courtesy_cart_items as $detailData) {

                $purchaseDetail = PurchaseDetail::where('purchase_id', $id)->where('flag', 'courtesy_copy')->where('book_id', $detailData['id'])->first();
                //dd($purchaseDetail->toArray());

                $book = Book::find($detailData['id']);
                if (!$purchaseDetail) {
                    // Update the quantity of the corresponding book
                    if ($book) {
                        // Ensure the quantity is not negative
                        $newQuantity = $book->stock_quantity + $detailData['courtesy_quantity'];
                        if ($newQuantity >= 0) {
                            $book->stock_quantity = $newQuantity;
                            $book->save();
                        }
                    }
                    // If the item doesn't have an ID, it's a new item, so create it
                    PurchaseDetail::create([
                        'purchase_id' => $id,
                        'book_id' => $detailData['id'],
                        'quantity' => $detailData['courtesy_quantity'],
                        'unit_price' => $detailData['unit_price'],
                        'sub_total' => $detailData['courtesy_quantity'] * $detailData['unit_price'],
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                        'vat_percentage' => 0,
                        'vat_amount' => 0,
                        'net_sub_total' => $detailData['courtesy_quantity'] * $detailData['unit_price'],
                        'flag' => 'courtesy_copy',
                    ]);

                } else {
                    // Update the quantity of the corresponding book
                    if ($book) {
                        $itemQtyChanges = $detailData['courtesy_quantity'] - $purchaseDetail->quantity;
                        $book->stock_quantity += $itemQtyChanges;
                        $book->save();
                        // }
                    }
                    // If the item has an ID, it's an existing item, so update it
                    $purchaseDetail->update([
                        'quantity' => $detailData['courtesy_quantity'],
                        'unit_price' => $detailData['unit_price'],
                        'sub_total' => $detailData['courtesy_quantity'] * $detailData['unit_price'],
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                        'vat_percentage' => 0,
                        'vat_amount' => 0,
                        'net_sub_total' => $detailData['courtesy_quantity'] * $detailData['unit_price'],
                    ]);

                }
            }
        }
    }

    private function calculateInvoiceChanges($oldInvoice, $newInvoiceData)
    {
        $oldTotal = $oldInvoice->due_amount;
        $newTotal = $newInvoiceData->due_amount;
        $invoiceChanges = $newTotal - $oldTotal;

        return $invoiceChanges;
    }
    public function destroy($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('purchase.delete');
        try {
            # decrypt value
            // $id = $this->webspice->encryptDecrypt('decrypt', $id);

            $purchase = $this->purchases->findById($id);
            $purchase->delete();
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
        // return back();
    }

    public function forceDelete($id)
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
        #permission verfy
        $this->webspice->permissionVerify('purchase.force_delete');
        try {
            #decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $purchase = Purchase::withTrashed()->findOrFail($id);
            $purchase->forceDelete();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->back();
    }
    public function restore($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('purchase.restore');
        try {
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $purchase = Purchase::withTrashed()->findOrFail($id);
            $purchase->restore();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        // return redirect()->route('purchases.index', ['status' => 'archived'])->withSuccess(__('User restored successfully.'));
        return redirect()->route('purchases.index');
    }

    public function restoreAll()
    {
        #permission verfy
        $this->webspice->permissionVerify('purchase.restore');
        try {
            $purchases = Purchase::onlyTrashed()->get();
            foreach ($purchases as $purchase) {
                $purchase->restore();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->route('purchases.index');
        // return redirect()->route('purchases.index')->withSuccess(__('All purchases restored successfully.'));
    }

    public function getpurchases()
    {
        $data = Purchase::where('status', 1)->get();
        return response()->json($data);
    }

}
