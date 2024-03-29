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
                <div class="container-lg">
                    <a href="{{ route('admin.items') }}" class="btn btn-secondary">Back</a>
                    <hr>
                </div>

                <div class="container-lg">
                    <h1 class="mb-2">Edit Item</h1>
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
                </div>

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

                    <div class="container-sm mb-2">
                        <label for="category"><b>Item category</b></label>
                        <select name="category" class="form-select text-capitalize" id="category">
                            @if($item->category)
                            <option value="{{ $item->category }}">{{ $item->category }}</option>
                            @endif
                            @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                            <option value="other">Other</option>
                        </select>
                        @error('category')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="container-sm mt-2 mb-2" id="new_category_div" style="display:none;">
                            <label for="new_category">Add new category</label>
                            <input type="text" name="new_category" class="form-control" id="new_category">
                            @error('new_category')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="unit"><b>Unit</b></label>
                        <select name="unit" class="form-select text-capitalize" id="unit">
                            @if($item->category)
                            <option value="{{ $item->unit }}">{{ $item->unit }}</option>
                            @endif
                            @foreach($units as $unit)
                            <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                            <option value="other">Other</option>
                        </select>
                        @error('unit')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="container-sm mt-2 mb-2" id="new_unit_div" style="display:none;">
                            <label for="new_unit">Add new unit</label>
                            <input type="text" name="new_unit" class="form-control" id="new_unit">
                            @error('new_unit')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="price"><b>Item Price</b></label>
                        <input type="text" name="price" class="form-control" id="price" value="{{ $item->price }}">

                        @error('price')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="container-sm p-3 mt-3 mb-3 shadow-lg rounded">
                        <div class="container-sm">
                            <h5>Threshold</h5>
                        </div>
                        <div class="container-sm">
                            <label for="max_limit">Maximum Quantity Limit</label>
                            <input type="number" min="1" name="max_limit" class="form-control" id="max_limit" value="{{ $item->max_limit  }}" required>
                        </div>
                        <div class="container-sm">
                            <label for="warning_level">Warning level (%)</label>
                            <input type="number" min="1" max="100" name="warning_level" class="form-control" id="warning_level" value="{{ $item->warning_level }}" required>
                        </div>
                    </div>


                    <div class="container-sm">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    category();
    unit();

    function category() {
        var category = document.getElementById('category');
        var newCategoryDiv = document.getElementById('new_category_div');

        if (category.value === 'other') {
            newCategoryDiv.style.display = 'block';
        }
        category.addEventListener("change", function() {
            if (category.value === "other") {
                newCategoryDiv.style.display = "block";
            } else {
                newCategoryDiv.style.display = "none";
            }
        });
    }

    function unit() {
        var unit = document.getElementById('unit');
        var newUnitDiv = document.getElementById('new_unit_div');

        if (unit.value === 'other') {
            newUnitDiv.style.display = 'block';
        }

        unit.addEventListener('change', function() {
            if (unit.value === 'other') {
                newUnitDiv.style.display = 'block';
            } else {
                newUnitDiv.style.display = 'none';
            }
        });
    }
</script>
@endsection