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
                <form action="{{ route('manager.update-item', ['id' => $item->id]) }}" method="post" class="container-sm">
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

                    @if(session('success'))
                    <div class="alert alert-success" id="alert">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger" id="alert">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="container-sm">
                        <a href="{{ route('manager.stocks') }}" class="btn btn-secondary">Cancel</a>
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
    alertTimeout();

    function alertTimeout() {
        setTimeout(function() {
            document.getElementById('alert').style.display = 'none';
        }, 3000);
    }

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