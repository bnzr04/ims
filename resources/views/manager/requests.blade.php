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
                    <button class="btn btn-outline-dark" id="pending">Pending</button>
                    <button class="btn btn-outline-dark" id="accepted">Accepted</button>
                    <button class="btn btn-outline-dark" id="delivered">Delivered</button>
                </div>
                <div class="container-lg mt-1 p-2 border rounded shadow">
                    <div class="container-md">
                        <h5>Pending Requests</h5>
                    </div>
                    <div class="container-md overflow-auto" style="height: 350px;">
                        <table class="table">
                            <thead class=" bg-success text-white" style="position: sticky;top:0;">
                                <tr>
                                    <th scope="col">Req ID</th>
                                    <th scope="col">Date-time</th>
                                    <th scope="col">Office</th>
                                    <th scope="col">Patient Name</th>
                                    <th scope="col">Request By</th>
                                    <th scope="col">Request To</th>
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
                    <a href="{{ route('manager.transaction') }}" class="btn btn-secondary">Transactions</a>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    const pendingBtn = document.getElementById("pending");
    const acceptedBtn = document.getElementById("accepted");
    const deliveredBtn = document.getElementById("delivered");

    function updateTable() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', "{{ route('manager.show-pending-requests') }}", true);
        xhr.onload = function() {
            if (xhr.status === 200) {

                var data = JSON.parse(xhr.responseText);
                // Update the table with the new data
                var requestTbody = document.querySelector('#request_table');
                requestTbody.innerHTML = '';

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    requestTbody.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>";
                }

                if (data.length === 0) {
                    requestTbody.innerHTML += "<tr><td colspan='8'>No pending request...</td></tr>";
                }

                // for (var i = 0; i < data.completed.length; i++) {
                //     var row = data.completed[i];
                //     completedtbody.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.request_to + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>";
                // }

                // if (data.completed.length === 0) {
                //     completedtbody.innerHTML += "<tr><td colspan='6'>No completed request...</td></tr>";
                // }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    // Update the table every 5 seconds
    var pendingInterval = setInterval(updateTable, 1000);

    pendingBtn.addEventListener('click', function() {
        setInterval(updateTable, 1000);
    });

    acceptedBtn.addEventListener('click', function() {
        clearInterval(pendingInterval);
        var xhr = new XMLHttpRequest();
        xhr.open('GET', "{{ route('manager.show-accepted-requests') }}", true);
        xhr.onload = function() {
            if (xhr.status === 200) {

                var data = JSON.parse(xhr.responseText);
                // Update the table with the new data
                var requestTbody = document.querySelector('#request_table');
                requestTbody.innerHTML = '';

                for (var i = 0; i < data.length; i++) {
                    var row = data[i];
                    requestTbody.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.patient_name + "</td><td>" + row.request_by + "</td><td>" + row.request_to + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>";
                }

                if (data.length === 0) {
                    requestTbody.innerHTML += "<tr><td colspan='8'>No accepted request...</td></tr>";
                }

                // for (var i = 0; i < data.completed.length; i++) {
                //     var row = data.completed[i];
                //     completedtbody.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.request_to + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>";
                // }

                // if (data.completed.length === 0) {
                //     completedtbody.innerHTML += "<tr><td colspan='6'>No completed request...</td></tr>";
                // }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    });


    setTimeout(function() {
        document.getElementById('alert').style.display = 'none';
    }, 3000);
</script>
@endsection