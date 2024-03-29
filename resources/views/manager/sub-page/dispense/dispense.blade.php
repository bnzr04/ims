@extends('layouts.app')
@include('layouts.header')
@section('content')
<style>
    #item-name-link {
        text-decoration: none;
        color: black;
    }

    #item-name-link:hover {
        text-decoration: underline;
        color: blue;
    }
</style>
<div class="container-fluid">
    <div class="row min-vh-100">
        <div class="col-md-3 col-lg-2 sidebar p-0 bg-dark ">
            @include('layouts.sidebar')
        </div>
        <div class="col-md-9 col-lg-10 p-0">
            <div id="content" class="px-2 py-1">
                <div class="container-fluid mt-3">
                    <h3>Dispense Report</h3>
                </div>
                <div class="container-fluid mt-1 p-2 rounded shadow">
                    <div class="container-fluid d-flex">
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
                    <div class="container-fluid m-0 mt-1" style="display: flex;">
                        <form id="petty_cash_form" class="m-0">
                            <div class="mx-1">
                                <input type="hidden" name="today" value="1">
                                <button type="submit" class="btn btn-outline-dark m-0">Petty Cash</button>
                            </div>
                        </form>
                        <form id="donation_form" class="m-0">
                            <div class="mx-1">
                                <input type="hidden" name="today" value="1">
                                <button type="submit" class="btn btn-outline-dark m-0">Donation</button>
                            </div>
                        </form>
                        <form id="lgu_form" class="m-0">
                            <div class="mx-1">
                                <input type="hidden" name="today" value="1">
                                <button type="submit" class="btn btn-outline-dark m-0">LGU</button>
                            </div>
                        </form>
                    </div>
                    <div class="container-fluid mt-3">
                        <h4 id="table_title"></h4>
                    </div>
                    <div class="container-fluid mt-1 overflow-auto border border-white" style="height: 350px;">
                        <table class="table">
                            <thead class=" bg-success text-white" style="position: sticky;top:0;">
                                <tr id="dispense_table_head">

                                </tr>
                            </thead>
                            <tbody id="dispense_table">

                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="container-fluid d-flex flex-wrap" style="align-items: center;">
                    <form action="{{ route('manager.export-dispense') }}" class="m-0" method="post">
                        @csrf
                        <button type="submit" name="filter" value="today" class="btn btn-outline-success" title="Download Today Report">Today <img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                        <button type="submit" name="filter" value="yesterday" class="btn btn-outline-success" title="Download Yesterday Report">Yesterday <img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                        <button type="submit" name="filter" value="this-month" class="btn btn-outline-success" title="Download This Month Report">This Month <img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                    </form>

                    <form class="m-0 mx-3" action="{{ route('manager.export-dispense') }}" method="post">
                        @csrf
                        <div class="container-fluid d-flex" style="width:100%;align-items: center;">
                            <label for="date_from">From</label>
                            <input type="date" class="form-control mx-1" name="date_from" id="date_from1" required>

                            <label for="date_to">To</label>
                            <input type="date" class="form-control mx-1" name="date_to" id="date_to1" pattern="\d{2}/\d{2}/\d{4}" placeholder="MM/DD/YYYY" required>
                            <button type="submit" title="Download Specific Date Report" class="btn btn-outline-success">Download</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div id="viewModal" class="modal fade modal-dialog-scrollable h-100" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modal_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="container-fluid" style="display: flex;flex-direction:column;">
                    <h5 class="modal-title" id="modal_title"></h5>
                    <h6>DISPENSED: <span class="border shadow p-1" id="dispense_count">100</span></h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <table class="table">
                    <thead style="position: sticky;top:0;" class="bg-secondary text-white">
                        <tr class="text-center">
                            <th scope="col">Req Code</th>
                            <th scope="col">Date</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Stock ID</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody id="modal_table_body">

                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

    const modalTitle = document.getElementById('modal_title');
    const modalDispenseCount = document.getElementById('dispense_count');
    const modalTableBody = document.getElementById('modal_table_body');

    function formatDate(date) {
        const options = {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        };
        return date.toLocaleDateString('en-US', options);
    }

    function todayTitle() {
        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const today = new Date().toLocaleDateString('en-US', options);
        currentTableTitle = table_title.innerHTML = 'TODAY - ' + today;
    };

    function yesterdayTitle() {
        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(today.getDate() - 1);

        currentTableTitle = table_title.innerHTML = 'YESTERDAY - ' + yesterday.toLocaleDateString('en-US', options);
    };

    function thisMonthTitle() {
        const monthNames = [
            'January', 'February', 'March', 'April',
            'May', 'June', 'July', 'August',
            'September', 'October', 'November', 'December'
        ];

        const today = new Date();
        const currentMonth = today.getMonth();

        currentTableTitle = table_title.innerHTML = 'THIS MONTH - ' + monthNames[currentMonth];
    }

    var mainUrl = "{{ route('manager.filter-dispense') }}?today=1";

    var filterForFetchUrl = "?today=1";

    var table_title = document.querySelector('#table_title');

    var currentTableTitle;

    todayTitle();

    function showDispense() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', mainUrl);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);

                // Update the table with the new data
                const dispense_table = document.querySelector('#dispense_table');
                const dispense_table_head = document.querySelector('#dispense_table_head');

                dispense_table.innerHTML = '';
                dispense_table_head.innerHTML = '';

                console.log(data);

                if (data.length > 0) {
                    data.forEach(function(row) {
                        modalTitle.innerHTML = row.name + " - " + row.category + " - " + row.unit;
                        var url = window.APP_URL + '/view-dispense/' + row.item_id;
                        var viewButton = "<button type='button' class='btn btn-secondary' data-bs-toggle='modal' data-bs-target='#viewModal' data-item-id='" + row.item_id + "' data-item-name='" + row.name + "' data-item-category='" + row.category + "' data-item-unit='" + row.unit + "' data-total-dispense='" + row.total_dispense + "'>View</button>";

                        if (row.stock_qty == null || row.stock_qty == undefined) {
                            row.stock_qty = "-";
                        }

                        if (row.acquired == null || row.acquired == undefined) {
                            row.acquired = "-";
                        }

                        dispense_table_head.innerHTML =
                            "<th scope='col' class='border'>Item ID</th><th scope='col' class='border'>Name</th><th scope='col' class='border'>Description</th><th scope='col' class='border'>Category</th><th scope='col' class='border'>Unit</th><th scope='col' class='border'>Current Stock</th><th scope='col' class='border'>Input Stock</th><th scope='col' class='border'>Acquired</th><th scope='col' class='border'>Total Dispense</th><th scope='col' class='border'></th>";

                        var stock_batch = window.APP_URL + "/manager/add-to-stocks/" + row.item_id;

                        dispense_table.innerHTML +=
                            "<tr><td class='border'>" + row.item_id + "</td><td class='border'><a id='item-name-link' target='_blank' href='" + stock_batch + "'>" + row.name + "</a></td><td class='border'>" + row.description + "</td><td class='border'>" + row.category + "</td><td class='border'>" + row.unit + "</td><td class='border'>" + row.stock_qty.current_quantity + "</td><td class='border'>" + row.inputStock + "</td><td class='border'>" + row.acquired + "</td><td class='border'>" + row.total_dispense + "</td><td class='border'>" + viewButton + "</td></tr>";
                    });

                    // Add click event listener to all view buttons
                    var viewButtons = document.querySelectorAll("[data-bs-toggle='modal']");
                    viewButtons.forEach(function(button) {
                        button.addEventListener("click", function() {
                            var itemId = this.getAttribute("data-item-id");
                            var itemName = this.getAttribute("data-item-name");
                            var itemCategory = this.getAttribute("data-item-category");
                            var itemUnit = this.getAttribute("data-item-unit");
                            var totalDispense = this.getAttribute("data-total-dispense");

                            // Set the values in the modal
                            modalTitle.innerHTML = itemName + " - " + itemCategory + " - " + itemUnit;
                            modalDispenseCount.innerHTML = totalDispense;

                            //to fetch the records
                            var fetchUrl = window.APP_URL + "/manager/fetch-record/" + itemId + filterForFetchUrl;

                            modalTableBody.innerHTML = "";

                            var req = new XMLHttpRequest();
                            req.open('GET', fetchUrl);
                            req.onload = function() {
                                if (req.status === 200) {
                                    var response = JSON.parse(req.responseText);

                                    // console.log(response);
                                    if (response.length > 0) {
                                        response.forEach(function(row) {
                                            var requestUrl = window.APP_URL + "/manager/view-request/" + row.request_id;
                                            modalTableBody.innerHTML += "<tr class='text-center'><th><a target='_blank' href='" + requestUrl + "'>" + row.request_id + "</a></th><td>" + row.formatDate + "</td><td>" + row.quantity + "</td><td>" + row.stock_id + "</td><td>" + row.status + "</td></tr>";
                                        });
                                    } else {
                                        modalTableBody.innerHTML += "<tr><td colspan='5'>No dispense record...</td></tr>";
                                    }
                                } else {
                                    console.error('Error: ' + req.statusText);
                                }
                            }

                            req.send();
                        });
                    });
                } else {
                    dispense_table.innerHTML += "<tr><td colspan='7'>No item dispensed...</td></tr>";
                }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    /////filter buttons///////

    const todayForm = document.querySelector('#today_form');
    const yesterdayForm = document.querySelector('#yesterday_form');
    const thisMonthForm = document.querySelector('#thisMonth_form');
    const filterForm = document.querySelector('#filter_form');

    const pettyCashForm = document.getElementById('petty_cash_form');
    const donationForm = document.getElementById('donation_form');
    const lguForm = document.getElementById('lgu_form');

    let currentMainUrl = "{{ route('manager.filter-dispense') }}?today=1";
    let currentFilterForFetchUrl = "?today=1";

    /////////Today////////
    todayForm.addEventListener('submit', function(event) {
        event.preventDefault();
        mainUrl = "{{ route('manager.filter-dispense') }}?today=1";
        todayTitle();

        filterForFetchUrl = "?today=1";
        showDispense();
        currentMainUrl = mainUrl;
        currentFilterForFetchUrl = filterForFetchUrl;
    });

    /////////Yesterday////////
    yesterdayForm.addEventListener('submit', function(event) {
        event.preventDefault();
        mainUrl = "{{ route('manager.filter-dispense') }}?yesterday=1";
        yesterdayTitle();

        filterForFetchUrl = "?yesterday=1";
        showDispense();
        currentMainUrl = mainUrl;
        currentFilterForFetchUrl = filterForFetchUrl;
    });

    /////////This month////////
    thisMonthForm.addEventListener('submit', function(event) {
        event.preventDefault();
        mainUrl = "{{ route('manager.filter-dispense') }}?this-month=1";
        thisMonthTitle();

        filterForFetchUrl = "?this-month=1";
        showDispense();
        currentMainUrl = mainUrl;
        currentFilterForFetchUrl = filterForFetchUrl;
    });

    /////////Date Filter////////
    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const fromDateInput = document.querySelector('#date_from').value;
        const toDateInput = document.querySelector('#date_to').value;
        const from = new Date(fromDateInput);
        const to = new Date(toDateInput);

        mainUrl = `{{ route('manager.filter-dispense') }}?date_from=${fromDateInput}&date_to=${toDateInput}`;

        currentTableTitle = table_title.innerHTML = 'Date from: ' + formatDate(from) + ' - ' + formatDate(to);

        filterForFetchUrl = "?filter=1&from=" + fromDateInput + "&to=" + toDateInput;
        showDispense();
        currentMainUrl = mainUrl;
        currentFilterForFetchUrl = filterForFetchUrl;
    });

    pettyCashForm.addEventListener('submit', (event) => {
        event.preventDefault();

        mainUrl = currentMainUrl + "&moa=petty-cash";
        filterForFetchUrl = currentFilterForFetchUrl + "&moa=petty-cash";
        showDispense();

        table_title.innerHTML = currentTableTitle + " - Petty Cash";
    });

    donationForm.addEventListener('submit', (event) => {
        event.preventDefault();

        mainUrl = currentMainUrl + "&moa=donation";
        filterForFetchUrl = currentFilterForFetchUrl + "&moa=donation";
        showDispense();
        table_title.innerHTML = currentTableTitle + " - Donation";
    });

    lguForm.addEventListener('submit', (event) => {
        event.preventDefault();

        mainUrl = currentMainUrl + "&moa=lgu";
        filterForFetchUrl = currentFilterForFetchUrl + "&moa=lgu";
        showDispense();

        table_title.innerHTML = currentTableTitle + " - LGU";
    });

    showDispense();
</script>
@endsection