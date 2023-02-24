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
                <div class="container-md">
                    <h2>New item</h2>
                    <form action="{{ route('admin.saveItem') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="container-sm mb-2">
                                <label for="name">Item name</label>
                                <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}">
                                @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-2">
                                <label for="description">Item description</label>
                                <textarea class="form-control" name="description" id="description">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-2">
                                <label for="category">Item category</label>
                                <select name="category" class="form-select" id="category">
                                    <option value="{{ old('name') }}">{{ old('name') == true ? old('category') : 'Select' }}</option>
                                    <option value="medicine">Medicine</option>
                                    <option value="medical supply">Medical Supply</option>
                                </select>
                                @error('category')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-2">
                                <label for="price">Item Price</label>
                                <input type="number" class="form-control" name="price" id="price" value="{{ old('price') }}">
                                @error('price')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif

                        @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif
                        <div class="container-sm">
                            <a href="{{ route('admin.items') }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary" id="unique">Add Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection