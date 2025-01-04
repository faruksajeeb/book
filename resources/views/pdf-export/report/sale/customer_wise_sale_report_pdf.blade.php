<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sale Invoice</title>
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
        <div style="width:15%; float:left">
            <img src="{{ public_path('assets/img/logo/logo.png') }}" width="100" height="100" alt="LOGO">
        </div>
        <div style="width:85%; float:right">
            <h1 style="text-align:center">Tritio Matra Publications</h1>
        </div>
    </div>
    <div class="text-center">

        <span style="background-color:#57375D; color:#FFFFFF;padding:5px;border-radius:5px">Customer Wise Sale Report</span>

    </div>
    <table style="width:100%; margin-top:50px" border="1" cellspacing="0" cellpadding="5">

        <tr>
            <td style="width:25%">Customer Name:</td>
            <td style="width:25%" colspan="{{ $customer->customer_name == 'All' ? 2 : 1 }}">{{ $customer->customer_name }}</td>
            <td style="width:25%">Customer Phone:</td>
            <td style="width:25%">{{ $customer->customer_phone }}</td>
        </tr>
        <tr>
            <td>Customer Address:</td>
            <td colspan="{{ $customer->customer_name == 'All' ? 2 : 1 }}">{{ $customer->customer_address }}</td>
            <td>Date:</td>
            <td>{{ $date_range }}</td>
        </tr>
    </table>
    <br>
    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <thead>
            <th class="thead_label">Sale Date</th>

            @if ($customer->customer_name == 'All')
                <th class="thead_label">Customer Name</th>
            @endif

            <th class="thead_label">Total Amount</th>
            <th class="thead_label">Discount Perct.</th>
            <th class="thead_label">Discount Amt.</th>
            <th class="thead_label">Net Amount</th>
            <th class="thead_label">Pay Amount</th>
            <th class="thead_label">Due Amount</th>
        </thead>
        <tbody>
            @php
                $total = 0;
                $discountTotal = 0;
                $netTotal = 0;
                $payTotal = 0;
                $dueTotal = 0;
            @endphp
            @foreach ($sales as $key => $sale)
                <tr>
                    <td class="text-center">{{ $sale->sale_date }}</td>

                    @if ($customer->customer_name == 'All')
                        <td class="text-center">{{ $sale->customer->customer_name }}</td>
                    @endif
                    <td class="text-right">{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="text-center">{{ $sale->discount_percentage }}</td>
                    <td class="text-right">{{ $sale->discount_amount }}</td>
                    <td class="text-right">{{ $sale->net_amount }}</td>
                    <td class="text-right">{{ $sale->pay_amount }}</td>
                    <td class="text-right">{{ $sale->due_amount }}</td>
                </tr>
                @php
                    $total += $sale->payment_amount;
                    $discountTotal += $sale->discount_amount;
                    $netTotal += $sale->net_amount;
                    $payTotal += $sale->pay_amount;
                    $dueTotal += $sale->due_amount;
                @endphp
            @endforeach
            <tr>
                <td colspan="{{ $customer->customer_name == 'All' ? 2 : 1 }}">Payment Total</td>
                
                <td class="text-right">{{ number_format($total, 2) }}</td>
                <td class="text-right"></td>
                <td class="text-right">{{ number_format($discountTotal, 2) }}</td>                
                <td class="text-right">{{ number_format($netTotal, 2) }}</td>
                <td class="text-right">{{ number_format($payTotal, 2) }}</td>
                <td class="text-right">{{ number_format($dueTotal, 2) }}</td>
            </tr>

        </tbody>
    </table>

</body>

</html>
