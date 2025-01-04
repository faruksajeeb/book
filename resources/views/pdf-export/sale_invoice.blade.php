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

        <span class="page_title">Sale Invoice</span>

    </div>
    <table style="width:100%; margin-top:50px" border="1" cellspacing="0" cellpadding="5">
        <tr>
            <td class="border-none" colspan="2">Invoice ID: {{ $sale->id }}</td>
            <td class="border-none text-right" colspan="2">Invoice Date: {{ $sale->sale_date }}</td>
        </tr>
        <tr>
            <td style="width:25%">Customer Name:</td>
            <td style="width:25%">{{ $sale->customer->customer_name }}</td>
            <td style="width:25%">Sale Note:</td>
            <td style="width:25%">{{ $sale->sale_note }}</td>
        </tr>
        <tr>
            <td>Shipping Address:</td>
            <td>{{ $sale->shipping_address }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>
    @if (count($sale_regular_details) > 0)
        <fieldset style="margin-top:30px">
            <legend>Regular Items</legend>
            <table width="100%" border="1" cellspacing="0" cellpadding="5">
                <thead>
                    <th class="thead_label">SL No</th>
                    <th class="thead_label">Book Title</th>
                    <th class="thead_label">Unit Price</th>
                    <th class="thead_label">Quantity</th>
                    <th class="thead_label">Price</th>
                </thead>
                <tbody>
                    @foreach ($sale_regular_details as $key => $regularItem)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td class="text-left">{{ $regularItem->title }}</td>
                            <td class="text-right">{{ number_format($regularItem->price, 2) }}</td>
                            <td class="text-center">{{ $regularItem->quantity }}</td>
                            <td class="text-right">{{ number_format($regularItem->sub_total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class=" text-bold">Total</td>
                        <td class="text-right text-bold">{{ $sale->total_amount }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class=" text-bold">Discount</td>
                        <td class="text-center text-bold">{{ $sale->discount_percentage }}%</td>
                        <td class="text-right text-bold">{{ $sale->discount_amount }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class=" text-bold">Vat</td>
                        <td class="text-center text-bold">{{ $sale->vat_percentage }}%</td>
                        <td class="text-right text-bold">{{ $sale->vat_amount }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class=" text-bold">Shipping Cost</td>
                        <td class="text-right text-bold">{{ $sale->shipping_amount }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class=" text-bold">Net Total</td>
                        <td class="text-right text-bold">{{ $sale->net_amount }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class=" text-bold">Pay Amount</td>
                        <td class="text-right text-bold">{{ $sale->pay_amount }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class=" text-bold">Due Amount</td>
                        <td class="text-right text-bold">{{ $sale->due_amount }}</td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    @endif
    @if (count($sale_courtesy_details) > 0)
        <fieldset style="margin-top:30px">
            <legend>Courtesy Items</legend>
            <table width="100%" border="1" cellspacing="0" cellpadding="5">
                <thead>
                    <th class="thead_label">SL No</th>
                    <th class="thead_label">Book Title</th>
                    <th class="thead_label">Unit Price</th>
                    <th class="thead_label">Quantity</th>
                    <th class="thead_label">Price</th>
                </thead>
                <tbody>
                    @foreach ($sale_courtesy_details as $key => $courtesyItem)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td class="text-left">{{ $courtesyItem->title }}</td>
                            <td class="text-right">{{ number_format($courtesyItem->unit_price, 2) }}</td>
                            <td class="text-center">{{ $courtesyItem->courtesy_quantity }}</td>
                            <td class="text-right">{{ number_format($courtesyItem->sub_total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class=" text-bold">Total</td>
                        <td class="text-right text-bold">{{ $sale->courtesy_total_amount }}</td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    @endif
</body>

</html>
