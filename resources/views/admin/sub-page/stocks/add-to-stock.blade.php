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
            <div class="container-sm pt-2">
                <a href="{{ route('admin.stocks') }}" class="btn btn-secondary">Back</a>
            </div>

            <div id="content" class="p-3">
                <h4>ITEM DESCRIPTION:</h4>
                <table class="table mt-2 mb-4 overflow-x-auto">
                    <thead class="bg-success text-white">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Description</th>
                            <th scope="col">Category</th>
                            <th scope="col">Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>{{ $item->id }}</th>
                            <td class="text-capitalize">{{ $item->name }}</td>
                            <td class="text-capitalize">{{ $item->description }}</td>
                            <td class="text-capitalize">{{ $item->category }}</td>
                            <td>{{ $item->unit }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="container-lg p-0">
                    <h4>STOCKS: <span class="text-danger">{{ $total_stocks }}</span></h4>
                </div>
                <table class="table mt-2 mb-4 overflow-x-auto">

                    @if($stocks->isEmpty())
                    <tr>
                        <td colspan="6" class="text-danger">No Stocks...</td>
                    </tr>
                    @else
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th scope="col">Stock ID</th>
                            <th scope="col">Create Date</th>
                            <th scope="col">Update Date</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Exp Date</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($stocks as $stock)
                        <tr>
                            <th>{{ $stock->id }}</th>
                            <td>{{ $stock->created_at }}</td>
                            <td>{{ $stock->updated_at }}</td>
                            <td>{{ $stock->stock_qty }}</td>
                            <td>{{ $stock->exp_date }}</td>
                            <td>
                                <a href="{{ route('admin.add-stock', ['id' => $stock->id]) }}" class="btn btn-primary">+</a>
                                <a href="{{ route('admin.delete-stock', ['id' => $stock->id]) }}" class="btn btn-danger" onclick="deleteStock()">Dispose</a>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>


                <form action="{{ route('admin.save-stock') }}" method="post">
                    @csrf
                    <div class="modal-body p-2">
                        <h5>ADD NEW STOCKS BATCH:</h5>
                        <input type="hidden" class="form-control" name="item_id" id="item_id" value="{{ $item->id }}">

                        <div class="container-sm mb-1">
                            <label for="stock_qty">Quantity</label>
                            <input type="number" min="1" class="form-control" name="stock_qty" id="stock_qty" required>
                        </div>

                        <div class="container-sm mb-1">
                            <label for="exp_date">Expiration Date</label>
                            <input type="date" class="form-control" name="exp_date" id="exp_date" required>
                        </div>
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

                    <div class="container-sm mt-4">
                        <button type="submit" class="btn btn-primary" id="unique">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function deleteStock() {
        if (!confirm('Are you sure that you want to dispose this stock batch?\nThis will remove permanently to our database.')) {
            event.preventDefault();
        }
    }
</script>
@endsection