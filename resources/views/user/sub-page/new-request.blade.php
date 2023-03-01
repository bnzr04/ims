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
                <div class="container-lg mt-4">
                    <form action="{{ route('user.save-request') }}" method="post">
                        @csrf
                        <div class="container-sm shadow p-4 mb-5 bg-body rounded">
                            <h4>Request Details</h4>
                            <div class="container-sm">
                                <input type="text" class="form-control" name="user_id" id="user_id" value="{{ auth()->user()->id }}" hidden>
                                <label for="office"><b>Office Name:</b></label>
                                <input type="text" class="form-control" name="office" id="office">
                            </div>

                            <div class="container-sm">
                                <label for="request_by"><b>Request By:</b></label>
                                <input type="text" class="form-control" name="request_by" id="request_by">
                            </div>

                            <div class="container-sm">
                                <label for="request_to"><b>Request To:</b></label>
                                <select name="request_to" id="request_to" class="form-select">
                                    <option value="">Select</option>
                                    <option value="pharmacy">Pharmacy</option>
                                    <option value="csr">Csr</option>
                                </select>
                            </div>

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

                            <div class="container-sm p-2 mb-2 mt-3 d-flex justify-content-between shadow-sm rounded bg-success text-white align-center">
                                <h4>Items</h4>
                                <div class="input-group flex-nowrap rounded" style="width: 300px;">
                                    <form action="" method="post">
                                        @csrf
                                        <input type="text" class="form-control" placeholder="" aria-label="search" aria-describedby="addon-wrapping">
                                        <button class="btn btn-warning">Search</button>
                                    </form>
                                </div>
                            </div>
                            <div class="container-sm overflow-auto mb-2 shadow-sm rounded" style="height:300px;padding: 10px 0 10px 0;">
                                <table class="table">
                                    <thead class="bg-success text-white">
                                        <tr>
                                            <th scope="col">Item ID</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Action & Quantity</th>
                                            <th scope="col" class="bg-danger">Available</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                        <tr>
                                            <td scope="col">{{ $item->id }}</td>
                                            <td scope="col">{{ $item->name }}</td>
                                            <td scope="col">{{ $item->description }}</td>
                                            <td scope="col">{{ $item->category }}</td>
                                            <td scope="col">{{ $item->unit }}</td>
                                            <td scope="col">
                                                <input type="checkbox" name="items[{{ $item->id }}][selected]" value="1">
                                                <input type="number" min="1" name="quantity" id="quantity" style="width: 90px;" value="" max="{{ $item->stock_qty }}">
                                            </td>
                                            <td scope="col" style="background-color: #ffd000">{{ $item->stock_qty }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="container-sm mt-3">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>

                    <!-- <div class="container-sm shadow p-4 mb-5 bg-body rounded">

                        

                        <div class="container-lg d-flex">
                            


                            <div class="container-sm overflow-auto" style="height:100px;">
                                <table class="table">
                                    <thead class="bg-success text-white">
                                        <tr>
                                            <th scope="col">Item ID</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection