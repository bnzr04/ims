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
                <form action="{{ route('admin.update-item', ['id' => $data->id]) }}" method="post" class="container-sm">
                    @csrf
                    <div class="container-sm mb-1">
                        <label for="item-id">Item ID</label>
                        <input type="text" class="form-control" name="item-id" id="item-id" disabled value="{{ $data->id }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="item-name">Item name</label>
                        <input type="text" class="form-control" name="itemName" id="item-name" required value="{{ $data->item_name }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="category">Item category</label>
                        <input type="text" class="form-control" name="category" id="category" required value="{{ $data->category }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="item-description">Item description</label>
                        <textarea class="form-control" name="itemDescription" id="item-description" required>{{ $data->item_description }}</textarea>
                    </div>

                    <div class="container-sm mb-1">
                        <label for="item-cost">Cost</label>
                        <input type="text" class="form-control" name="cost" id="item-cost" required value="{{ $data->item_cost }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="salvage-cost">Salvage cost</label>
                        <input type="text" class="form-control" name="salvageCost" id="salvage-cost" required value="{{ $data->item_salvage_cost }}">
                    </div>

                    <div class="container-sm mb-2">
                        <label for="useful-life">Useful life</label>
                        <input type="number" min='0' class="form-control" name="usefulLife" id="useful-life" required value="{{ $data->item_useful_life }}">
                    </div>

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