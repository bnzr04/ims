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
                <h1 class="mb-2">Edit Item</h1>
                <form action="{{ route('admin.update-item', ['id' => $item->id]) }}" method="post" class="container-sm">
                    @csrf
                    <div class="container-sm mb-1">
                        <label for="id">Item ID</label>
                        <input type="text" class="form-control" name="id" id="id" disabled value="{{ $item->id }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="name">Item name</label>
                        <input type="text" class="form-control" name="name" id="name" required value="{{ $item->name }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="description">Item description</label>
                        <textarea class="form-control" name="description" id="description" required>{{ $item->description }}</textarea>
                    </div>

                    <div class="container-sm mb-1">
                        <label for="category">Item category</label>
                        <select name="category" class="text-capitalize form-select" id="category">
                            @if($item->category === 'medicine')
                            <option value="medicine" selected>Medicine</option>
                            <option value="medical supply">Medical Supply</option>
                            @endif
                            @if($item->category === 'medical supply')
                            <option value="medical supply" selected>Medical Supply</option>
                            <option value="medicine">Medicine</option>
                            @endif
                        </select>
                    </div>

                    <div class="container-sm mb-1">
                        <label for="price">Cost</label>
                        <input type="text" class="form-control" name="price" id="price" required value="{{ $item->price }}">
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

                    <div class="container-sm">
                        <a href="{{ route('admin.items') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection