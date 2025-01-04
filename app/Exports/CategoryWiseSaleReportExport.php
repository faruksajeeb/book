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


class CategoryWiseSaleReportExport implements FromArray, WithHeadings, Responsable, ShouldAutoSize, WithStyles, WithCustomStartCell
{
    use Exportable;
    public $data;
    public $title;
    public function __construct(object $objData,String $title,String $dateRange)
    {
        ini_set('max_execution_time', 30*60); // 30 min
        ini_set('memory_limit', '2048M');
        $this->data = $objData;
        $this->title =$title;
        $this->dateRange =$dateRange;
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->setCellValue('A1', $this->title);
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->mergeCells('A1:F2');
        $sheet->setCellValue('C4', "Date:");
        $sheet->setCellValue('D4',  $this->dateRange );
        $styleArray = [
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => array('rgb' => '000000'),
            ],
            'alignment' => [
                // 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::FORIZONTAL_RIGHT,
                // 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::FORIZONTAL_CENTER,
            ],
            // 'borders' => [
            //     'outline' => [
            //         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            //         'color' => ['argb' => '000000'],
            //     ],
            // ],
            'fill' => [
                // 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                // 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'FFA0A0A0',
                ],
                'endColor' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($styleArray);
        $headerStyleArray = [
            'font' => [
                'bold' => true,
            ]];
        $sheet->getStyle('A5:F5')->applyFromArray($headerStyleArray);
    }
    public function headings(): array
    {
        return [
            [
                "Sale Date",
                "Book Name",
                "Publisher",
                "Author",
                "Category",
                "Customer",
                "Amount"
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
        $customArray = array();
        foreach ($this->data as $k=>$val) {
            $customArray[] = array(
                $val->sale_date,
                $val->book_name,
                $val->publisher_name,
                $val->author_name,
                $val->category_name,
                $val->customer_name,
                $val->sub_total,
            );
            $total +=  $val->sub_total;
        }
         $data = $customArray;
        $data[] = ['Sale Total','','','','','', $total];
        return $data;
    }
}

