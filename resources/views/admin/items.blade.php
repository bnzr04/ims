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
                    <div class="container-sm">
                        <a href="{{ route('admin.new-item') }}" class="btn btn-success">New Item</a>
                        <a href="{{ route('admin.stocks') }}" class="btn btn-secondary">Stocks</a>
                    </div>

                    <div class="input-group flex-nowrap">
                        <input type="text" class="form-control bg-white" placeholder="" aria-label="search" aria-describedby="addon-wrapping">
                        <button class="btn btn-secondary">Search</button>
                    </div>

                </div>

                <div class="container-sm mt-2">
                    <form action="{{ route('admin.items') }}" method="get">
                        <label for="category">Item Category</label>
                        <div class="container-sm p-0 d-flex input-group">
                            <select name="category" class="form-select text-capitalize" id="category">
                                @if($category)
                                <option value="{{ $category }}">{{ $category }}</option>
                                @endif
                                <option value="">All</option>
                                @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                                <!-- @if(!$category == true)
                                <option value="" selected>All</option>
                                <option value="medicine">Medicine</option>
                                <option value="medical supply">Medical Supply</option>
                                @endif
                                @if($category === 'medicine')
                                <option value="medicine" selected>Medicine</option>
                                <option value="">All</option>
                                <option value="medical supply">Medical Supply</option>
                                @endif
                                @if($category === 'medical supply')
                                <option value="medical supply" selected>Medical Supply</option>
                                <option value="">All</option>
                                <option value="medicine">Medicine</option>
                                @endif -->
                            </select>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>

                <table class="table mt-2 overflow-x-auto" id="items_table">
                    <thead class="bg-success text-white">
                        <tr>
                            <th scope="col">Item ID</th>
                            <th scope="col">Item Name</th>
                            <th scope="col">Description</th>
                            <th scope="col">Category</th>
                            <th scope="col">Unit</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <th>{{ $item->id }}</th>
                            <td class="text-capitalize">{{ $item->name }}</td>
                            <td class="text-capitalize">{{ $item->description }}</td>
                            <td class="text-capitalize">{{ $item->category }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>
                                <a href="{{route('admin.add-to-stocks', ['id' => $item->id])}}" class="btn btn-secondary" title="Add stocks">+</a>
                                <a href="{{route('admin.show-item', ['id' => $item->id])}}" class="btn btn-success">Edit</a>
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
<script>
    function deleteUser() {
        if (!confirm("Are you sure you want to delete this item?\nThis item will be deleted even in the stocks if it's added.")) {
            event.preventDefault();
        };
    }
</script>
@endsection