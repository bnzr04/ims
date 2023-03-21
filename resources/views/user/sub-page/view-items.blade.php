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
                <a href="{{ route('user.request') }}" class="btn btn-secondary">Back</a>
                <div class="container-md">
                    <div class="container-md mt-3 pt-2 pb-2 shadow lh-1 rounded overflow-auto">
                        <table class="table">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th scope="col">Request ID</th>
                                    <th scope="col">Office</th>
                                    <th scope="col">Request By</th>
                                    <th scope="col">Request To</th>
                                    <th scope="col">Request status</th>
                                    <th scope="col">Request date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td scope="col">{{ $request->id }}</td>
                                    <td scope="col" class="text-capitalize">{{ $request->office }}</td>
                                    <td scope="col" class="text-capitalize">{{ $request->request_by }}</td>
                                    <td scope="col" class="text-capitalize">{{ $request->request_to }}</td>
                                    <td scope="col" class="text-capitalize">{{ $request->status }}</td>
                                    <td scope="col">{{ $request->formatted_date }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="container-md">

                    @if($request->status == 'not completed')
                    <form action="{{ route('user.add-item') }}" method="post">
                        @csrf
                        <div class="container-lg">
                            <input type="text" name="request_id" id="request_id" value="{{ $request->id }}" hidden>
                            <h5 style="letter-spacing: 3px;">ITEM NAME: </h5>
                            <select id="nameSearch" class="text-capitalize" name="nameSearch" style="width: 280px;" required>
                                <option></option>
                                @foreach($items as $item)
                                <option value="{{ $item->item_id }}" class="text-capitalize" data-stock-id="{{ $item->id }}" data-stock-exp="{{ $item->exp_date }}">{{ $item->name }} ({{ $item->formatted_exp_date }})</option>
                                @endforeach
                            </select>

                            <input type="number" id="quantity" name="quantity" min="1" style="min-width:120px;" placeholder="Quantity" required>
                            <input type="hidden" id="stock_id" name="stock_id" style="min-width:120px;">
                            <input type="hidden" id="exp_date" name="exp_date" style="min-width:120px;">
                            <button type="submit" class="btn btn-secondary py-1 px-2">Add</button>
                        </div>
                    </form>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success" id="alert">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('warning'))
                    <div class="alert alert-warning" id="alert">
                        {{ session('warning') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger" id="alert">
                        {{ session('error') }}
                    </div>
                    @endif


                    <div class="container-lg p-0 border border-dark shadow p-3 mt-3 mb-2 bg-body rounded" style="height: 340px;">
                        <h4>Requested Items</h4>
                        <div class=" container-lg p-0" style="height:230px;overflow-y: auto;">
                            <table class="table">
                                <thead class="text-white bg-secondary" style="position: sticky;top: 0;z-index: 0;">
                                    <tr>
                                        <th scope="col">Item ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Unit</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Stock ID</th>
                                        <th scope="col">Expiration</th>
                                        @if($request->status == 'not completed')
                                        <th scope="col">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requestItems as $item)
                                    <tr>
                                        <td scope="col">{{ $item->item_id }}</td>
                                        <td scope="col" class="text-capitalize">{{ $item->name }}</td>
                                        <td scope="col" class="text-capitalize">{{ $item->description }}</td>
                                        <td scope="col" class="text-capitalize">{{ $item->category }}</td>
                                        <td scope="col" class="text-capitalize">{{ $item->unit }}</td>
                                        <td scope="col" class="text-capitalize">{{ $item->quantity }}</td>
                                        <td scope="col" class="text-capitalize">{{ $item->stock_id }}</td>
                                        <td scope="col" class="text-capitalize">{{ $item->exp_date }}</td>
                                        @if($request->status == 'not completed')
                                        <td scope="col">
                                            <a href="" class="btn btn-secondary">Edit</a>
                                            <a href="{{ route('user.remove-item', ['sid' => $item->stock_id,'id' => $item->item_id]) }}" class="btn btn-danger" onclick="remove()">Remove</a>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8">
                                            No Items...
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($request->status == 'not completed')
                        <form action="{{ route('user.submit-request',['rid' => $request->id]) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-primary">Submit request</button>
                        </form>
                        @endif
                        @if($request->status == 'delivered')
                        <td>
                            <form action="{{ route('user.receive-request', ['rid' => $request->id]) }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-warning">Received</button>
                            </form>
                        </td>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(session('searched'))
<script>
    // Automatically scroll to the search div
    window.location.hash = '#search';
</script>
@endif


<script>
    setTimeout(function() {
        document.getElementById('alert').style.display = 'none';
    }, 3000);

    function remove() {
        if (!confirm('Do you want to remove this item?')) {
            event.preventDefault();
        }
    }
    $(document).ready(function() {
        $('#nameSearch').select2({
            placeholder: 'select item',
            allowClear: true,
        });

        $('#nameSearch').on('change', function() {
            var selectedOptionValue = $('#nameSearch option:selected');
            var stockId = selectedOptionValue.data('stock-id');
            var stockExpDate = selectedOptionValue.data('stock-exp');
            $('#stock_id').val(stockId);
            $('#exp_date').val(stockExpDate);
        });
    });
</script>
@endsection