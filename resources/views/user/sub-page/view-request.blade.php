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
                    <a href="{{ route('user.home') }}" class="btn btn-secondary">Back</a>
                </div>
                <div class="container-lg mt-3 px-2 pt-3 rounded shadow">
                    <!-- <div class="container-lg">
                        <button class="btn btn-outline-dark" id="pending">Pending (<span id="pending-count"></span>)</button>
                        <button class="btn btn-outline-dark" id="accepted">Accepted (<span id="accepted-count"></span>)</button>
                        <button class="btn btn-outline-dark" id="delivered">Delivered (<span id="delivered-count"></span>)</button>
                    </div> -->
                    <div class="container-lg">
                        <h5>

                        </h5>
                    </div>
                    <div class="container-md d-flex">
                        <form action="{{ route('user.viewRequest', ['request' => 'pending']) }}" class="mx-1" method="get">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Pending (<span id="pending-count"></span>)</button>
                        </form>
                        <form action="{{ route('user.viewRequest', ['request' => 'accepted']) }}" class="mx-1" method="get">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Accepted (<span id="accepted-count"></span>)</button>
                        </form>
                        <form action="{{ route('user.viewRequest', ['request' => 'delivered']) }}" class="mx-1" method="get">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Delivered (<span id="delivered-count"></span>)</button>
                        </form>
                        <form action="{{ route('user.viewRequest', ['request' => 'completed']) }}" class="mx-1" method="get">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Completed (<span id="completed-count"></span>)</button>
                        </form>
                    </div>
                    <div class="container-md overflow-auto mt-1" style="height: 400px;">
                        <table class="table">
                            <thead class="bg-success text-white" style="position: sticky;top:0;">
                                <tr>
                                    <th scope="col">Req ID</th>
                                    <th scope="col">Date-time</th>
                                    <th scope="col">Office</th>
                                    <th scope="col">Patient</th>
                                    <th scope="col">Doctor</th>
                                    <th scope="col">Request By</th>
                                    <th scope="col">Request to</th>
                                    <th scope="col">Accepted By</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>{{$item->id}}</td>
                                    <td>{{$item->formatted_date}}</td>
                                    <td>{{$item->office}}</td>
                                    <td>{{$item->patient_name}}</td>
                                    <td>{{$item->doctor_name}}</td>
                                    <td>{{$item->request_by}}</td>
                                    <td>{{$item->request_to}}</td>
                                    <td>{{is_null($item->accepted_by_user_name) ? "-" : $item->accepted_by_user_name}}</td>
                                    <td>{{$item->status}}</td>
                                    <td>
                                        <a href="{{ route('user.request-items',['id' => $item->id]) }}" class="btn btn-secondary">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10">No Request...</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const pendingCountOutput = document.getElementById('pending-count');
    const acceptedCountOutput = document.getElementById('accepted-count');
    const deliveredCountOutput = document.getElementById('delivered-count');
    const completedCountOutput = document.getElementById('completed-count');

    function pendingCount() {
        setInterval(function() {
            $.ajax({
                url: "{{ route('user.show-pending-requests') }}",
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
                url: "{{ route('user.show-accepted-requests') }}",
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
                url: "{{ route('user.show-delivered-requests') }}",
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

    function completedCount() {
        setInterval(function() {
            $.ajax({
                url: "{{ route('user.show-completed-requests') }}",
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    // console.log(data.deliveredCount);
                    completedCountOutput.innerHTML = data.completedCount;
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + xhr.status);
                }
            });
        }, 1000);
    }

    pendingCount();
    acceptedCount();
    deliveredCount();
    completedCount();
</script>
@endsection