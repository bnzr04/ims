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
                    <a href="{{ route('admin.stocks') }}" class="btn btn-secondary">Back</a>
                    <hr>
                </div>
                <div class="container-lg mt-3">
                    <h3>Dispense Report</h3>
                </div>
                <div class="container-lg mt-1 p-2 rounded shadow">
                    <div class="container-md d-flex">

                        <form id="today_form" class="m-0">
                            <div class="mx-1">
                                <input type="hidden" name="today" value="1">
                                <button type="submit" class="btn btn-outline-dark m-0">Today</button>
                            </div>
                        </form>
                        <form id="yesterday_form" class="m-0">
                            <div class="mx-1">
                                <input type="hidden" name="yesterday" value="1">
                                <button type="submit" class="btn btn-outline-dark m-0">Yesterday</button>
                            </div>
                        </form>
                        <form id="thisMonth_form" class="m-0">
                            <div class="mx-1">
                                <input type="hidden" name="this-month" value="1">
                                <button type="submit" class="btn btn-outline-dark m-0">This Month</button>
                            </div>
                        </form>
                        <form id="filter_form" class="m-0 mx-2">
                            <div class="d-flex" style="align-items: center;">
                                <label for="date_from">From</label>
                                <input type="date" class="form-control mx-1" name="date_from" id="date_from" required>

                                <label for="date_to">To</label>
                                <input type="date" class="form-control mx-1" name="date_to" id="date_to" pattern="\d{2}/\d{2}/\d{4}" placeholder="MM/DD/YYYY" required>
                                <button type="submit" class="btn btn-outline-success">Filter</button>
                            </div>
                        </form>
                    </div>
                    <div class="container-lg mt-3">
                        <h4 id="table_title"></h4>
                    </div>
                    <div class="container-md mt-1 overflow-auto" style="height: 350px;">
                        <table class="table">
                            <thead class=" bg-success text-white" style="position: sticky;top:0;">
                                <tr>
                                    <th scope="col">Item ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">Total Dispense</th>
                                </tr>
                            </thead>
                            <tbody id="dispense_table">

                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="container-sm d-flex flex-wrap" style="align-items: center;">
                    <form action="{{ route('admin.export-dispense') }}" class="m-0" method="post">
                        @csrf
                        <button type="submit" name="filter" value="today" class="btn btn-outline-success" title="Download Today Report">Today <img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                        <button type="submit" name="filter" value="yesterday" class="btn btn-outline-success" title="Download Yesterday Report">Yesterday <img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                        <button type="submit" name="filter" value="this-month" class="btn btn-outline-success" title="Download This Month Report">This Month <img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                    </form>

                    <form class="m-0 mx-3" action="{{ route('admin.export-dispense') }}" method="post">
                        @csrf
                        <div class="container-lg d-flex" style="width:100%;align-items: center;">
                            <label for="date_from">From</label>
                            <input type="date" class="form-control mx-1" name="date_from" id="date_from" required>

                            <label for="date_to">To</label>
                            <input type="date" class="form-control mx-1" name="date_to" id="date_to" pattern="\d{2}/\d{2}/\d{4}" placeholder="MM/DD/YYYY" required>
                            <button type="submit" title="Download Specific Date Report" class="btn btn-outline-success">Download</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Get today's date
    var today = new Date().toISOString().split('T')[0];

    // Set the maximum value for a date input field to today's date
    document.getElementById("date_from").setAttribute("max", today);
    document.getElementById("date_to").setAttribute("max", today);


    function showDispense() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', "{{ route('admin.get-dispense') }}", true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const today = new Date().toLocaleDateString('en-US', options);

                var data = JSON.parse(xhr.responseText);
                // Update the table with the new data
                var dispense_table = document.querySelector('#dispense_table');
                var table_title = document.querySelector('#table_title');
                table_title.innerHTML = 'TODAY - ' + today;
                dispense_table.innerHTML = '';

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    dispense_table.innerHTML += "<tr><td>" + row.item_id + "</td><td>" + row.name + "</td><td>" + row.description + "</td><td>" + row.category + "</td><td>" + row.unit + "</td><td>" + row.total_dispense + "</td></tr>";
                }

                if (data.length === 0) {
                    dispense_table.innerHTML += "<tr><td colspan='6'>No item dispensed...</td></tr>";
                }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    showDispense();


    /////filter buttons///////

    const todayForm = document.querySelector('#today_form');
    const yesterdayForm = document.querySelector('#yesterday_form');
    const thisMonthForm = document.querySelector('#thisMonth_form');
    const filterForm = document.querySelector('#filter_form');

    /////////Today////////
    todayForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `{{ route('admin.filter-dispense') }}?today=1`);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const today = new Date().toLocaleDateString('en-US', options);

                var data = JSON.parse(xhr.responseText);
                var dispense_table = document.querySelector('#dispense_table');
                var table_title = document.querySelector('#table_title');
                table_title.innerHTML = 'TODAY - ' + today;
                dispense_table.innerHTML = '';
                // console.log(data);

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    dispense_table.innerHTML += "<tr><td>" + row.item_id + "</td><td>" + row.name + "</td><td>" + row.description + "</td><td>" + row.category + "</td><td>" + row.unit + "</td><td>" + row.total_dispense + "</td></tr>";
                }

                if (data.length === 0) {
                    dispense_table.innerHTML += "<tr><td colspan='6'>No item dispensed...</td></tr>";
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

    /////////Yesterday////////
    yesterdayForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `{{ route('admin.filter-dispense') }}?yesterday=1`);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const today = new Date();
                const yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);


                var data = JSON.parse(xhr.responseText);
                var dispense_table = document.querySelector('#dispense_table');
                var table_title = document.querySelector('#table_title');
                table_title.innerHTML = 'YESTERDAY - ' + yesterday.toLocaleDateString('en-US', options);
                dispense_table.innerHTML = '';
                // console.log(data);

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    dispense_table.innerHTML += "<tr><td>" + row.item_id + "</td><td>" + row.name + "</td><td>" + row.description + "</td><td>" + row.category + "</td><td>" + row.unit + "</td><td>" + row.total_dispense + "</td></tr>";
                }

                if (data.length === 0) {
                    dispense_table.innerHTML += "<tr><td colspan='6'>No item dispensed...</td></tr>";
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

    /////////This month////////
    thisMonthForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `{{ route('admin.filter-dispense') }}?this-month=1`);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const monthNames = [
                    'January', 'February', 'March', 'April',
                    'May', 'June', 'July', 'August',
                    'September', 'October', 'November', 'December'
                ];

                const today = new Date();
                const currentMonth = today.getMonth();

                var data = JSON.parse(xhr.responseText);
                var dispense_table = document.querySelector('#dispense_table');
                var table_title = document.querySelector('#table_title');
                table_title.innerHTML = 'THIS MONTH - ' + monthNames[currentMonth];
                dispense_table.innerHTML = '';
                // console.log(data);

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    dispense_table.innerHTML += "<tr><td>" + row.item_id + "</td><td>" + row.name + "</td><td>" + row.description + "</td><td>" + row.category + "</td><td>" + row.unit + "</td><td>" + row.total_dispense + "</td></tr>";
                }

                if (data.length === 0) {
                    dispense_table.innerHTML += "<tr><td colspan='6'>No item dispensed...</td></tr>";
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

    /////////Date Filter////////
    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const fromDateInput = document.querySelector('#date_from').value;
        const toDateInput = document.querySelector('#date_to').value;
        const from = new Date(fromDateInput);
        const to = new Date(toDateInput);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `{{ route('admin.filter-dispense') }}?date_from=${fromDateInput}&date_to=${toDateInput}`);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                var dispense_table = document.querySelector('#dispense_table');
                var table_title = document.querySelector('#table_title');
                table_title.innerHTML = 'Date from: ' + from.toLocaleDateString('en-US') + ' t o: ' + to.toLocaleDateString('en-US');
                dispense_table.innerHTML = '';
                console.log(data);

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    dispense_table.innerHTML += "<tr><td>" + row.item_id + "</td><td>" + row.name + "</td><td>" + row.description + "</td><td>" + row.category + "</td><td>" + row.unit + "</td><td>" + row.total_dispense + "</td></tr>";
                }

                if (data.length === 0) {
                    dispense_table.innerHTML += "<tr><td colspan='6'>No item dispensed...</td></tr>";
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
</script>
@endsection