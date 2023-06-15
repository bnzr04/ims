@extends('layouts.app')
@section('content')
<div class="container-fluid p-0">
    <div class="container-fluid p-2">
        <div class="container-fluid m-0">
            <a href="{{ route('info') }}" class="btn btn-secondary">Back</a>
        </div>
        <hr>
        <div class="container-fluid">
            <h4 style="letter-spacing: 2px;">TRANSACTIONS</h4>
            <div class="container-fluid p-0 mt-3">
                <div class="container-fluid p-0" style="display: flex;flex-wrap:wrap">
                    <form id="request_code_search_form" class="m-0 p-0">
                        <div class="input-group flex-nowrap m-1 p-0 " style="width:100%;max-width:280px;z-index:0;">
                            <input type="number" min="1" class="form-control bg-white" id="request_code_input" placeholder="Search Request Code..." aria-label="search" aria-describedby="addon-wrapping" required>
                            <button type="submit" class="btn btn-outline-success">Search</button>
                        </div>
                    </form>
                </div>
                <div class="container-fluid mt-2" style="display: flex;flex-wrap:wrap;align-items:center">
                    <div class="container-fluid p-0 m-1">
                        <button class="btn btn-outline-success" id="this_day_btn">This day</button>
                        <button class="btn btn-outline-success" id="this_week_btn">This week</button>
                        <button class="btn btn-outline-success" id="this_month_btn">This month</button>
                    </div>
                    <div class="container-fluid p-0 m-1">
                        <form id="filter_form" class="m-0 mx-2">
                            <div class="d-flex" style="align-items: center;width:100%;max-width:420px;z-index:0;">
                                <label for="date_from_input">From</label>
                                <input type="date" class="form-control mx-1" name="from" id="date_from_input" required>

                                <label for="date_to_input">To</label>
                                <input type="date" class="form-control mx-1" name="to" id="date_to_input" pattern="\d{2}/\d{2}/\d{4}" placeholder="MM/DD/YYYY" required>
                                <button type="submit" class="btn btn-outline-success">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="container-fluid p-0 mt-1 border shadow" style="height:70vh;">
                    <div class="container-fluid p-2" style="display:flex;flex-direction:row;text-align:center;align-items:center;">
                        <h4 class="m-0" style="letter-spacing: 2px;" id="table_title"></h4>
                        <h5 id="transaction_count" class="m-0 mx-2"></h5>
                    </div>
                    <div class="container-fluid p-0 border" style="overflow: auto;max-height:60vh;">
                        <table class="table">
                            <thead class="bg-success text-white" style="position: sticky;top:0;">
                                <tr>
                                    <th scope="col" class="border">Req Code/ID</th>
                                    <th scope="col" class="border">Date Created</th>
                                    <th scope="col" class="border">Office</th>
                                    <th scope="col" class="border">Patient Name</th>
                                    <th scope="col" class="border">Doctor Name</th>
                                    <th scope="col" class="border">Requester</th>
                                    <th scope="col" class="border" id="accepter_header">Accepter</th>
                                    <th scope="col" class="border">Status</th>
                                    <th scope="col" class="border">Action</th>
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

    //transaction table
    const transactionTable = document.getElementById("transaction_table");
    const tableTitle = document.getElementById("table_title");

    //filter buttons
    const thisDayBtn = document.getElementById("this_day_btn");
    const thisWeekBtn = document.getElementById("this_week_btn");
    const thisMonthBtn = document.getElementById("this_month_btn");

    //filter form
    const filterForm = document.getElementById("filter_form");

    //search form
    const requestCodeSearchForm = document.getElementById("request_code_search_form");

    //transaction count
    const transactionCount = document.getElementById("transaction_count");

    function formatDate(date) {
        const options = {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Get today's date
    var today = new Date();

    // Get tomorrow's date
    var tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);

    // Format tomorrow's date as a string
    var tomorrowString = tomorrow.toISOString().split('T')[0];

    // Set the maximum value for a date input field to tomorrow's date
    document.getElementById("date_from_input").setAttribute("max", tomorrowString);
    document.getElementById("date_to_input").setAttribute("max", tomorrowString);

    function index() {
        var trans = new XMLHttpRequest();
        trans.open("GET", url, true);
        trans.onload = function() {
            if (trans.status === 200) {
                var data = JSON.parse(trans.responseText);
                transactionTable.innerHTML = "";
                tableTitle.innerHTML = title;

                var transCount = data.length;

                transactionCount.innerHTML = "(" + transCount + ")";
                if (data.length > 0) {
                    // console.log(data.length);
                    data.forEach(function(row) {
                        var requestUrl = window.APP_URL + "/view-request/" + row.id;

                        if (row.accepted_by_user_name === null) {
                            row.accepted_by_user_name = "-";
                        }
                        transactionTable.innerHTML += "<tr><td class='border'>" + row.id + "</td><td class='border'>" + row.formatted_date + "</td><td class='border'>" + row.office +
                            "</td><td class='border'>" + row.patient_name + "</td><td class='border'>" + row.doctor_name + "</td><td class='border'>" + row.request_by + "</td><td class='border'>" +
                            row.accepted_by_user_name + "</td><td class='border'>" + row.status + "</td><td class='border'><a target='_blank' href='" + requestUrl + "' class='btn btn-secondary'>View</a></td></tr>";
                    });
                } else {
                    transactionTable.innerHTML += "<tr><td colspan='9'>No transaction...</td></tr>"
                    console.log(data);
                }
            } else {
                console.error("Error: " + trans.status);
            }
        }
        trans.onerror = function() {
            var data = JSON.parse(trans.responseText);
            console.log(data);
        };
        trans.send();
    }

    var url = "{{ route('filter-all-transaction') }}";
    var title = "Today transaction";
    index();

    thisDayBtn.addEventListener('click', function() {
        url = "{{ route('filter-all-transaction') }}?this-day=1";
        title = "Today transaction";
        requestCodeInputSearch.value = "";
        index();
    });

    thisWeekBtn.addEventListener('click', function() {
        url = "{{ route('filter-all-transaction') }}?this-week=1";
        title = "This week transaction";
        requestCodeInputSearch.value = "";
        index();
    });

    thisMonthBtn.addEventListener('click', function() {
        url = "{{ route('filter-all-transaction') }}?this-month=1";
        title = "This month transaction";
        requestCodeInputSearch.value = "";
        index();
    });

    filterForm.addEventListener('submit', function(event) {
        event.preventDefault();

        //form input
        const dateFromInput = document.querySelector("#date_from_input");
        const dateToInput = document.querySelector("#date_to_input");

        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;
        const from = new Date(dateFrom);
        const to = new Date(dateTo);

        url = `{{ route('filter-all-transaction') }}?filter=1&from=${dateFrom}&to=${dateTo}`;
        title = formatDate(from) + " - " + formatDate(to) + " transaction";
        requestCodeInputSearch.value = "";
        index();
    });

    const requestCodeInputSearch = document.getElementById("request_code_input");

    requestCodeSearchForm.addEventListener('submit', function(event) {
        event.preventDefault();

        url = `{{ route('search-transaction') }}?req-code=${requestCodeInputSearch.value}`;
        title = "Request Code: " + requestCodeInputSearch.value;
        index();
    });
</script>
@endsection