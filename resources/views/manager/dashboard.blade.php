@extends('layouts.app')
@include('layouts.header')
@section('content')
<div class="container-fluid ">
    <div class="row min-vh-100">
        <div class="col-md-3 col-lg-2 sidebar p-0 bg-dark ">
            @include('layouts.sidebar')
        </div>
        <div class="col-md-9 col-lg-10 p-0">
            <div id="content" class="container-fluid mt-2">
                <h2>Dashboard</h2>
                <div class="container-fluid p-2 d-flex" style="flex-wrap:wrap;">
                    <div class="m-2 p-2" style="width: 200px;height: 120px;background-color:	#005073;color:white">
                        Total Items
                        <hr>
                        <h3 id="total-items"></h3>
                    </div>
                    <div class="m-2 p-2" style="width: 200px;height: 120px;background-color:	#107dac;color:white">
                        Total Items In Stocks
                        <hr>
                        <h3 id="in-stocks"></h3>
                    </div>
                    <div class="m-2 p-2" style="width: 200px;height: 120px;background-color:	#189ad3;color:white">
                        Pending Requests
                        <hr>
                        <h3 id="pending-request"></h3>
                    </div>
                    <div class="m-2 p-2" style="width: 200px;height: 120px;background-color:	#1ebbd7;color:white">
                        Today Completed Requests
                        <hr class="mb-1">
                        <h3 id="completed-today"></h3>
                    </div>
                </div>

                <div class="container-fluid">
                    <iframe src="{{ route('manager.show-pending') }}" frameborder="0" class="border shadow" style="height:250px;width:30%;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function dataUpdate() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', "{{ route('manager.dashboard-display') }}", true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                // console.log(data);
                var totalItemsOutput = document.getElementById("total-items");
                var inStocksOutput = document.getElementById("in-stocks");
                var pendingRequestOutput = document.getElementById("pending-request");
                var completedTodayOutput = document.getElementById("completed-today");
                var adminOutput = document.getElementById("admins");
                var managerOutput = document.getElementById("managers");
                var userOutput = document.getElementById("users");

                //display total items
                totalItemsOutput.innerHTML = data.total_items;
                //display in stocks items
                inStocksOutput.innerHTML = data.in_stocks.count;
                //display pending request number
                pendingRequestOutput.innerHTML = data.pending_request;
                //display today completed request
                completedTodayOutput.innerHTML = data.completed_today;
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    dataUpdate();
    setInterval(dataUpdate, 10000);
</script>
@endsection