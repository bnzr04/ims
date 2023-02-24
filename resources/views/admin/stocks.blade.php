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
                <a href="{{ route('admin.items') }}" class="btn btn-secondary mt-2 mb-4">Back</a>
                <h2>STOCKS</h2>
                <div class="mt-3 d-flex justify-content-between">
                    <div class="input-group flex-nowrap" style="width: 270px;">
                        <span class="input-group-text" id="addon-wrapping">Search</span>
                        <input type="text" class="form-control bg-white" placeholder="" aria-label="search" aria-describedby="addon-wrapping">
                    </div>
                </div>

                <table class="table mt-2">
                    <thead class="bg-success text-white">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Item ID</th>
                            <th scope="col">Item Name</th>
                            <th scope="col">Description</th>
                            <th scope="col">Stocks</th>
                            <th scope="col">Exp Date</th>
                            <th scope="col">Stock Date</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse( $stocks as $stock )
                        <tr>
                            <th scope="row">{{ $stock->id }}</th>
                            <th scope="row">{{ $stock->item_id }}</th>
                            <td>{{ $stock->name }}</td>
                            <td>{{ $stock->description }}</td>
                            <th>{{ $stock->stock_qty }}</th>
                            <th>{{ $stock->exp_date }}</th>
                            <td>{{ $stock->created_at}}</td>
                            <td>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addStocks">Add Stocks</button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editItem">Edit</button>
                                <button type="button" class="btn btn-danger">Delete</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                No Item stock...
                            </td>

                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection