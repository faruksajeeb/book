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

        <span style="background-color:#57375D; color:#FFFFFF;padding:5px;border-radius:5px">Customer Payment
            History</span>

    </div>
    <table style="width:100%; margin-top:50px" border="1" cellspacing="0" cellpadding="5">

        <tr>
            <td>Customer Name:</td>
            <td colspan="{{ $customer->customer_name == 'All' ? 2 : 1 }}">{{ $customer->customer_name }}</td>
            <td>Customer Phone:</td>
            <td>{{ $customer->customer_phone }}</td>
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
            <th class="thead_label">Payment Date</th>

            @if ($customer->customer_name == 'All')
                <th class="thead_label">Customer Name</th>
            @endif

            <th class="thead_label">Payment Method</th>
            <th class="thead_label">Payment Description</th>
            <th class="thead_label">Paid By</th>
            <th class="thead_label">Amount</th>
        </thead>
        <tbody>
            @php
                $total = 0;
            @endphp
            @foreach ($customer_payments as $key => $customer_payment)
                <tr>
                    <td class="text-center">{{ $customer_payment->payment_date }}</td>

                    @if ($customer->customer_name == 'All')
                        <td class="text-center">{{ $customer_payment->customer->customer_name }}</td>
                    @endif
                    <td class="text-center">{{ $customer_payment->paymentmethod->name }}</td>
                    <td class="text-left">{{ $customer_payment->payment_description }}</td>
                    <td class="text-left">{{ $customer_payment->paid_by }}</td>
                    <td class="text-right">{{ number_format($customer_payment->payment_amount, 2) }}</td>
                </tr>
                @php
                    $total += $customer_payment->payment_amount;
                @endphp
            @endforeach
            <tr>
                <td colspan="{{ $customer->customer_name == 'All' ? 5 : 4 }}">Payment Total</td>
                <td class="text-right">{{ number_format($total, 2) }}</td>
            </tr>

        </tbody>
    </table>

</body>

</html>
