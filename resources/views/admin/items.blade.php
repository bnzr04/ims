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
                <h2>ITEMS</h2>
                <div class="mt-3 d-flex justify-content-between">
                    <a href="{{ route('admin.new-item') }}" class="btn btn-primary">New Item</a>

                    <div class="input-group flex-nowrap" style="width: 270px;">
                        <span class="input-group-text" id="addon-wrapping">Search</span>
                        <input type="text" class="form-control bg-white" placeholder="" aria-label="search" aria-describedby="addon-wrapping">
                    </div>

                </div>

                <table class="table mt-2 overflow-x-auto" id="items_table">
                    <thead>
                        <tr>
                            <th scope="col">Item ID</th>
                            <th scope="col">Item Name</th>
                            <th scope="col">Description</th>
                            <th scope="col">Category</th>
                            <th scope="col">Cost</th>
                            <th scope="col">S. Cost</th>
                            <th scope="col">Useful Life</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <th>{{ $item->id }}</th>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->item_description }}</td>
                            <td>{{ $item->category }}</td>
                            <td>{{ $item->item_cost }}</td>
                            <td>{{ $item->item_salvage_cost }}</td>
                            <td>{{ $item->item_useful_life }}</td>
                            <td>
                                <a href="{{route('admin.show-user', $item->id)}}" class="btn btn-success">Edit</a>
                                <a href="{{route('admin.delete-item', $item->id)}}" class="btn btn-danger">Delete</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                No data...
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