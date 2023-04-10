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
                </div>
                <div class="container-lg mt-3">
                    <h3>Dispense Report</h3>
                </div>
                <div class="container-lg mt-3 p-2 pt-3 rounded shadow">
                    <div class="container-md d-flex">

                        <form id="today_form" class="m-0">
                            <div class="mx-3">
                                <input type="hidden" name="today" value="1">
                                <button type="submit" class="btn btn-secondary m-0">Today</button>
                            </div>
                        </form>
                        <form id="filter_form" class="m-0">
                            @csrf
                            <div class="d-flex" style="align-items: center;">
                                <label for="date_from">From</label>
                                <input type="date" class="form-control mx-1" name="date_from" id="date_from">

                                <label for="date_to">To</label>
                                <input type="date" class="form-control mx-1" name="date_to" id="date_to" pattern="\d{2}/\d{2}/\d{4}" placeholder="MM/DD/YYYY">
                                <button type="submit" class="btn btn-success">Filter</button>
                            </div>
                        </form>
                    </div>
                    <div class="container-md mt-2 overflow-auto" style="height: 350px;">
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
                var data = JSON.parse(xhr.responseText);
                // Update the table with the new data
                // var transaction_table = document.querySelector('#transaction_table');
                // transaction_table.innerHTML = '';


                console.log(data)
                // for (var i = 0; i < data.length; i++) {
                //     var row = data[i];
                //     transaction_table.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.request_to + "</td><td>" + row.status + "</td><td><a href='/admin/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>";
                // }

                // if (data.length === 0) {
                //     transaction_table.innerHTML += "<tr><td colspan='6'>No pending request...</td></tr>";
                // }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    showDispense();
</script>
@endsection