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
                <div class="container-lg mt-2">
                    <a href="{{ route('user.home') }}" class="btn btn-secondary">Back</a>
                </div>
                <div class="container-lg mt-3 px-2 rounded shadow">
                    <!-- <div class="container-lg">
                        <button class="btn btn-outline-dark" id="pending">Pending (<span id="pending-count"></span>)</button>
                        <button class="btn btn-outline-dark" id="accepted">Accepted (<span id="accepted-count"></span>)</button>
                        <button class="btn btn-outline-dark" id="delivered">Delivered (<span id="delivered-count"></span>)</button>
                    </div> -->
                    <div class="container-lg">
                        <h3 id="title" style="text-transform: capitalize;">
                            {{ $title }} request
                        </h3>
                        @if(session('success'))
                        <div class="alert alert-success" id="alert">
                            {{ session('success') }}
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-danger" id="alert">
                            {{ session('error') }}
                        </div>
                        @endif
                        <div class="container-lg p-0">
                            <div class="container-lg p-0 d-flex">
                                <div class="container-lg p-0">
                                    <a href="{{ route('user.viewRequest', ['request' => $title, 'filter' => 'today']) }}" class="btn btn-outline-success">Today</a>
                                    <a href="{{ route('user.viewRequest', ['request' => $title, 'filter' => 'this-week']) }}" class="btn btn-outline-success">This Week</a>
                                    <a href="{{ route('user.viewRequest', ['request' => $title, 'filter' => 'this-month']) }}" class="btn btn-outline-success">This Month</a>
                                </div>
                                <div class="d-flex" style="align-items: center;width:100%;max-width:500px">
                                    <label for="date_from">From</label>
                                    <input type="date" class="form-control mx-1" name="date_from" id="date_from">

                                    <label for="date_to">To</label>
                                    <input type="date" class="form-control mx-1" name="date_to" id="date_to" pattern="\d{2}/\d{2}/\d{4}" placeholder="MM/DD/YYYY">
                                    <button type="submit" class="btn btn-outline-success">Filter</button>
                                </div>
                            </div>
                            <div class="container-lg p-0 mt-2">
                                <h5>{{ $filter == "today" ? "Today" : ($filter == "this-week" ? "This Week" : ($filter == "this-month" ? "This Month" : "")) }} Requests</h5>
                            </div>
                        </div>
                    </div>
                    <div class="container-md overflow-auto mt-2 border border-dark p-0 rounded" style="height: 400px;">
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
    const alert = document.getElementById("alert");

    setTimeout(function() {
        alert.remove();
    }, 3000);
</script>
@endsection