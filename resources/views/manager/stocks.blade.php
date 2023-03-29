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
                <h2>ITEMS & STOCKS</h2>
                <div class="mt-3 d-flex justify-content-between">
                    <div class="container-sm">
                        <a href="{{ route('manager.new-item') }}" class="btn btn-success">New Item</a>
                        <a href="{{ route('manager.AllStocks') }}" class="btn btn-secondary">All Stocks</a>
                    </div>

                    <form action="" method="get">
                        <div class="input-group flex-nowrap" style="width: 30rem;">
                            <input type="text" class="form-control bg-white" placeholder="Search name..." aria-label="search" aria-describedby="addon-wrapping" name="search" id="search" value="{{ $search }}">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>

                </div>

                @if(Auth::user()->dept === 'pharmacy')
                <div class="container-sm mt-2 d-flex">
                    <form action="" method="get">
                        <label for="category">Item Category</label>
                        <div class="container-sm p-0 d-flex input-group" style="width: 400px;">
                            <select name="category" class="form-select text-capitalize" id="category">
                                @if($category)
                                <option value="{{ $category }}">{{ $category }}</option>
                                <option value="">All</option>
                                @else
                                <option value="">All</option>
                                @endif
                                @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
                @else
                <div class="container-sm mt-2 d-flex">
                    <form action="" method="get">
                        <label for="category">Item Category</label>
                        <div class="container-sm p-0 d-flex input-group" style="width: 400px;">
                            <select name="category" class="form-select text-capitalize" id="category" disabled>
                                <option value="medical supply">Medical Supply</option>
                            </select>
                        </div>
                    </form>
                </div>
                @endif

                <div class="container-sm">
                    <form action="{{ route('manager.export-items') }}" method="post">
                        @csrf
                        <button class="btn btn-light border border-secondary" title="Download Report"><img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                    </form>
                </div>

                <div class="container-lg p-0 border border-dark rounded shadow" style="height: 400px;overflow:auto;">
                    <table class="table">
                        <thead class="bg-success text-white" style="position: sticky;top: 0;">
                            <tr>
                                <th scope="col">Item ID</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Category</th>
                                <th scope="col">Unit</th>
                                <th scope="col">Current Stock</th>
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
                                <th class="">{{ $item->unit }}</th>
                                @if($item->total_quantity !== null)
                                <th class="{{ $item->total_quantity <= 100 ? 'text-warning' : ( $item->total_quantity < 1 ? 'text-danger' : 'text-success') }}">{{ $item->total_quantity }}</th>
                                @else
                                <th class="text-danger">No stocks</th>
                                @endif
                                <td>
                                    <a href="{{ route('manager.add-to-stocks',['id' => $item->id]) }}" class="btn btn-secondary" title="Add stocks">Stocks</a>
                                    <a href="{{ route('manager.show-item', ['id' => $item->id]) }}" class="btn btn-success">Edit</a>
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
</div>
<script>
    function deleteUser() {
        if (!confirm("Are you sure you want to delete this item?\nThis item will be deleted even in the stocks if it's added.")) {
            event.preventDefault();
        };
    }
</script>
@endsection