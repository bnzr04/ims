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
                <div class="mt-3 d-inline-block justify-content-between">
                    <div class="container-sm p-0">
                        <label for="category">Item Category</label>
                        <form action="" method="get">
                            <div class="container-sm p-0 d-flex input-group" style="width: 100%;max-width: 400px">
                                <select name="category" class="form-select text-capitalize" id="category">
                                    @if($category !== null)
                                    <option value="{{ $category }}" class="text-capitalize">{{ $category }}</option>
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

                    <div class="input-group" style="width: 100%;max-width: 400px">
                        <input type="text" class="form-control bg-white" placeholder="Search item name or id" name="search" id="search">
                        <button type="submit" class="input-group-text btn btn-secondary">Search</button>
                    </div>
                </div>
                <div class="container-lg px-2 pt-2 pb-4 mt-3 mb-4 rounded shadow bg-white">
                    <div class="container-lg p-0 mx-0 d-flex justify-content-between" style="width: 100%;max-width:200px">
                        <form action="{{ route('admin.export-stocks') }}" method="post" class="m-0">
                            @csrf
                            <button class="btn btn-light border border-secondary" title="Download Report"><img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                        </form>

                        <form action="{{ route('admin.dispense') }}" method="get">
                            <button class="btn btn-secondary">Dispense report</button>
                        </form>
                    </div>

                    <div class=" container-lg p-0 border" style="height: 400px;overflow:auto;">
                        <table class="table">
                            <thead class="bg-success text-white" style="position: sticky;top: 0;">
                                <tr>
                                    <th scope="col">Item ID</th>
                                    <th scope="col">Item Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Batches</th>
                                    <th scope="col">Total Stocks</th>
                                    <th scope="col">Latest Date Stocked</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse( $stocks as $stock )
                                <tr>
                                    <th scope="row">{{ $stock->id }}</th>
                                    <th>{{ $stock->name }}</th>
                                    <td>{{ $stock->description }}</td>
                                    <td>{{ $stock->category }}</td>
                                    <td>{{ $stock->stocks_batch}}</td>
                                    @if($stock->total_quantity !== null)
                                    <th class="{{ $stock->total_quantity <= 100 ? 'text-warning' : 'text-success' }}">{{ $stock->total_quantity }}</th>
                                    @else
                                    <th class="text-danger">No stocks</th>
                                    @endif
                                    <th>{{ $stock->latest_stock }}</th>
                                    <td>
                                        <a href="{{ route('admin.add-to-stocks', ['id' => $stock->id]) }}" class="btn btn-secondary">View batches</a>
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
    </div>
</div>
@endsection