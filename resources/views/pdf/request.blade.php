<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Request</title>

    <style>
        @media print {
            @page {
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
            }

            body * {
                visibility: hidden;
            }

            .main-container,
            .main-container * {
                visibility: visible;
            }

        }

        * {
            margin: 5px;
            font-family: 'Courier New', Courier, monospace;
        }

        .main-container {
            width: 385px;
            height: 520px;
            border: #000 1px solid;
            /* float: right; */
        }

        .header {
            display: flex;
            flex-direction: row;
            align-items: center;
            /* border: #000 1px solid; */
        }

        .header img {
            /* border: #000 1px solid; */
            display: flex;
            margin: 0 10px;
            width: 54px;
        }

        .header-text {
            /* border: #000 1px solid; */
            font-size: 8px;
            line-height: 5px;
            margin: 0 auto;
            text-align: center;
        }

        .header-text b {
            margin: 0;
            line-height: 7px;
        }

        .title {
            margin: 0;
            text-align: center;
            letter-spacing: 2px;
            background-color: blue;
            /* border: #000 1px solid; */
        }

        .title h5 {
            margin: 0;
            color: #fff;
            /* text-align: center; */
        }

        .request {
            display: inline;
            margin-top: 0;
            text-align: center;
            /* border: #000 1px solid; */
            font-size: 10px;
            float: right;
        }

        .request span {
            margin: 0;
            font-weight: bold;
            color: red;
        }

        .patient-details {
            margin: 0 auto;
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-wrap: nowrap;
            /* border: #000 1px solid; */
            font-size: 10px;
        }

        .patient-details p>span {
            text-decoration: underline;
        }

        .other-details {
            margin: 0 auto;
            width: 100%;
            max-width: 400px;
            text-align: center;
            display: flex;
            /* border: #000 1px solid; */
            font-size: 10px;
        }

        .other-details p>span {
            text-decoration: underline;
        }

        table {
            margin: 2px auto;
            margin-bottom: 5px;
            border: #000 1px solid;
            font-size: 8px;
            border-collapse: collapse;
            max-width: 98%;
        }

        tr {
            margin: 0;
            padding: 0;
            /* border: #000 1px solid; */
            text-align: center;
        }

        td,
        th {
            border: #000 1px solid;
            margin: 0;
            padding: 0 10px;
            /* width: fit-content; */
        }

        #total-amount {
            padding: 2px;
            text-align: end;
            font-weight: 700;
        }

        .sign-table td {
            height: 30px;
            vertical-align: bottom;
            font-weight: bold;
        }

        .items-table #total-amount-value {
            font-weight: bolder;
            font-size: 9px;
        }
    </style>

</head>

<body>
    <div class="main-container">
        <div class="header">
            <img src="{{asset('logos/San_Pedro_City.png')}}" style="float:left;margin-right:7px">
            <div class="header-text">
                <p>Republic of the Philippines</p>
                <p>Province of Laguna</p>
                <p><b>CITY OF SAN PEDRO</b></p>
                <p><b>SAN PEDRO JOSE L. AMANTE EMERGENCY HOSPITAL</b></p>
                <p>Brgy. Sto. Niño, San Pedro, Laguna</p>
            </div>
            <img src="{{asset('logos/amante-logo.png')}}" style="float:right">
        </div>

        <div class="title">
            <h5>PHARMACY CHARGE SLIP</h5>
        </div>

        <div class="request">
            <p><strong>REQUEST CODE: </strong><span>{{ $request->id }}</span></p>
        </div>

        <div class="patient-details">
            <p>PATIENT NAME:<span>{{ $request->patient_name }}</span></p>
            <p>AGE:<span>0</span></p>
            <p>GENDER:<span>-</span></p>
        </div>

        <div class="other-details">
            <p>WARD/ROOM:<span>{{ $request->office }}</span></p>
            <p>DATE & TIME:<span id="todayDateAndTime"></span></p>
        </div>

        <table class="sign-table">
            <tr>
                <th>Prescribing Physician/Nurse</th>
                <th>Received By (Admission)</th>
                <th>Issued/Charged By: (Pharmacist)</th>
            </tr>
            <tr>
                <td>{{ $request->doctor_name }}</td>
                <td></td>
                <td>{{ Auth::user()->name }}</td>
            </tr>
        </table>

        <table class="items-table">
            <tr>
                <td colspan="5" id="total-amount">Total Amount</td>
                <td id="total-amount-value">{{ $total_amount }}</td>
            </tr>
            <tr>
                <th>ID</th>
                <th>QTY</th>
                <th>UNIT</th>
                <th>DRUG/MEDICAL SUPPLY</th>
                <th>UNIT PRICE</th>
                <th>AMOUNT</th>

            </tr>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->item_id }}</td>
                <td id="quantity">{{ $item->quantity }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ is_null($item->price) ? "-" : $item->price }}</td>
                <td id="amount">{{ $item->amount }}</td>
                <td>{{ $item->remaining }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <button class="" onclick="printReq()">PRINT</button>

    <script>
        function printReq() {

            window.print();
            window.close();
        }

        function dateAndTime() {
            const todayDateAndTimeOutput = document.getElementById('todayDateAndTime');

            // create a new Date object
            const today = new Date();

            // get the month name and convert it to uppercase
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
            ];
            const month = monthNames[today.getMonth()].toUpperCase();

            // get the day and add a leading zero if necessary
            const day = ("0" + today.getDate()).slice(-2);

            // get the year
            const year = today.getFullYear();

            // get the hours in 12-hour format
            let hours = today.getHours();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours %= 12;
            hours = hours ? hours : 12;

            // get the minutes and add a leading zero if necessary
            const minutes = ("0" + today.getMinutes()).slice(-2);

            // combine the date and time components into a string
            const formattedDateTime = `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;

            // output the formatted date and time
            todayDateAndTimeOutput.innerHTML = formattedDateTime;
        }

        setInterval(function() {
            dateAndTime();
        }, 1000);
    </script>
</body>

</html>