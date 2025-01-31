<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Webspice;
use App\Models\Book;
use App\Models\ProductVariant;
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
        //dd($request->all());
        #permission verfy
        $this->webspice->permissionVerify('purchase.create');
        $validated = $request->validate(
            [
                'cart_items' => 'array',
                'courtesy_cart_items' => 'array',
                'supplier_id' => 'required',
                'purchase_date' => 'required',
                'pay_amount' => 'numeric|min:0', // Ensure pay_amount is a number and greater than or equal to 0
                // 'payment_method' => 'required_if:pay_amount,0', // payment_method is required if pay_amount is greater than 0
                'payment_method' => Rule::requiredIf($request->pay_amount > 0), // payment_method is required if pay_amount is greater than 0
                'payment_description' => 'nullable|string', // payment_method is required if pay_amount is greater than 0
                'paid_by' => Rule::requiredIf($request->pay_amount > 0), // payment_method is required if pay_amount is greater than 0
                'attach_file' => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            ]
        );

        try {

            // Check if both carts are empty
            if (
                empty($request->cart_items) &&
                empty($request->courtesy_cart_items)
            ) {
                return response()->json(
                    ['error' => 'The cart/courtesy cart must contain at least one item.'],
                    401
                );
            }

            // Begin a database transaction
            \DB::beginTransaction();
            $purchaseData = $request->except(['cart_items', 'courtesy_cart_items']); 

            // Handle file upload
            if ($request->hasFile('attach_file')) {
                $file = $request->file('attach_file');
                $filename = time() . '-' . $file->getClientOriginalName();
                $destinationPath = 'assets/img/purchase/';
                if ($file->move(public_path($destinationPath), $filename)) {
                    $purchaseData['attach_file'] = $filename;
                }
            }
            $purchaseData['created_by'] = $this->webspice->getUserId();
            # INSERT
            $purchase = Purchase::create($purchaseData);
            
            if ($purchase) {
                // Update supplier balance
                if ($request->supplier_id) {
                    $supplier = Supplier::find($request->supplier_id);
                    if ($supplier) {
                        // Ensure the balance is not negative
                        $supplier->balance = max(0, $supplier->balance + $request->due_amount);
                        $supplier->save();
                    }
                }

                // Insert into supplier payment if pay-amount is grater than 0
                if ($request->pay_amount > 0) {
                    SupplierPayment::create([
                        'supplier_id' => $request->supplier_id,
                        'purchase_id' => $purchase->id,
                        'payment_date' => $request->purchase_date,
                        'payment_amount' => $request->pay_amount,
                        'payment_method' => $request->payment_method,
                        'paid_by' => $request->paid_by,
                        'file' => '',
                        'payment_description' => $request->payment_description,
                        'transaction_type' => 'invoice_create',
                        'created_by' => $this->webspice->getUserId(),
                    ]);
                }

                // Process purchase details for both regular and courtesy items
                $this->processPurchaseDetails($purchase->id, $request->cart_items, 'regular_item');
                $this->processPurchaseDetails($purchase->id, $request->courtesy_cart_items, 'courtesy_copy');
            }

            // Commit the database transaction
            \DB::commit();
            return response()->json(['message' => 'Purchase saved successfully!'], 201);
        } catch (Exception $e) {
            \DB::rollback();
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    private function processPurchaseDetails($purchaseId, $items, $flag)
    {
        if (!$items || count($items) === 0) return;

        $purchaseDetails = [];
        $stockUpdates = [];
        foreach ($items as $item) {
            $quantityField = ($flag === 'courtesy_copy') ? 'courtesy_quantity' : 'quantity';
            $quantity = $item[$quantityField] ?? 0;
            if ($quantity <= 0) continue;
            // Determine whether it's a book or a variant
            $variantId = ($item['variant_id']!='no-variant') ? $item['variant_id'] : null; // Assuming variant_id exists in request
            //dd($variantId);
            $bookId = $item['id'];

            $unitPrice = $item['unit_price'] ?? $item['price'];
            
            // Prepare purchase details batch insert
            $purchaseDetails[] = [
                'purchase_id' => $purchaseId,
                'book_id' => $bookId,
                'variant_id' => $variantId, // Storing variant ID if applicable
                'quantity' => $quantity,
                'price' => $unitPrice,
                'sub_total' => $quantity * $unitPrice,
                'discount_percentage' => 0,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'vat_percentage' => 0,
                'vat_amount' => 0,
                'net_sub_total' => $quantity * $unitPrice,
                'flag' => $flag,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Store stock update information
            $stockKey = $variantId ? "variant_{$variantId}" : "book_{$bookId}";
            if (!isset($stockUpdates[$stockKey])) {
                $stockUpdates[$stockKey] = 0;
            }
            $stockUpdates[$stockKey] += $quantity;
        }
        // Bulk insert purchase details
        if (!empty($purchaseDetails)) {
            PurchaseDetail::insert($purchaseDetails);
        }
        // Bulk update stock quantities
        foreach ($stockUpdates as $stockKey => $quantity) {
            if (strpos($stockKey, 'variant_') === 0) {
                $variantId = str_replace('variant_', '', $stockKey);
                ProductVariant::where('id', $variantId)->increment('stock_quantity', $quantity);
            } else {
                $bookId = str_replace('book_', '', $stockKey);
                Book::where('id', $bookId)->increment('stock_quantity', $quantity);
            }
        }
    }

    public function show($id)
    {
        try {
            $purchase = Purchase::with('supplier')->findOrFail($id);

            $purchaseRegularDetails = PurchaseDetail::with([
                'book',
                'variant.attributeOptions.attribute' // Fetch variant attributes
            ])
            ->where('purchase_id', $id)
            ->where('flag', 'regular_item')
            ->get();

            $purchaseCourtesyDetails = PurchaseDetail::with([
                'book',
                'variant.attributeOptions.attribute'
            ])
            ->where('purchase_id', $id)
            ->where('flag', 'courtesy_copy')
            ->get();

            $paymentInfo = SupplierPayment::where('purchase_id', $id)->get();

            return response()->json([
                'purchase' => $purchase,
                'purchase_regular_details' => $purchaseRegularDetails,
                'purchase_courtesy_details' => $purchaseCourtesyDetails,
                'payment_details' => $paymentInfo,
            ]);
        } catch (Exception $e) {
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
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
                        'price' => $detailData['price'],
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
