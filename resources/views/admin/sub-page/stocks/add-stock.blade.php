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
                <h4>Add Stock</h4>
                <div class="container-sm">
                    <table class="table mt-2 mb-4 overflow-x-auto">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th scope="col">Stock ID</th>
                                <th scope="col">Create Date</th>
                                <th scope="col">Update Date</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Exp Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>{{ $stock->id }}</th>
                                <td>{{ $stock->created_at }}</td>
                                <td>{{ $stock->updated_at }}</td>
                                <td>{{ $stock->stock_qty }}</td>
                                <td>{{ $stock->exp_date }}</td>
                            </tr>
                        </tbody>
                    </table>

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

                    <form action="{{ route('admin.update-stock', ['id' => $stock->id]) }}" method="post">
                        @csrf
                        <div class="container-sm mb-3">
                            <label for="operation">Operation</label>
                            <select name="operation" id="operation" class="form-select" style="width: 200px;">
                                <option value="add">To add</option>
                                <option value="remove">To remove</option>
                            </select>
                        </div>

                        <div class="container-sm">
                            <label for="new_stock">Quantity:</label>
                            <input type="number" class="form-control" min="0" name="new_stock" id="new_stock" style="width: 200px;">
                        </div>

                        <div class="container-sm mt-3">
                            <a href="{{ route('admin.add-to-stocks', ['id' => $item]) }}" class="btn btn-secondary">Back</a>
                            <button class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection