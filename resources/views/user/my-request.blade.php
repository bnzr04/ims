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

                <div class="container-lg mt-3">
                    <table class="table">
                        <thead class="bg-success text-white">
                            <tr>
                                <th scope="col">Request ID</th>
                                <th scope="col">Items</th>
                                <th scope="col">Office</th>
                                <th scope="col">Requested By</th>
                                <th scope="col">Requested To</th>
                                <th scope="col">Request date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                            <tr>
                                <th scope="col">{{ $request->id }}</th>
                                <td>
                                    <a href="{{ route('user.item-request', ['id' => $request->id]) }}" class="text-decoration-none">View</a>
                                </td>
                                <td scope="col" class="text-capitalize">{{ $request->office }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->request_by }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->request_to }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->created_at }}</td>
                                <td scope="col">{{ $request->status }}</td>
                                <td>
                                    <a href="" class="btn btn-danger">Delete</a>
                                </td>
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
            </div>
        </div>
    </div>
</div>
@endsection