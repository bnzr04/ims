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
                <h1>Log</h1>
                <div class="container-md mb-3">
                    <div class="btn-group" role="group" aria-label="Basic outlined example">
                        <button type="button" onclick="window.location.href='{{ route('admin.log-index', ['date' => 'today']) }}'" class="btn btn-outline-dark">Today</button>
                        <button type="button" onclick="window.location.href='{{ route('admin.log-index', ['date' => 'yesterday']) }}'" class="btn btn-outline-dark">Yesterday</button>
                        <button type="button" onclick="window.location.href='{{ route('admin.log-index', ['date' => 'this_month']) }}'" class="btn btn-outline-dark">This Month</button>
                    </div>
                    <button type="button" class="btn btn-outline-primary" onclick="window.location.href=''">↻</button>
                </div>
                <div class="container-lg m-0">
                    <h5><span class="text-capitalize">{{ $requestDate == 'this_month' ? 'This month' : $requestDate}}</span> <span>( {{$dateAndTime}} )</span></h5>
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
                            <tr colspan="6">
                                <td>
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
@endsection