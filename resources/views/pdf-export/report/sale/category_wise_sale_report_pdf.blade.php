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

        <span style="background-color:#57375D; color:#FFFFFF;padding:5px;border-radius:5px">Sale Report (Category Wise)</span>

    </div>
    <table style="width:100%; margin-top:50px" border="1" cellspacing="0" cellpadding="5">

        <tr>
            <td></td>
            <td></td>
            <td>Date:</td>
            <td>{{ $date_range }}</td>
        </tr>
    </table>
    <br>
    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <thead>
            <th class="thead_label">Sale Date</th>
            <th class="thead_label">Book Name</th>
            <th class="thead_label">Publisher Name</th>
            <th class="thead_label">Author Name</th>
            <th class="thead_label">Category Name</th>
            <th class="thead_label">Customer Name</th>
            <th class="thead_label">Amount</th>
        </thead>
        <tbody>
            @php
                $total = 0;
            @endphp
            @foreach ($sales as $key => $sale)
                <tr>
                    <td class="text-center">{{ $sale->sale_date }}</td>
                    <td class="text-center">{{ $sale->book_name }}</td>
                    <td class="text-center">{{ $sale->publisher_name }}</td>
                    <td class="text-center">{{ $sale->author_name }}</td>
                    <td class="text-center">{{ $sale->category_name }}</td>
                    <td class="text-center">{{ $sale->customer_name }}</td>
                    <td class="text-right">{{ number_format($sale->sub_total, 2) }}</td>
                </tr>
                @php
                    $total += $sale->sub_total;
                @endphp
            @endforeach
            <tr>
                <td colspan="6">Payment Total</td>
                <td class="text-right">{{ number_format($total, 2) }}</td>
            </tr>

        </tbody>
    </table>

</body>

</html>
