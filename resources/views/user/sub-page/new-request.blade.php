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
                <a href="{{ route('user.request') }}" class="btn btn-secondary">Back</a>
                <div class="container-lg shadow rounded p-3 mt-3 mb-4">
                    <div class="container-sm p-2 mb-2 d-flex justify-content-between text-white rounded" style="align-items:center;background-color:#8905f5;">
                        <h4 class="m-0">List of items</h4>
                        <form action="{{ route('user.newRequest') }}" method="get" class="mb-0">
                            <div class="input-group flex-nowrap rounded" style="width: 350px;align-items:center;">
                                <input type="text" class="form-control" name="search_item" id="search_item" placeholder="Search item name" aria-label="search" aria-describedby="addon-wrapping" value="{{ $search_item }}">
                                <button type="submit" class="btn text-white" style="background-color: #dc03fc;">Search</button>
                            </div>
                        </form>
                    </div>
                    <div class="container-sm overflow-auto px-0 mb-2 shadow-sm" style="height:300px;">
                        <table class="table">
                            <thead class="text-white" style="background-color: #6402b5;">

                                <tr>
                                    <th scope="col">Item ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td scope="col">{{ $item->id }}</td>
                                    <td scope="col">{{ $item->name }}</td>
                                    <td scope="col">{{ $item->description }}</td>
                                    <td scope="col">{{ $item->category }}</td>
                                    <td scope="col">{{ $item->unit }}</td>
                                    <td scope="col">
                                        <!-- <input type="checkbox" name="items[{{ $item->id }}][selected]" value="{{ $item->id }}">
                                                <input type="number" min="1" name="items[{{ $item->id }}][quantity]" id="quantity" style="width: 90px;"> -->
                                        <button class="btn btn-primary" id="add-btn-{{ $item->id }}" onclick="addItem({{$item->id}})">+</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6">Item not found...</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="container-lg mt-4">
                    <form action="{{ route('user.save-request') }}" method="post">
                        @csrf
                        <div class="container-sm shadow p-4 mb-5 bg-body rounded">
                            <h4>Request Details</h4>
                            <div class="container-sm">
                                <input type="text" class="form-control" name="user_id" id="user_id" value="{{ auth()->user()->id }}" hidden>
                                <label for="office"><b>Office Name:</b></label>
                                <input type="text" class="form-control" name="office" id="office" required>
                            </div>

                            <div class="container-sm">
                                <label for="request_by"><b>Request By:</b></label>
                                <input type="text" class="form-control" name="request_by" id="request_by" required>
                            </div>

                            <div class="container-sm">
                                <label for="request_to"><b>Request To:</b></label>
                                <select name="request_to" id="request_to" class="form-select" required>
                                    <option value="">Select</option>
                                    <option value="pharmacy">Pharmacy</option>
                                    <option value="csr">Csr</option>
                                </select>
                            </div>

                            <div class="container-sm p-3">
                                @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif

                                @if(session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif
                            </div>

                            <div class="container-sm overflow-auto mb-2 shadow-sm rounded" style="height:300px;padding: 0 0 10px 0;">
                                <h4 class="p-2 bg-success rounded text-white">Selected Items:</h4>
                                <table class="table">
                                    <thead class="bg-success text-white">
                                        <tr>
                                            <th scope="col">Item ID</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td scope="col">1</td>
                                            <td scope="col">Name</td>
                                            <td scope="col">Description</td>
                                            <td scope="col">Category</td>
                                            <td scope="col">Unit</td>
                                            <td scope="col">
                                                <input type="number" name="quantity" id="quantity" style="width: 100px;">
                                            </td>
                                            <td>
                                                <button class="btn btn-danger">-</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="container-sm mt-5">
                                <button type="submit" class="btn btn-success" style="letter-spacing: 3px;">CONFIRM</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection