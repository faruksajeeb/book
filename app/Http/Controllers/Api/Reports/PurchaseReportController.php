<?php

namespace App\Http\Controllers\Api\Reports;
use App\Http\Controllers\Controller;
use App\Lib\Webspice;
use App\Models\supplier;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use MPDF;
use PDF; //DOMPDF
use Dompdf\Options;
use App\Exports\SupplierWisePurchaseReportExport;
use App\Exports\CategoryWisePurchaseReportExport;

class PurchaseReportController extends Controller
{
    public $webspice;
    public function __construct(Webspice $webspice)
    {
        $this->webspice = $webspice;
        $this->middleware('JWT');
    }

    public function supplierWisePurchase(Request $request){
        # permission verfy
        $this->webspice->permissionVerify('report.purchase');
        # data validation
        $request->validate(
            [
                'supplier_id' => 'required',
                'date_range' => 'required',
            ]
        );
        try { 
            $supplier = null;
            $query = Purchase::with(['supplier']);
            if (strpos($request->date_range, 'to')) {  
                $dateExplode = explode(" to ", $request->date_range);
                $startDate = $dateExplode[0];
                $endDate = $dateExplode[1];              
                $query->whereBetween('purchase_date', [$startDate, $endDate]);
            } else {
                $query->where('purchase_date',$request->date_range);
            }
            
            if($request->supplier_id == 'all'){
                $supplier = new \stdClass();
                $supplier->supplier_name = "All";
                $supplier->supplier_phone = "";
                $supplier->supplier_address = "";
            }else{
                $query->where('supplier_id',$request->supplier_id);
                $supplier = supplier::find($request->supplier_id);
            }
            $Purchases = $query->orderBy('purchase_date','DESC')->get();
            if($Purchases->count()==0){
                return response()->json(
                    [
                        'error' => "No record found!",
                    ], 404);
            }
            if($request->btn_type=='excel'){
                // ini_set('max_execution_time', 10 * 60); //10 min
                // ini_set('memory_limit', '2048M');
                return Excel::download(new SupplierWisePurchaseReportExport($Purchases,$supplier,"Purchase",$request->date_range), 'category.xlsx',\Maatwebsite\Excel\Excel::XLSX);
            }else if($request->btn_type=='pdf'){
               
                $pdf = PDF::loadView('pdf-export.report.purchase.supplier_wise_Purchase_report_pdf', [
                    'supplier' => $supplier,
                    'date_range' => $request->date_range,
                    'purchases' => $Purchases
                ]);
                return $pdf->output();
            
            }else if($request->btn_type=='print'){
               $data = [
                'supplier' => $supplier,
                'date_range' => $request->date_range,
                'purchases' => $Purchases
               ];
                // $pdf = MPDF::loadView('pdf-export.report.purchase.supplier_wise_Purchase_report_pdf',$data);
                $mpdf = MPDF::loadView('pdf-export.report.purchase.supplier_wise_Purchase_report_pdf',$data,[],[
                    'orientation' => 'L'
                ]);
                // Change the page orientation to landscape
                //$mpdf->getMpdf()->AddPage('L'); // 'L' stands for landscape, 'P' for portrait

                  // Output the PDF to the browser
                  return $mpdf->stream('purchase_report.pdf');
            }
            return response()->json([
                'report_type' => $request->btn_type,
                'supplier' => $supplier,
                'date_range' => $request->date_range,
                'purchases' => $Purchases,
            ]);

        } catch (Exception $e) {
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    } 
    public function categoryWisePurchase(Request $request){
        # permission verfy
        $this->webspice->permissionVerify('report.purchase');
        # data validation
        $request->validate(
            [
                'category_id' => 'required',
                'date_range' => 'required',
            ]
        );
        try { 
            $query = PurchaseDetail::select(
                'purchases.purchase_date',
                'books.title as book_name',
                'authors.author_name',
                'publishers.publisher_name',
                'categories.category_name',
                'suppliers.supplier_name',
                'purchase_details.quantity',
                'purchase_details.sub_total',
            );
            $query->leftJoin('purchases', 'purchases.id', '=', 'purchase_details.purchase_id');
            $query->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id');
            $query->leftJoin('books', 'books.id', '=', 'purchase_details.book_id');
            $query->leftJoin('authors', 'authors.id', '=', 'books.author_id');
            $query->leftJoin('publishers', 'publishers.id', '=', 'books.publisher_id');
            $query->leftJoin('categories', 'categories.id', '=', 'books.category_id');
            if (strpos($request->date_range, 'to')) {  
                $dateExplode = explode(" to ", $request->date_range);
                $startDate = $dateExplode[0];
                $endDate = $dateExplode[1];              
                $query->whereBetween('purchases.purchase_date', [$startDate, $endDate]);
            } else {
                $query->where('purchases.purchase_date',$request->date_range);
            }
            if($request->supplier_id != 'all'){
                $query->where('purchases.supplier_id',$request->supplier_id);
            }
            if($request->category_id != 'all'){
                $query->where('books.category_id',$request->category_id);
            }
            if($request->author_id != 'all'){
                $query->where('books.author_id',$request->author_id);
            }
            if($request->publisher_id != 'all'){
                $query->where('books.publisher_id',$request->publisher_id);
            }
            $purchases = $query->orderBy('purchases.purchase_date','DESC')->get();
            if($purchases->count()==0){
                return response()->json(
                    [
                        'error' => "No record found!",
                    ], 404);
            }
            if($request->btn_type=='excel'){
                return Excel::download(new CategoryWisePurchaseReportExport($purchases,"Purchase report",$request->date_range), 'category.xlsx',\Maatwebsite\Excel\Excel::XLSX);
            }else if($request->btn_type=='pdf'){
               
                $pdf = PDF::loadView('pdf-export.report.purchase.category_wise_Purchase_report_pdf', [
                    'date_range' => $request->date_range,
                    'purchases' => $purchases
                ]);
                return $pdf->output();
            
            }else if($request->btn_type=='print'){
               
                $pdf = PDF::loadView('pdf-export.report.purchase.category_wise_Purchase_report_pdf', [
                    'date_range' => $request->date_range,
                    'purchases' => $purchases
                ]);
                // $pdf->AddPage('L'); // 'L' for landscape, 'P' for portrait
                  // Output the PDF to the browser
                  return $pdf->stream('purchase_report.pdf');
            }
            return response()->json([
                'report_type' => $request->btn_type,
                'date_range' => $request->date_range,
                'purchases' => $purchases,
            ]);

        } catch (Exception $e) {
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    } 

}
