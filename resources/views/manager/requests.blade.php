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
                <h2>Requests</h2>
                <div class="container-lg mt-3 p-2 pt-3 rounded shadow">
                    <div class="container-md">
                        <h5>Pending & accepted requests</h5>
                    </div>
                    <div class="container-md overflow-auto" style="height: 400px;">
                        <table class="table">
                            <thead class="bg-success text-white" style="position: sticky;top:0;">
                                <tr>
                                    <th scope="col">Req ID</th>
                                    <th scope="col">Date-time</th>
                                    <th scope="col">Office</th>
                                    <th scope="col">Request to</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody id="pending_table">

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
    function updateTable() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', "{{ route('manager.show-requests') }}", true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                // Update the table with the new data
                var pendingtbody = document.querySelector('#pending_table');
                pendingtbody.innerHTML = '';

                for (var i = 0; i < data.pending.length; i++) {
                    var row = data.pending[i];
                    pendingtbody.innerHTML += "<tr><td>" + row.id + "</td><td>" + row.formatted_date + "</td><td>" + row.office + "</td><td>" + row.request_to + "</td><td>" + row.status + "</td><td><a href='/manager/requested-items/" + row.id + "' class='btn btn-secondary'>View</a></td></tr>";
                }

                if (data.pending.length === 0) {
                    pendingtbody.innerHTML += "<tr><td colspan='6'>No pending request...</td></tr>";
                }

            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    // Update the table every 5 seconds
    setInterval(updateTable, 1000);

    setTimeout(function() {
        document.getElementById('alert').style.display = 'none';
    }, 3000);
</script>
@endsection