<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Webspice;
use App\Models\Book;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\DamagedItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PDF;
// use Image;

class DamagedItemController extends Controller
{
    public $webspice;
    protected $damageItem;
    protected $damageItems;
    protected $damageItemId;
    public $tableName;

    public function __construct(DamagedItem $damageItem, Webspice $webspice)
    {
        $this->webspice = $webspice;
        $this->damageItems = $damageItem;
        $this->tableName = 'damage_items';
        $this->middleware('JWT');
    }

    public function index()
    {
        #permission verfy
        $this->webspice->permissionVerify('damage_item.manage');
        try {
            $paginate = request('paginate', 5);
            $searchTerm = request('search', '');

            $sortField = request('sort_field', 'created_at');
            if (!in_array($sortField, [
                'id',
                'damage_date',
                'quantity',
                'title'
            ])) {
                $sortField = 'created_at';
            }
            $sortDirection = request('sort_direction', 'created_at');
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }

            $filled = array_filter(request([
                'id',
                'damage_date',
                'quantity',
                'title'
            ]));

            $damageItems = DamagedItem::with(['book'])->when(count($filled) > 0, function ($query) use ($filled) {
                foreach ($filled as $column => $value) {
                    if ($column == 'damage_date') {
                        if (strpos($value, 'to')) {
                            $dateExplode = explode(" to ", $value);
                            $startDate = $dateExplode[0];
                            $endDate = $dateExplode[1];
                            $query->whereBetween($column, [$startDate, $endDate]);
                        } else {
                            $query->where('damage_date', $value);
                        }
                    } else if ($column == 'title') {
                        $query->search(trim($value));
                    } else {
                        $query->where($column, 'LIKE', '%' . $value . '%');
                    }
                }
            })->when(request('search', '') != '', function ($query) use ($searchTerm) {
                $query->search(trim($searchTerm));
            })->orderBy($sortField, $sortDirection)->paginate($paginate);

            return response()->json($damageItems);
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                401
            );
        }
    }

    public function store(Request $request)
    {
        // dd($request->all());
        #permission verfy
        $this->webspice->permissionVerify('damage_item.create');
        $request->validate(
            [
                'book_id' => 'required',
                'damage_date' => 'required',
                'quantity' => 'numeric|min:1'
            ]
        );
        try {
            // Begin a database transaction
            \DB::beginTransaction();

            $input = $request->all();
            $input['created_by'] = $this->webspice->getUserId();
            $inserted = DamagedItem::create($input);
            $damageItemId = $inserted->id;

            if ($damageItemId) {
                // Update the quantity of the corresponding book
                $book = Book::find($input['book_id']);
                if ($book) {
                    // Ensure the quantity is not negative
                    $newQuantity = $book->stock_quantity - $input['quantity'];
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
            // Commit the database transaction
            \DB::commit();
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            // Handle errors
            \DB::rollback();
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                401
            );
        }
        // return redirect()->back();
    }

    public function show($id)
    {
        try {
            $damageItem = DamagedItem::with(['book'])->find($id);
            return $damageItem;
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                401
            );
        }
    }

    public function update(Request $request, $id)
    {
        #permission verfy
        $this->webspice->permissionVerify('damage_item.edit');

        # decrypt value
        // $id = $this->webspice->encryptDecrypt('decrypt', $id);

        $request->validate(
            [
                'book_id' => 'required',
                'damage_date' => 'required',
                'quantity' => 'numeric|min:1'
            ]
        );
        try {
            // $input = $request->all();
            // Begin a database transaction
            \DB::beginTransaction();
            $oldRecord = DamagedItem::find($id);
            if (!$oldRecord) {
                // Handle if the invoice does not exist
                \DB::rollBack();
                return response()->json(
                    [
                        'error' => 'Invoice not found',
                    ],
                    401
                );
            }
            // Update the quantity of the corresponding book
            if ($oldRecord->book_id == $request->book_id) {
                $book = Book::find($request->book_id);
                if ($book) {
                    $qtyChanges = $request->quantity - $oldRecord->quantity;

                    $newQuantity = $book->stock_quantity - $qtyChanges;
                    if ($newQuantity >= 0) {
                        $book->stock_quantity = $newQuantity;
                        $book->save();
                    } else {
                        // Handle insufficient quantity error
                        \DB::rollback();
                        return response()->json(['error' => $book->title . ' (Insufficient quantity in stock.)'], 401);
                    }
                }
            } else {
                $oldProduct = Book::find($oldRecord->book_id);
                if ($oldProduct) {
                    $newQuantity = $oldProduct->stock_quantity + $oldRecord->quantity;
                    $oldProduct->stock_quantity = $newQuantity;
                    $oldProduct->save();
                }

                $newProduct = Book::find($request->book_id);
                if ($newProduct) {
                    // Ensure the quantity is not negative
                    $newQuantity = $newProduct->stock_quantity - $request->quantity;
                    if ($newQuantity >= 0) {
                        $newProduct->stock_quantity = $newQuantity;
                        $newProduct->save();
                    } else {
                        // Handle insufficient quantity error
                        \DB::rollback();
                        return response()->json(['error' => $newProduct->title . ' (Insufficient quantity in stock.)'], 401);
                    }
                }
            }

            $oldRecord->book_id = $request->book_id;
            $oldRecord->damage_date = $request->damage_date;
            $oldRecord->quantity = $request->quantity;
            $oldRecord->updated_by = $this->webspice->getUserId();
            $oldRecord->save();

            // Commit the database transaction
            \DB::commit();
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            // Handle errors
            \DB::rollback();
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                401
            );
        }
        // return redirect()->route('DamageItems.index');
    }

    public function destroy($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('damage_item.delete');
        try {
            # decrypt value
            // $id = $this->webspice->encryptDecrypt('decrypt', $id);

            $damageItem = $this->damageItems->findById($id);
            $damageItem->delete();
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                401
            );
        }
        // return back();
    }

    public function forceDelete($id)
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
        #permission verfy
        $this->webspice->permissionVerify('damage_item.force_delete');
        try {
            #decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $damageItem = DamagedItem::withTrashed()->findOrFail($id);
            $damageItem->forceDelete();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->back();
    }
    public function restore($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('damage_item.restore');
        try {
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $damageItem = DamagedItem::withTrashed()->findOrFail($id);
            $damageItem->restore();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        // return redirect()->route('DamageItems.index', ['status' => 'archived'])->withSuccess(__('User restored successfully.'));
        return redirect()->route('DamageItems.index');
    }

    public function restoreAll()
    {
        #permission verfy
        $this->webspice->permissionVerify('damage_item.restore');
        try {
            $damageItems = DamagedItem::onlyTrashed()->get();
            foreach ($damageItems as $damageItem) {
                $damageItem->restore();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->route('DamageItems.index');
        // return redirect()->route('DamageItems.index')->withSuccess(__('All DamageItems restored successfully.'));
    }

    public function getDamageItems()
    {
        $data = DamagedItem::where('status', 1)->get();
        return response()->json($data);
    }

    public function exportInvoicePdf($id)
    {
        try {
            // dd('hello');
            ini_set('max_execution_time', 30 * 60); //30 min
            ini_set('memory_limit', '2048M');
            $damageItem = DamagedItem::with('customer')->find($id);
            // $damageItemRegularDetails = DamageItemDetail::with(['book'])->where('DamageItem_id', $id)->where('flag', 'regular_item')->get();
            $damageItemRegularDetails = DamageItemDetail::leftJoin('books', 'DamageItem_details.book_id', '=', 'books.id')
                ->select('books.id', 'books.title', 'DamageItem_details.unit_price as price', 'DamageItem_details.quantity', 'DamageItem_details.sub_total')
                ->where('DamageItem_details.DamageItem_id', $id)->where('DamageItem_details.flag', 'regular_item')->get();
            $damageItemCourtesyDetails = DamageItemDetail::leftJoin('books', 'DamageItem_details.book_id', '=', 'books.id')
                ->select('books.id', 'books.title', 'DamageItem_details.unit_price', 'DamageItem_details.quantity as courtesy_quantity', 'DamageItem_details.sub_total')->where('DamageItem_id', $id)->where('flag', 'courtesy_copy')->get();

            $paymentInfo = CustomerPayment::where('DamageItem_id', $id)->get();

            $pdf = PDF::loadView('pdf-export.DamageItem_invoice', [
                'DamageItem' => $damageItem,
                'DamageItem_regular_details' => $damageItemRegularDetails,
                'DamageItem_courtesy_details' => $damageItemCourtesyDetails,
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
