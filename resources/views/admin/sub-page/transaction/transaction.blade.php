@extends('layouts.app')
@include('layouts.header')
@section('content')
<div class="container-fluid ">
    <div class="row min-vh-100">
        <div class="col-md-3 col-lg-2 sidebar p-0 bg-dark ">
            @include('layouts.sidebar')
        </div>
        <div class="col-md-9 col-lg-10 p-0">
            <div id="content" class="px-2 py-1">
                <div class="container-lg mt-2">
                    <a href="{{ route('admin.requests') }}" class="btn btn-secondary">Back</a>
                </div>
                <div class="container-lg mt-3">
                    <h3>Transactions</h3>
                </div>
                <div class="container-lg mt-3 p-2 pt-3 rounded shadow">
                    <div class="container-md d-flex">

                        <form id="today_form" class="m-0">
                            <div class="mx-1">
                                <input type="hidden" name="today" value="1">
                                <button type="submit" class="btn btn-outline-secondary m-0">Today</button>
                            </div>
                        </form>
                        <form id="yesterday_form" class="m-0">
                            <div class="mx-1">
                                <input type="hidden" name="today" value="1">
                                <button type="submit" class="btn btn-outline-secondary m-0">Yesterday</button>
                            </div>
                        </form>
                        <form id="thisMonth_form" class="m-0">
                            <div class="mx-1">
                                <input type="hidden" name="today" value="1">
                                <button type="submit" class="btn btn-outline-secondary m-0">This Month</button>
                            </div>
                        </form>
                        <form id="filter_form" class="m-0 mx-3">
                            @csrf
                            <!-- <a href="" class="btn btn-secondary" style="letter-spacing:2px;">TODAY</a> -->
                            <div class="d-flex" style="align-items: center;">
                                <label for="date_from">From</label>
                                <input type="date" class="form-control mx-1" name="date_from" id="date_from" required>

                                <label for="date_to">To</label>
                                <input type="date" class="form-control mx-1" name="date_to" id="date_to" pattern="\d{2}/\d{2}/\d{4}" placeholder="MM/DD/YYYY" required>
                                <button type="submit" class="btn btn-outline-success">Filter</button>
                            </div>
                        </form>
                    </div>
                    <div class="container-lg">
                        <h5 id="title" class="mt-2"></h5>
                    </div>
                    <div class="container-md mt-2 overflow-auto" style="height: 350px;">
                        <table class="table">
                            <thead class=" bg-success text-white" style="position: sticky;top:0;">
                                <tr>
                                    <th scope="col">Req ID</th>
                                    <th scope="col">Date-time</th>
                                    <th scope="col">Office</th>
                                    <th scope="col">Patient Name</th>
                                    <th scope="col">Doctor Name</th>
                                    <th scope="col">Request By</th>
                                    <th scope="col">Request To</th>
                                    <th scope="col">Accepted By</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody id="transaction_table">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.APP_URL = "{{ url('') }}";

    // Get today's date
    var today = new Date();

    // Get tomorrow's date
    var tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);

    // Format tomorrow's date as a string
    var tomorrowString = tomorrow.toISOString().split('T')[0];

    // Set the maximum value for a date input field to tomorrow's date
    document.getElementById("date_from").setAttribute("max", tomorrowString);
    document.getElementById("date_to").setAttribute("max", tomorrowString);

    const tableTitle = document.getElementById("title");

    function transaction() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', "{{ route('admin.show-transaction') }}", true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                // Update the table with the new data
                var transaction_table = document.querySelector('#transaction_table');
                transaction_table.innerHTML = '';

                tableTitle.innerHTML = "Today Completed Transactions";

                // console.log(data)
                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    var url = window.APP_URL + '/admin/requested-items/' + row.id;
                    if (row.accepted_by_user_name === null) {
                        row.accepted_by_user_name = "-";
                    }
                    transaction_table.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='" + url + "' class='btn btn-outline-secondary'>View</a></td></tr>";
                }

                if (data.length === 0) {
                    transaction_table.innerHTML += "<tr><td colspan='10'>No request...</td></tr>";
                }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    transaction();


    const todayForm = document.querySelector('#today_form');
    const yesterdayForm = document.querySelector('#yesterday_form');
    const thisMonthForm = document.querySelector('#thisMonth_form');
    const form = document.querySelector('#filter_form');
    const fromDateInput = document.querySelector('#date_from');
    const toDateInput = document.querySelector('#date_to');

    todayForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `{{ route('admin.filter-transaction') }}?today=1`);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                var transaction_table = document.querySelector('#transaction_table');
                transaction_table.innerHTML = '';

                tableTitle.innerHTML = "Today Completed Transactions";
                // console.log(data);

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    var url = window.APP_URL + '/admin/requested-items/' + row.id;
                    if (row.accepted_by_user_name === null) {
                        row.accepted_by_user_name = "-";
                    }
                    transaction_table.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='" + url + "' class='btn btn-outline-secondary'>View</a></td></tr>";
                }

                if (data.length === 0) {
                    transaction_table.innerHTML += "<tr><td colspan='10'>No request...</td></tr>";
                }
            } else {
                var data = JSON.parse(xhr.responseText);
                console.log(data);
            }
        };
        xhr.onerror = function() {
            var data = JSON.parse(xhr.responseText);
            console.log(data);
        };
        xhr.send();
    });


    yesterdayForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `{{ route('admin.filter-transaction') }}?yesterday=1`);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                var transaction_table = document.querySelector('#transaction_table');
                transaction_table.innerHTML = '';

                tableTitle.innerHTML = "Yesterday Completed Transactions";
                // console.log(data);

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    var url = window.APP_URL + '/admin/requested-items/' + row.id;
                    if (row.accepted_by_user_name === null) {
                        row.accepted_by_user_name = "-";
                    }
                    transaction_table.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='" + url + "' class='btn btn-outline-secondary'>View</a></td></tr>";
                }

                if (data.length === 0) {
                    transaction_table.innerHTML += "<tr><td colspan='10'>No request...</td></tr>";
                }
            } else {
                var data = JSON.parse(xhr.responseText);
                console.log(data);
            }
        };
        xhr.onerror = function() {
            var data = JSON.parse(xhr.responseText);
            console.log(data);
        };
        xhr.send();
    });


    thisMonthForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `{{ route('admin.filter-transaction') }}?this-month=1`);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                var transaction_table = document.querySelector('#transaction_table');
                transaction_table.innerHTML = '';

                tableTitle.innerHTML = "This Month Transactions";
                // console.log(data);

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    var url = window.APP_URL + '/admin/requested-items/' + row.id;
                    if (row.accepted_by_user_name === null) {
                        row.accepted_by_user_name = "-";
                    }
                    transaction_table.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='" + url + "' class='btn btn-outline-secondary'>View</a></td></tr>";
                }

                if (data.length === 0) {
                    transaction_table.innerHTML += "<tr><td colspan='10'>No request...</td></tr>";
                }
            } else {
                var data = JSON.parse(xhr.responseText);
                console.log(data);
            }
        };
        xhr.onerror = function() {
            var data = JSON.parse(xhr.responseText);
            console.log(data);
        };
        xhr.send();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        var fromDate = fromDateInput.value;
        var toDate = toDateInput.value;

        var fromDateStr = new Date(fromDate);
        var toDateStr = new Date(toDate);

        var monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        var fromFormattedDate = monthNames[fromDateStr.getMonth()] + " " +
            fromDateStr.getDate() + ", " + fromDateStr.getFullYear();

        var toFormattedDate = monthNames[toDateStr.getMonth()] + " " +
            toDateStr.getDate() + ", " + toDateStr.getFullYear();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `{{ route('admin.filter-transaction') }}?from=${fromDate}&to=${toDate}`);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                var transaction_table = document.querySelector('#transaction_table');
                transaction_table.innerHTML = '';

                tableTitle.innerHTML = "From " + fromFormattedDate + " to " + toFormattedDate;
                // console.log(data);

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    var url = window.APP_URL + '/admin/requested-items/' + row.id;
                    if (row.accepted_by_user_name === null) {
                        row.accepted_by_user_name = "-";
                    }
                    transaction_table.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='" + url + "' class='btn btn-outline-secondary'>View</a></td></tr>";
                }

                if (data.length === 0) {
                    transaction_table.innerHTML += "<tr><td colspan='10'>No request...</td></tr>";
                }
            } else {
                var data = JSON.parse(xhr.responseText);
                console.log(data);
            }
        };
        xhr.onerror = function() {
            var data = JSON.parse(xhr.responseText);
            console.log(data);
        };
        xhr.send();
    });
    // Update the table every 5 seconds
    // setInterval(transaction, 1000);
</script>
@endsection