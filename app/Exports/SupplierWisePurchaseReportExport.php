<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Carbon\Carbon;


class SupplierWisePurchaseReportExport implements FromArray, WithHeadings, Responsable, ShouldAutoSize, WithStyles, WithCustomStartCell
{
    use Exportable;
    public $data;
    public $title;
    public function __construct(object $objData,Object $supplier,String $title,String $dateRange)
    {
        ini_set('max_execution_time', 30*60); // 30 min
        ini_set('memory_limit', '2048M');
        $this->supplier = $supplier;
        $this->data = $objData;
        $this->title =$title;
        $this->dateRange =$dateRange;
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->setCellValue('A1', $this->title);
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->mergeCells('A1:H2');
        $sheet->setCellValue('A3', "Supplier Name");
        $sheet->setCellValue('B3', $this->supplier->supplier_name);
        $sheet->setCellValue('C3', "Supplier Phone");
        $sheet->setCellValue('D3', $this->supplier->supplier_phone);
        $sheet->setCellValue('A4', "Supplier Address");
        $sheet->setCellValue('B4', $this->supplier->supplier_address);
        $sheet->setCellValue('C4', "Date:");
        $sheet->setCellValue('D4',  $this->dateRange );
        $styleArray = [
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => array('rgb' => '000000'),
            ],
            'alignment' => [
                // 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            // 'borders' => [
            //     'outline' => [
            //         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            //         'color' => ['argb' => '000000'],
            //     ],
            // ],
            'fill' => [
                // 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::HILL_GRADIENT_LINEAR,
                // 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::HILL_SOLID,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'FFA0A0A0',
                ],
                'endColor' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($styleArray);
        $headerStyleArray = [
            'font' => [
                'bold' => true,
            ]];
        $sheet->getStyle('A5:H5')->applyFromArray($headerStyleArray);
    }
    public function headings(): array
    {
        return [
            [
                "Purchase Date",
                "supplier Name",
                "Total Amount",
                "Discount",
                "Discount Amount",
                "Net Payment",
                "Pay Amount",
                "Due Amount"
            ]
        ];
    }
    public function startCell(): string
    {
        return 'A5';
    }
    public function array(): array
    {
        $total = 0;
        $discountTotal = 0;
        $netTotal = 0;
        $payTotal = 0;
        $dueTotal = 0;
        $customArray = array();
        foreach ($this->data as $k=>$val) {
            $customArray[] = array(
                $val->purchase_date,
                $val->supplier->supplier_name,
                $val->total_amount,
                $val->discount_percentage,
                $val->discount_amount,
                $val->net_amount,
                $val->pay_amount,
                $val->due_amount,
            );
            $total +=  $val->total_amount;
            $discountTotal += $val->discount_amount;
            $netTotal += $val->net_amount;
            $payTotal += $val->pay_amount;
            $dueTotal += $val->due_amount;
        }
         $data = $customArray;
        $data[] = ['Purchase Total','',$total,'',$discountTotal,$netTotal ,$payTotal, $dueTotal];
        return $data;
    }
}

