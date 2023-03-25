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
                <h1>My request</h1>
                <div class="container-lg">
                    <a href="{{ route('user.newRequest') }}" class="btn btn-success">New request</a>
                </div>

                <div class="container-lg my-3">
                    <div class="btn-group" role="group">
                        <a href="{{ route('user.request',['request' => 'pending']) }}" class="btn btn-outline-primary">Pending ({{ $pending }})</a>
                        <a href="{{ route('user.request',['request' => 'accepted']) }}" class="btn btn-outline-primary">Accepted ({{ $accepted }})</a>
                        <a href="{{ route('user.request',['request' => 'delivered']) }}" class="btn btn-outline-primary">Delivered ({{ $delivered }})</a>
                        <a href="{{ route('user.request',['request' => 'completed']) }}" class="btn btn-outline-primary">Completed ({{ $completed }})</a>
                        <a href="{{ route('user.request',['request' => 'not completed']) }}" class="btn btn-outline-primary">Not completed ({{ $notcompleted }})</a>
                    </div>
                </div>
                <div class="container-lg">
                    <h5 class="text-capitalize">{{ $status == true ? $status : 'pending' }} Requests</h5>
                </div>
                <div class="container-lg mt-3">
                    <table class="table">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th scope="col">Request ID</th>
                                <th scope="col">Items</th>
                                <th scope="col">Office</th>
                                <th scope="col">Requested By</th>
                                <th scope="col">Requested To</th>
                                <th scope="col">Request date</th>
                                <th scope="col">Status</th>
                                @if(empty($status) || $status === 'pending' || $status === 'delivered' || $status === 'not completed')
                                <th scope="col">Actions</th>
                                @endif

                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                            <tr>
                                <th scope="col">{{ $request->id }}</th>
                                <td>
                                    <a href="{{ route('user.request-items', ['id' => $request->id]) }}" class="text-decoration-none">View</a>
                                </td>
                                <td scope="col" class="text-capitalize">{{ $request->office }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->request_by }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->request_to }}</td>
                                <td scope="col">{{ $request->formatted_created_at }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->status }}</td>
                                @if($request->status == 'pending' || $request->status == 'not completed')
                                <td>
                                    <a href="{{ route('user.delete-request', ['id' => $request->id]) }}" class="btn btn-danger" onclick="cancelRequest()">Cancel</a>
                                </td>
                                @endif
                                @if($request->status == 'delivered')
                                <td>
                                    <form action="{{ route('user.receive-request', ['rid' => $request->id]) }}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">Received</button>
                                    </form>
                                </td>
                                @endif

                            </tr>
                            @empty
                            <tr>
                                <td colspan="8">
                                    No Request...
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
            </div>
        </div>
    </div>
</div>
<script>
    setTimeout(function() {
        document.getElementById('alert').style.display = 'none';
    }, 3000);

    function cancelRequest() {
        if (!confirm("Are you sure you want to cancel this request?")) {
            event.preventDefault();
        };
    }
</script>
@endsection