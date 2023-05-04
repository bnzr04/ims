@php
use Illuminate\Support\Facades\Session
@endphp
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
                <div class="container-lg">
                    <h2>Requests</h2>
                </div>
                <div class="container-lg pt-3 d-flex">
                    <form id="pending_form">
                        <button class="btn btn-outline-dark mx-1" id="pending">Pending (<span id="pending-count"></span>)</button>
                    </form>
                    <form id="accepted_form">
                        <button class="btn btn-outline-dark mx-1" id="accepted">Accepted (<span id="accepted-count"></span>)</button>
                    </form>
                    <form id="delivered_form">
                        <button class="btn btn-outline-dark mx-1" id="delivered">Delivered (<span id="delivered-count"></span>)</button>
                    </form>
                </div>
                <div class="container-lg mt-1 p-2 border rounded shadow">
                    <div class="container-md">
                        <h5 id="title"></h5>
                    </div>
                    <div class="container-md overflow-auto" style="height: 350px;">
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
                            <tbody id="request_table">

                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                @if(session('success'))
                <div class="alert alert-success" id="alert">
                    {{ session('success') }}
                </div>
                @endif

                <div class="container-lg">
                    <a href="{{ route('manager.transaction') }}" class="btn btn-secondary">Completed Transactions</a>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    const pendingForm = document.querySelector("#pending_form");
    const acceptedForm = document.querySelector("#accepted_form");
    const deliveredForm = document.querySelector("#delivered_form");

    const pendingCountOutput = document.getElementById('pending-count');
    const acceptedCountOutput = document.getElementById('accepted-count');
    const deliveredCountOutput = document.getElementById('delivered-count');

    const title = document.getElementById("title");
    const requestTbody = document.querySelector('#request_table');

    var pendingInterval = setInterval(pendingUpdate, 1000);
    var acceptedInterval;
    var deliveredInterval;

    function pendingCount() {
        setInterval(function() {
            $.ajax({
                url: "{{ route('manager.show-pending-requests') }}",
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    // console.log(data);
                    pendingCountOutput.innerHTML = data.pendingCount;
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + xhr.status);
                }
            });
        }, 1000);
    }

    function acceptedCount() {
        setInterval(function() {
            $.ajax({
                url: "{{ route('manager.show-accepted-requests') }}",
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    // console.log(data.acceptedCount);
                    acceptedCountOutput.innerHTML = data.acceptedCount;
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + xhr.status);
                }
            });
        }, 1000);
    }

    function deliveredCount() {
        setInterval(function() {
            $.ajax({
                url: "{{ route('manager.show-delivered-requests') }}",
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    // console.log(data.deliveredCount);
                    deliveredCountOutput.innerHTML = data.deliveredCount;
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + xhr.status);
                }
            });
        }, 1000);
    }

    function pendingUpdate() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "{{ route('manager.show-pending-requests') }}");
        xhr.responseType = "json";
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = xhr.response;
                title.innerHTML = "Pending request";
                // console.log(data);

                // Update the table with the new data
                requestTbody.innerHTML = "";

                if (data.pending.length > 0) {
                    for (let i = 0; i < data.pending.length; i++) {
                        var row = data.pending[i];
                        if (row.accepted_by_user_name === null) {
                            row.accepted_by_user_name = "-";
                        }
                        requestTbody.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>";
                    }
                } else {
                    requestTbody.innerHTML += "<tr><td colspan='10'>No pending request...</td></tr>";
                }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    pendingForm.addEventListener('submit', (event) => {
        event.preventDefault();
        pendingInterval = setInterval(pendingUpdate, 1000);
    });

    acceptedForm.addEventListener('submit', (event) => {
        event.preventDefault();

        clearInterval(pendingInterval);
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "{{ route('manager.show-accepted-requests') }}");
        xhr.responseType = "json";
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = xhr.response;
                title.innerHTML = "Accepted request";
                // console.log(data);
                // Update the table with the new data

                requestTbody.innerHTML = "";

                if (data.accepted.length > 0) {
                    data.accepted.forEach(function(row) {
                        if (row.accepted_by_user_name === null) {
                            row.accepted_by_user_name = "-";
                        }
                        requestTbody.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>";
                    });
                } else {
                    requestTbody.innerHTML += "<tr><td colspan='10'>No accepted request...</td></tr>";
                }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    });

    deliveredForm.addEventListener('submit', (event) => {
        event.preventDefault();

        clearInterval(pendingInterval);
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "{{ route('manager.show-delivered-requests') }}");
        xhr.responseType = "json";
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = xhr.response;
                title.innerHTML = "Delivered request";
                // console.log(data);

                // Update the table with the new data
                requestTbody.innerHTML = "";

                if (data.delivered.length > 0) {
                    data.delivered.forEach(function(row) {
                        if (row.accepted_by_user_name === null) {
                            row.accepted_by_user_name = "-";
                        }
                        requestTbody.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>";
                    });
                } else {
                    requestTbody.innerHTML += "<tr><td colspan='10'>No delivered request...</td></tr>";
                }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    });

    pendingCount();
    acceptedCount();
    deliveredCount();

    setTimeout(function() {
        document.getElementById('alert').style.display = 'none';
    }, 3000);
</script>
@endsection