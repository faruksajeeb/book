<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchase Report</title>
    <!-- <link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet"> -->
    <style>
            .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .border-none {
            border: none;
        }

        .thead_label {
            /* font-weight: bold!important; */
            background-color: #434141;
            color: #FFFFFF;
            padding: 5px;
        }

        table,
        td,
        th {
            padding: 3px;
            border: 1px dotted #CCCCCC;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        span.report-name{
            display: inline;
            background-color:#57375D; 
            color:#FFFFFF; 
            border-radius:5px;   
            font-size:15px;  
            padding: 5px;   
        }
    </style>
</head>

<body>
    <div>
        <div style="width:20%; float:left; height:100px; vertical-align:middle">
            <img src="{{ public_path('assets/img/logo/logo.png') }}" width="100%" height="100" alt="LOGO">
        </div>
        <div style="width:80%; float:right; height:100px; padding:10px !important; vertical-align:middle">
            <h1 style="text-align:center;">Tritio Matra Publications</h1>
            <div class="text-center">
                <span class="report-name">Supplier Wise Purchase Report</span>
            </div>
        </div>
    </div>

    <table style="margin-top:30px; ">

        <tr>
            <td style="width:25%">Supplier Name:</td>
            <td style="width:25%" colspan="{{ $supplier->supplier_name == 'All' ? 2 : 1 }}">{{ $supplier->supplier_name }}</td>
            <td style="width:25%">Supplier Phone:</td>
            <td style="width:25%">{{ $supplier->supplier_phone }}</td>
        </tr>
        <tr>
            <td>Supplier Address:</td>
            <td colspan="{{ $supplier->supplier_name == 'All' ? 2 : 1 }}">{{ $supplier->supplier_address }}</td>
            <td>Date:</td>
            <td>{{ $date_range }}</td>
        </tr>
    </table>
    <br>
    <table>
        <tr>
            <th class="thead_label text-bold">Purchase Date</th>

            @if ($supplier->supplier_name == 'All')
            <th class="thead_label text-bold">supplier Name</th>
            @endif

            <th class="thead_label text-bold">Total Amount</th>
            <th class="thead_label text-bold">Discount Perct.</th>
            <th class="thead_label text-bold">Discount Amt.</th>
            <th class="thead_label text-bold">Net Amount</th>
            <th class="thead_label text-bold">Pay Amount</th>
            <th class="thead_label text-bold">Due Amount</th>
        </tr>
        <tbody>
            @php
            $total = 0;
            $discountTotal = 0;
            $netTotal = 0;
            $payTotal = 0;
            $dueTotal = 0;
            @endphp
            @foreach ($purchases as $key => $purchase)
            <tr>
                <td class="text-center">{{ $purchase->purchase_date }}</td>

                @if ($supplier->supplier_name == 'All')
                <td class="text-center">{{ $purchase->supplier->supplier_name }}</td>
                @endif
                <td class="text-right">{{ number_format($purchase->total_amount, 2) }}</td>
                <td class="text-center">{{ $purchase->discount_percentage }}</td>
                <td class="text-right">{{ $purchase->discount_amount }}</td>
                <td class="text-right">{{ $purchase->net_amount }}</td>
                <td class="text-right">{{ $purchase->pay_amount }}</td>
                <td class="text-right">{{ $purchase->due_amount }}</td>
            </tr>
            @php
            $total += $purchase->payment_amount;
            $discountTotal += $purchase->discount_amount;
            $netTotal += $purchase->net_amount;
            $payTotal += $purchase->pay_amount;
            $dueTotal += $purchase->due_amount;
            @endphp
            @endforeach
            <tr>
                <td class=" text-bold"colspan="{{ $supplier->supplier_name == 'All' ? 2 : 1 }}">Payment Total</td>

                <td class="text-right text-bold">{{ number_format($total, 2) }}</td>
                <td class="text-right text-bold"></td>
                <td class="text-right text-bold">{{ number_format($discountTotal, 2) }}</td>
                <td class="text-right text-bold">{{ number_format($netTotal, 2) }}</td>
                <td class="text-right text-bold">{{ number_format($payTotal, 2) }}</td>
                <td class="text-right text-bold">{{ number_format($dueTotal, 2) }}</td>
            </tr>

        </tbody>
    </table>

</body>

</html>