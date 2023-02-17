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
                <h2>ITEM STOCKS</h2>
                <div class="mt-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newItem">New Item</button>
                    @include('admin.modals.new-item')

                    <div class="input-group flex-nowrap" style="width: 270px;">
                        <span class="input-group-text" id="addon-wrapping">Search</span>
                        <input type="text" class="form-control bg-white" placeholder="" aria-label="search" aria-describedby="addon-wrapping">
                    </div>
                </div>

                <table class="table mt-2">
                    <thead>
                        <tr>
                            <th scope="col">Item ID</th>
                            <th scope="col">Item Name</th>
                            <th scope="col">Description</th>
                            <th scope="col">Category</th>
                            <th scope="col">Stocks</th>
                            <th scope="col">Last Stocked</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">1</th>
                            <td>sample</td>
                            <td>description</td>
                            <td>sample</td>
                            <td>5.</td>
                            <td>01-25-2023.</td>
                            <td>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addStocks">Add Stocks</button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editItem">Edit</button>
                                <button type="button" class="btn btn-danger">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection