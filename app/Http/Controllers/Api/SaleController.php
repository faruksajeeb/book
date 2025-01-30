<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Webspice;
use App\Models\Book;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Sale;
use App\Models\SaleDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PDF;
// use Image;

class SaleController extends Controller
{
    public $webspice;
    protected $sale;
    protected $sales;
    protected $saleid;
    public $tableName;

    public function __construct(Sale $sale, Webspice $webspice)
    {
        $this->webspice = $webspice;
        $this->sales = $sale;
        $this->tableName = 'sales';
        $this->middleware('JWT');
    }

    public function index()
    {
        #permission verfy
        $this->webspice->permissionVerify('sale.manage');
        try {
            $paginate = request('paginate', 5);
            $searchTerm = request('search', '');

            $sortField = request('sort_field', 'created_at');
            if (!in_array($sortField, [
                'id',
                'sale_date',
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
                'sale_date',
                'customer_id',
                'total_amount',
                'discount_percentage',
            ]));

            $sales = Sale::with(['customer'])->when(count($filled) > 0, function ($query) use ($filled) {
                foreach ($filled as $column => $value) {
                    if ($column == 'sale_date') {
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

            return response()->json($sales);
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
        
        #permission verfy
        $this->webspice->permissionVerify('sale.create');
        $request->validate(
            [
                'customer_id' => 'required',
                'sale_date' => 'required',
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

                $destinationPath = 'assets/img/sale/';

                $file = $request->file('attach_file');
                $filename = time() . '-' . $file->getClientOriginalName();
                $uploadSuccess = $file->move(public_path($destinationPath), $filename);

                // $uploadSuccess = $image->save($destinationPath . $imageName);

                /**
                 * Generate Thumbnail Image Upload on Folder Code
                 */
                // $destinationPathThumbnail = public_path('assets/img/sale/thumbnail/');
                // $image->resize(50, 50);
                // $image->save($destinationPathThumbnail . $imageName);

                if ($uploadSuccess) {
                    $input['attach_file'] = $filename;
                }
            }
            $input['created_by'] = $this->webspice->getUserId();

            $inserted = Sale::create($input);
            $saleId = $inserted->id;
            if ($saleId) {
                // Update the balance of the corresponding customer
                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    // Ensure the balance is not negative
                    $newBalance = $customer->balance + $request->due_amount;
                    if ($newBalance >= 0) {
                        $customer->balance = $newBalance;
                        $customer->save();
                    }
                }
                // Insert into customer payment if pay-amount is grater than 0
                if ($request->pay_amount > 0) {
                    CustomerPayment::create([
                        'customer_id' => $request->customer_id,
                        'sale_id' => $saleId,
                        'payment_date' => $request->sale_date,
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
                    // Update the quantity of the corresponding book
                    $book = Book::find($item['id']);

                    if ($book) {
                        // Ensure the quantity is not negative
                        $newQuantity = $book->stock_quantity - $item['quantity'];
                        if ($newQuantity >= 0) {
                            $book->stock_quantity = $newQuantity;
                            $book->save();
                        } else {
                            // Handle insufficient quantity error
                            \DB::rollback();
                            return response()->json(['error' => $book->title . ' (Insufficient quantity in stock.)'], 401);
                        }
                    }
                    SaleDetail::create([
                        'sale_id' => $saleId,
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

                }
            }
            if ($request->courtesy_cart_items != null && count($request->courtesy_cart_items) > 0) {
                foreach ($input['courtesy_cart_items'] as $item) {
                    if ($item['courtesy_quantity'] <= 0) {continue;}
                    SaleDetail::create([
                        'sale_id' => $saleId,
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
                        $newQuantity = $book->stock_quantity - $item['courtesy_quantity'];
                        if ($newQuantity >= 0) {
                            $book->stock_quantity = $newQuantity;
                            $book->save();
                        } else {
                            // Handle insufficient quantity error
                            \DB::rollback();
                            return response()->json(['error' => $book->title . ' (Insufficient quantity in stock.)'], 401);
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
            $sale = Sale::with('customer')->find($id);
            // $saleRegularDetails = SaleDetail::with(['book'])->where('sale_id', $id)->where('flag', 'regular_item')->get();
            $saleRegularDetails = SaleDetail::leftJoin('books', 'sale_details.book_id', '=', 'books.id')
                ->select('books.id', 'books.title', 'sale_details.unit_price as price', 'sale_details.quantity', 'sale_details.sub_total')
                ->where('sale_details.sale_id', $id)->where('sale_details.flag', 'regular_item')->get();
            $saleCourtesyDetails = SaleDetail::leftJoin('books', 'sale_details.book_id', '=', 'books.id')
                ->select('books.id', 'books.title', 'sale_details.unit_price', 'sale_details.quantity as courtesy_quantity', 'sale_details.sub_total')->where('sale_id', $id)->where('flag', 'courtesy_copy')->get();

            $paymentInfo = CustomerPayment::where('sale_id', $id)->get();
            $data = [
                'sale' => $sale,
                'sale_regular_details' => $saleRegularDetails,
                'sale_courtesy_details' => $saleCourtesyDetails,
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
        $this->webspice->permissionVerify('sale.edit');

        # decrypt value
        // $id = $this->webspice->encryptDecrypt('decrypt', $id);

        $request->validate(
            [
                'customer_id' => 'required',
                'sale_date' => 'required',
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

            // Begin a database transaction
            \DB::beginTransaction();

            $oldInvoice = Sale::find($id);
            if (!$oldInvoice) {
                // Handle if the invoice does not exist
                \DB::rollBack();
                return response()->json(
                    [
                        'error' => 'Invoice not found',
                    ], 401);
            }

            $previousCustomerID = $oldInvoice->customer_id;
            $newCustomerID = $request->customer_id;
            # Check Customer Changes
            if ($previousCustomerID != $newCustomerID) {

                // Update the previous customer's balance
                Customer::where('id', $previousCustomerID)->decrement('balance', $oldInvoice->due_amount);

                // Update the new customer's balance
                Customer::where('id', $newCustomerID)->increment('balance', $request->due_amount);

            } else {
                // Calculate the changes in the invoice amount
                $invoiceChanges = $this->calculateInvoiceChanges($oldInvoice, $request);
                # Update the balance of the corresponding customer
                $customer = Customer::find($previousCustomerID);
                if (!$customer) {
                    // Handle if the customer does not exist
                    \DB::rollBack();
                    return response()->json(
                        [
                            'error' => 'Customer not found',
                        ], 401);
                }
                $customer->balance += $invoiceChanges;
                $customer->save();
            }

            #  Update the payment history (if exist) of the corresponding customer

            $customerPayment = CustomerPayment::where('sale_id', $id)->latest()->first();

            if ($customerPayment) {
                if ($request->pay_amount != 0) {

                    $customerPayment->customer_id = $request->customer_id;
                    $customerPayment->payment_date = $request->sale_date;
                    $customerPayment->payment_amount = $request->pay_amount;
                    $customerPayment->payment_method = $request->payment_method;
                    $customerPayment->paid_by = $request->paid_by;
                    $customerPayment->payment_description = $request->payment_description;
                    $customerPayment->transaction_type = 'invoice_update';
                    $customerPayment->updated_by = $this->webspice->getUserId();
                    $customerPayment->save();
                } else {
                    // CustomerPayment::where('id', $customerPayment->id)->delete();
                    $customerPayment->delete();
                }

            } else {
                // Insert into customer payment if pay-amount is grater than 0
                if ($request->pay_amount > 0) {
                    $paymentTransaction = new CustomerPayment([
                        'customer_id' => $request->customer_id,
                        'sale_id' => $id,
                        'payment_date' => $request->sale_date,
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

            if ($request->hasFile('attach_file')) {

                $destinationPath = 'assets/img/sale/';
                // $uploadSuccess = $image->save($destinationPath . $imageName);

                $file = $request->file('attach_file');
                $filename = time() . '-' . $file->getClientOriginalName();
                $uploadSuccess = $file->move(public_path($destinationPath), $filename);
                if ($uploadSuccess) {
                    //Delete Old File
                    $imgExist = Sale::where('id', $id)->first();
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
        // return redirect()->route('sales.index');
    }

    public function updateSaleDetails($request, $id)
    {

        # Get the existing sale details for the invoice
        $regularItemExistingSaleDetails = SaleDetail::where('sale_id', $id)->where('flag', 'regular_item')->get();
        $regularExistingIds = $regularItemExistingSaleDetails->pluck('book_id')->toArray();
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
        $removeItems = $regularItemExistingSaleDetails->whereIn('book_id', $regularItemsToRemoveBookIds);
        foreach ($removeItems as $removeItem) {
            $book = Book::find($removeItem->book_id);
            if ($book) {
                // Ensure the quantity is not negative
                $newQuantity = $book->stock_quantity + $removeItem->quantity;
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
        $regularItemsToRemoveIds = $regularItemExistingSaleDetails->whereIn('book_id', $regularItemsToRemoveBookIds)->pluck('id')->toArray();

        SaleDetail::whereIn('id', $regularItemsToRemoveIds)->delete();

        if ($request->cart_items) {
            foreach ($request->cart_items as $detailData) {

                $saleDetail = SaleDetail::where('sale_id', $id)->where('flag', 'regular_item')->where('book_id', $detailData['id'])->first();
                //dd($saleDetail->toArray());
                $book = Book::find($detailData['id']);

                if (!$saleDetail) {
                    // Update the quantity of the corresponding book
                    if ($book) {
                        // Ensure the quantity is not negative
                        $newQuantity = $book->stock_quantity - $detailData['quantity'];
                        if ($newQuantity >= 0) {
                            $book->stock_quantity = $newQuantity;
                            $book->save();
                        } else {
                            \DB::rollBack();
                            return response()->json(
                                [
                                    'error' => 'Removal Item (' . $book->title . ') Stock Quantity Insufficient!',
                                ], 401);
                        }
                    }
                    // If the item doesn't have an ID, it's a new item, so create it
                    SaleDetail::create([
                        'sale_id' => $id,
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
                        $itemQtyChanges = $detailData['quantity'] - $saleDetail->quantity;
                        // $newQuantity = $book->stock_quantity + $detailData['quantity'];
                        // if ($newQuantity >= 0) {
                        $book->stock_quantity -= $itemQtyChanges;
                        $book->save();
                        // }
                    }

                    // If the item has an ID, it's an existing item, so update it
                    $saleDetail->update([
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

        # Get the existing sale details for the invoice
        $courtesyItemExistingSaleDetails = SaleDetail::where('sale_id', $id)->where('flag', 'courtesy_copy')->get();
        $courtesyExistingIds = $courtesyItemExistingSaleDetails->pluck('book_id')->toArray();
        if (!empty($request->courtesy_cart_items)) {
            // Use array_column on $items
            $columnValues = array_column($request->courtesy_cart_items, 'id');
        } else {
            // Handle the case where $items is empty or null
            $columnValues = [];
        }
        # Identify items to remove (existing items not in the update request)
        $courtesyItemsToRemoveBookIds = array_diff($courtesyExistingIds, $columnValues);

        $removeCourtesyItems = $courtesyItemExistingSaleDetails->whereIn('book_id', $courtesyItemsToRemoveBookIds);
        foreach ($removeCourtesyItems as $removeItem) {
            $book = Book::find($removeItem->book_id);
            if ($book) {
                // Ensure the quantity is not negative
                $newQuantity = $book->stock_quantity + $removeItem->quantity;
                if ($newQuantity >= 0) {
                    $book->stock_quantity = $newQuantity;
                    $book->save();
                } else {
                    \DB::rollBack();
                    return response()->json(
                        [
                            'error' => 'Removal Item (' . $book->title . ') Stock Quantity Insufficient!',
                        ], 401);
                }
            }
        }
        $courtesyItemsToRemoveIds = $courtesyItemExistingSaleDetails->whereIn('book_id', $courtesyItemsToRemoveBookIds)->pluck('id')->toArray();

        SaleDetail::whereIn('id', $courtesyItemsToRemoveIds)->delete();
        if ($request->courtesy_cart_items) {
            foreach ($request->courtesy_cart_items as $detailData) {

                $saleDetail = SaleDetail::where('sale_id', $id)->where('flag', 'courtesy_copy')->where('book_id', $detailData['id'])->first();
                //dd($saleDetail->toArray());

                $book = Book::find($detailData['id']);
                if (!$saleDetail) {
                    // Update the quantity of the corresponding book
                    if ($book) {
                        // Ensure the quantity is not negative
                        $newQuantity = $book->stock_quantity - $detailData['courtesy_quantity'];
                        if ($newQuantity >= 0) {
                            $book->stock_quantity = $newQuantity;
                            $book->save();
                        } else {
                            \DB::rollBack();
                            return response()->json(
                                [
                                    'error' => 'Item (' . $book->title . ') Stock Quantity Insufficient!',
                                ], 401);
                        }
                    }
                    // If the item doesn't have an ID, it's a new item, so create it
                    SaleDetail::create([
                        'sale_id' => $id,
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
                        $itemQtyChanges = $detailData['courtesy_quantity'] - $saleDetail->quantity;
                        $book->stock_quantity -= $itemQtyChanges;
                        $book->save();
                        // }
                    }
                    // If the item has an ID, it's an existing item, so update it
                    $saleDetail->update([
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
        $this->webspice->permissionVerify('sale.delete');
        try {
            # decrypt value
            // $id = $this->webspice->encryptDecrypt('decrypt', $id);

            $sale = $this->sales->findById($id);
            $sale->delete();
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
        $this->webspice->permissionVerify('sale.force_delete');
        try {
            #decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $sale = Sale::withTrashed()->findOrFail($id);
            $sale->forceDelete();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->back();
    }
    public function restore($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('sale.restore');
        try {
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $sale = Sale::withTrashed()->findOrFail($id);
            $sale->restore();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        // return redirect()->route('sales.index', ['status' => 'archived'])->withSuccess(__('User restored successfully.'));
        return redirect()->route('sales.index');
    }

    public function restoreAll()
    {
        #permission verfy
        $this->webspice->permissionVerify('sale.restore');
        try {
            $sales = Sale::onlyTrashed()->get();
            foreach ($sales as $sale) {
                $sale->restore();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->route('sales.index');
        // return redirect()->route('sales.index')->withSuccess(__('All sales restored successfully.'));
    }

    public function getsales()
    {
        $data = Sale::where('status', 1)->get();
        return response()->json($data);
    }

    public function exportInvoicePdf($id)
    {
        try {
            // dd('hello');
            ini_set('max_execution_time', 30 * 60); //30 min
            ini_set('memory_limit', '2048M');
            $sale = Sale::with('customer')->find($id);
            // $saleRegularDetails = SaleDetail::with(['book'])->where('sale_id', $id)->where('flag', 'regular_item')->get();
            $saleRegularDetails = SaleDetail::leftJoin('books', 'sale_details.book_id', '=', 'books.id')
                ->select('books.id', 'books.title', 'sale_details.unit_price as price', 'sale_details.quantity', 'sale_details.sub_total')
                ->where('sale_details.sale_id', $id)->where('sale_details.flag', 'regular_item')->get();
            $saleCourtesyDetails = SaleDetail::leftJoin('books', 'sale_details.book_id', '=', 'books.id')
                ->select('books.id', 'books.title', 'sale_details.unit_price', 'sale_details.quantity as courtesy_quantity', 'sale_details.sub_total')->where('sale_id', $id)->where('flag', 'courtesy_copy')->get();

            $paymentInfo = CustomerPayment::where('sale_id', $id)->get();
       
            $pdf = PDF::loadView('pdf-export.sale_invoice', [
                'sale' => $sale,
                'sale_regular_details' => $saleRegularDetails,
                'sale_courtesy_details' => $saleCourtesyDetails,
                'payment_details' => $paymentInfo,
            ]);
            return $pdf->output();
            // return $pdf->download('itsolutionstuff.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }

}
