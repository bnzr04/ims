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
                <a href="{{ route('user.request') }}" class="btn btn-secondary">Back</a>
                <div class="container-sm mt-3 lh-1">
                    <p class="text-capitalize"><span class="fw-bold">Request ID: </span>{{ $request->id }}</p>
                    <p class="text-capitalize"><span class="fw-bold">Office: </span>{{ $request->office }}</p>
                    <p class="text-capitalize"><span class="fw-bold">Request By: </span>{{ $request->request_by }}</p>
                    <p class="text-capitalize"><span class="fw-bold">Request To: </span>{{ $request->request_to }}</p>
                    <p class="text-capitalize"><span class="fw-bold">Request status: </span>{{ $request->status }}</p>
                    <p class="text-capitalize"><span class="fw-bold">Request date: </span>{{ $request->created_at }}</p>
                </div>
                <hr>
                <div class="container-md">
                    <a href="" class="btn btn-success mb-2">Add new item</a>
                    <div class="container-lg p-0 shadow p-3 mb-5 bg-body rounded" style="height:300px;overflow-y: auto;">
                        <h4>Requested Items</h4>
                        <table class="table">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th scope="col">Item ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Remarks</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td scope="col">{{ $item->id }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->name }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->description }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->category }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->unit }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->quantity }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->remarks == '' ? '------' :  $item->remarks }}</td>
                                    <td scope="col">
                                        <a href="" class="btn btn-danger">Remove</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6">
                                        No Items...
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
</div>
@endsection