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
                <h1>Logs</h1>
                <div class="container-fluid mb-3 d-flex">
                    <div class="btn-group" role="group" aria-label="Basic outlined example">
                        <button type="button" onclick="window.location.href='{{ route('admin.log-index', ['date' => 'today']) }}'" class="btn btn-outline-dark">Today</button>
                        <button type="button" onclick="window.location.href='{{ route('admin.log-index', ['date' => 'yesterday']) }}'" class="btn btn-outline-dark">Yesterday</button>
                        <button type="button" onclick="window.location.href='{{ route('admin.log-index', ['date' => 'this_month']) }}'" class="btn btn-outline-dark">This Month</button>
                    </div>
                    <form action="{{ route('admin.log-index', ['date_from' => request()->input('date_from'),'date_to' => request()->input('date_to') ]) }}" id="filter_form" class="m-0 mx-3">
                        @csrf
                        <!-- <a href="" class="btn btn-secondary" style="letter-spacing:2px;">TODAY</a> -->
                        <div class="d-flex" style="align-items: center;width:100%;max-width:500px">
                            <label for="date_from">From</label>
                            <input type="date" class="form-control mx-1" name="date_from" id="date_from">

                            <label for="date_to">To</label>
                            <input type="date" class="form-control mx-1" name="date_to" id="date_to" pattern="\d{2}/\d{2}/\d{4}" placeholder="MM/DD/YYYY">
                            <button type="submit" class="btn btn-outline-secondary">Filter</button>
                        </div>
                    </form>
                    <button type="button" class="btn btn-outline-primary" onclick="window.location.href=''">↻</button>
                </div>
                <div class="container-fluid m-0">
                    <h5><span class="text-capitalize">{{ $requestDate == 'this_month' ? 'This month' : ( $requestDate == null ? 'Today' : ($requestDate == 'yesterday' ? 'Yesterday' : $requestDate))}}</span> <span>( {{$dateAndTime}} )</span></h5>
                </div>
                <div class="table-responsive border border-dark" style="max-height: 30rem;">
                    <table class="table table-striped m-0">
                        <thead class="bg-secondary text-white" style="position: sticky;top: 0;z-index: 1;">
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">User ID</th>
                                <th scope="col">User Type</th>
                                <th scope="col">Message</th>
                                <th scope="col">Query</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->formatted_created_at }}</td>
                                <td>{{ $log->user_id }}</td>
                                <td>{{ $log->user_type }}</td>
                                <td>{{ $log->message }}</td>
                                <td>{{ $log->query }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    No log...
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
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
</script>
@endsection