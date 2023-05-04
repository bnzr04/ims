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
                <div class="container-lg pt-3">
                    <button class="btn btn-outline-dark" id="pending">Pending (<span id="pending-count"></span>)</button>
                    <button class="btn btn-outline-dark" id="accepted">Accepted (<span id="accepted-count"></span>)</button>
                    <button class="btn btn-outline-dark" id="delivered">Delivered (<span id="delivered-count"></span>)</button>
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
    const pendingBtn = document.getElementById("pending");
    const acceptedBtn = document.getElementById("accepted");
    const deliveredBtn = document.getElementById("delivered");

    const pendingCountOutput = document.getElementById('pending-count');
    const acceptedCountOutput = document.getElementById('accepted-count');
    const deliveredCountOutput = document.getElementById('delivered-count');

    const title = document.getElementById("title");
    const requestTbody = $('#request_table');

    var pendingInterval = setInterval(pendingUpdate, 1000);

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
        $.ajax({
            url: "{{ route('manager.show-pending-requests') }}",
            type: "GET",
            dataType: "json",
            success: function(data) {
                title.innerHTML = "Pending request";
                console.log(data);
                // Update the table with the new data

                requestTbody.empty();

                if (data.pending.length > 0) {
                    $.each(data.pending, function(i, row) {
                        if (row.accepted_by_user_name === null) {
                            row.accepted_by_user_name = "-";
                        }
                        requestTbody.append("<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>");
                    });
                } else {
                    requestTbody.append("<tr><td colspan='10'>No pending request...</td></tr>");
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log('Error: ' + xhr.status);
            }
        });
    }

    function acceptedUpdate() {
        $.ajax({
            url: "{{ route('manager.show-accepted-requests') }}",
            type: "GET",
            dataType: "json",
            success: function(data) {
                title.innerHTML = "Accepted request";
                console.log(data);
                // Update the table with the new data
                requestTbody.empty();

                if (data.accepted.length > 0) {
                    $.each(data.accepted, function(i, row) {
                        requestTbody.append("<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>");
                    });
                } else {
                    requestTbody.append("<tr><td colspan='10'>No accepted request...</td></tr>");
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log('Error: ' + xhr.status);
            }
        });
    }

    function deliveredUpdate() {
        $.ajax({
            url: "{{ route('manager.show-delivered-requests') }}",
            type: "GET",
            dataType: "json",
            success: function(data) {
                title.innerHTML = "Delivered request";
                console.log(data);
                // Update the table with the new data
                requestTbody.empty();

                $.each(data.delivered, function(i, row) {
                    requestTbody.append("<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.doctor_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.accepted_by_user_name + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>");
                });

                if (data.delivered.length === 0) {
                    requestTbody.append("<tr><td colspan='10'>No delivered request...</td></tr>");
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log('Error: ' + xhr.status);
            }
        });
    }

    $(pendingBtn).on('click', function() {
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        pendingUpdate();
        pendingInterval = setInterval(pendingUpdate, 1000);
    });

    $(acceptedBtn).on('click', function() {
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        acceptedUpdate();
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
    });

    $(deliveredBtn).on('click', function() {
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        deliveredUpdate();
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
        clearInterval(pendingInterval);
    });

    pendingCount();
    acceptedCount();
    deliveredCount();

    setTimeout(function() {
        document.getElementById('alert').style.display = 'none';
    }, 3000);
</script>
@endsection