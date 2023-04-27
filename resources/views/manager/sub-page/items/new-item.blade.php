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

                    <div class="container-md mt-2 d-inline" style="flex-direction: column;">
                        <a href="{{ route('manager.stocks') }}" class="btn btn-secondary">Back</a>
                        <hr>
                        <div class="container-md py-2 mt-3 shadow">
                            <form action="{{ route('manager.insert-items') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <label for="import_item">Add items file</label>
                                <div class="container-md p-0 d-flex">
                                    <input type="file" class="form-control" name="import_item" id="import_item" accept=".csv,.xlsx" style="max-width:230px;" required>
                                    <button type="submit" class="btn btn-outline-secondary mx-2">Insert</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="container-lg px-0 py-3">
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
                        <h2>NEW ITEM</h2>
                        <form action="{{ route('manager.saveItem') }}" method="post">
                            @csrf
                            <div class="modal-body shadow pb-2 rounded">
                                <div class="container-sm mb-2">
                                    <label for="name"><b>Item name</b></label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}">
                                    @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="container-sm mb-2">
                                    <label for="description"><b>Item description</b></label>
                                    <textarea class="form-control" name="description" id="description">{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="container-sm mb-2">
                                    <label for="category"><b>Item category</b></label>
                                    <select name="category" class="form-select text-capitalize" id="category">
                                        <option value="">Select</option>
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
                                        <option value="">Select</option>
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
                                    <input type="text" name="price" class="form-control" id="price">

                                    @error('price')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="container-sm p-3 mt-3 mb-3 shadow-lg rounded">
                                <div class="container-sm">
                                    <h5>Threshold</h5>
                                </div>
                                <div class="container-sm">
                                    <label for="max_limit">Maximum Quantity Limit</label>
                                    <input type="number" name="max_limit" class="form-control" id="max_limit" placeholder="Default: 500">
                                </div>
                                <div class="container-sm">
                                    <label for="warning_level">Warning level (%)</label>
                                    <input type="number" name="warning_level" class="form-control" id="warning_level" placeholder="Default: 30%">
                                </div>
                            </div>


                            <div class="container-sm">
                                <button type="submit" class="btn btn-primary" id="unique">Add Item</button>
                            </div>
                        </form>
                    </div>
                </div>
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