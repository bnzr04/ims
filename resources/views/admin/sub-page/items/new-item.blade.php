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
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>

                            <div class="container-sm mb-2">
                                <label for="category">Item category</label>
                                <input type="text" class="form-control" name="category" id="category" required>
                            </div>

                            <div class="container-sm mb-2">
                                <label for="description">Item description</label>
                                <textarea class="form-control" name="description" id="description" required></textarea>
                            </div>

                            <div class="container-sm mb-2">
                                <label for="cost">Cost</label>
                                <input type="text" class="form-control" name="cost" id="cost" required>
                            </div>

                            <div class="container-sm mb-2">
                                <label for="salvage_cost">Salvage cost</label>
                                <input type="text" class="form-control" name="salvage_cost" id="salvage_cost" required>
                            </div>

                            <div class="container-sm mb-2">
                                <label for="useful_life">Useful life</label>
                                <input type="number" min='0' class="form-control" name="useful_life" id="useful_life" required>
                            </div>
                        </div>
                        <div class="container-sm">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                            <button type="submit" class="btn btn-primary" id="unique">Add Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection