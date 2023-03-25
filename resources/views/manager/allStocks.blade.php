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
                <a href="{{ route('manager.stocks') }}" class="btn btn-secondary mt-2 mb-4">Back</a>
                <h2>STOCKS</h2>
                <div class="mt-3 d-inline-block justify-content-between">
                    <div class="container-sm p-0">
                        <label for="category">Item Category</label>
                        <form action="" method="get">
                            <div class="container-sm p-0 d-flex input-group" style="width: 400px;">
                                <select name="category" class="form-select text-capitalize" id="category" {{ Auth::user()->dept === 'csr' ? 'disabled' : '' }}>
                                    @if(Auth::user()->dept === 'csr')
                                    <option value="">Medical Supply</option>
                                    @else
                                    @if($category !== null)
                                    <option value="{{ $category }}" class="text-capitalize">{{ $category }}</option>
                                    <option value="">All</option>
                                    @else
                                    <option value="">All</option>
                                    @endif
                                    @endif
                                    @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                                @if(Auth::user()->dept !== 'csr')
                                <button type="submit" class="btn btn-primary">Filter</button>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="input-group" style="width: 400px;">
                        <input type="text" class="form-control bg-white" placeholder="Search item name or id" name="search" id="search">
                        <button type="submit" class="input-group-text btn btn-secondary">Search</button>
                    </div>
                </div>
                <div class="container-lg p-0 my-3 border border-dark rounded shadow" style="height: 400px;overflow:auto;">
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
                                <th scope="row">{{ $stock->item_id }}</th>
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
                                    <a href="{{ route('manager.add-to-stocks', ['id' => $stock->item_id]) }}" class="btn btn-secondary">View batches</a>
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
@endsection