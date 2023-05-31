@extends('layouts.app')
<div class="container-lg p-0 m-1">
    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
</div>
<div id="content" class="px-2 py-1">
    <div class="container-lg p-2 d-flex" style="flex-wrap:wrap;justify-content:center">
        <div class="container-lg shadow p-0" style="overflow-y: auto;height: 500px">
            <table class="table table-dark table-striped">
                <thead style="position: sticky;top: 0;">
                    <tr>
                        <th class="border" scope="col">Date & Time</th>
                        <th class="border" scope="col">UID</th>
                        <th class="border" scope="col">User Type</th>
                        <th class="border" scope="col">Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="border">
                        <th class="border" scope="row">{{ $log->format_date }}</th>
                        <td class="border">{{ $log->user_id }}</td>
                        <td class="border">{{ $log->user_type }}</td>
                        <td class="border">{{ $log->message }}</td>
                    </tr>
                    @empty
                    <tr class="border">
                        <td class="border" colspan="4">No Logs...</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    // setInterval(function() {
    //     var xhr = new XMLHttpRequest();
    //     xhr.open('GET', "{{ route('dashboard-display') }}", true);
    //     xhr.onload = function() {
    //         if (xhr.status === 200) {
    //             var data = JSON.parse(xhr.responseText);
    //             // console.log(data);
    //             var totalItemsOutput = document.getElementById("total-items");
    //             var inStocksOutput = document.getElementById("in-stocks");
    //             var pendingRequestOutput = document.getElementById("pending-request");
    //             var completedTodayOutput = document.getElementById("completed-today");
    //             var adminOutput = document.getElementById("admins");
    //             var managerOutput = document.getElementById("managers");
    //             var userOutput = document.getElementById("users");

    //             //display total items
    //             totalItemsOutput.innerHTML = data.total_items;
    //             //display in stocks items
    //             inStocksOutput.innerHTML = data.in_stocks.count;
    //             //display pending request number
    //             pendingRequestOutput.innerHTML = data.pending_request;
    //             //display today completed request
    //             completedTodayOutput.innerHTML = data.completed_today;

    //             //display admins
    //             adminOutput.innerHTML = data.admins;
    //             //display managers
    //             managerOutput.innerHTML = data.managers;
    //             //display users
    //             userOutput.innerHTML = data.users;

    //         } else {
    //             console.log('Error: ' + xhr.status);
    //         }
    //     };
    //     xhr.send();
    // }, 1000);
</script>