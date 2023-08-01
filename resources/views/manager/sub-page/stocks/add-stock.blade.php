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
                                <th scope="col">MOA</th>
                                <th scope="col">Lot #</th>
                                <th scope="col">Block #</th>
                                <th scope="col">Exp Date</th>
                            </tr>

                            <!-- this is edit button of mode of acqusition -->
                            <!-- <button id="moa_edit_btn" class="rounded"><img id="edit_button_img" src="{{ asset('/icons/edit.png') }}" alt="edit-icon" width="10px" height="12px"></button> -->
                        </thead>
                        <tbody>
                            <tr>
                                <th id="stock_id">{{ $stock->id }}</th>
                                <td>{{ $stock->formated_created_at }}</td>
                                <td>{{ $stock->formated_updated_at }}</td>
                                <td id="stock_quantity">{{ $stock->stock_qty }}</td>
                                <td id="moa_data">{{ $stock->mode_acquisition }}</td>
                                <td><input type="text" class="form-control" style="width: 120px;" id="lot_number" value="{{ $stock->lot_number ?? '' }}"></td>
                                <td><input type="text" class="form-control" style="width: 120px;" id="block_number" value="{{ $stock->block_number ?? '' }}"></td>
                                <td>{{ $stock->exp_date }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="alert alert-success" id="alert" style="display: none;">
                        {{ session('success') }}
                    </div>

                    <form action="{{ route('manager.update-stock', ['id' => $stock->id]) }}" method="post" id="update_form" data-stock-id="{{ $stock->id }}">
                        @csrf
                        <div class="container-sm mb-3">
                            <label for="operation">Operation</label>
                            <select name="operation" id="operation" class="form-select" style="width: 200px;">
                                <option value="return">To return</option>
                                <option value="remove">To remove</option>
                            </select>
                        </div>

                        <div class="container-sm">
                            <label for="quantity">Quantity:</label>
                            <input type="number" class="form-control" min="1" id="quantity" style="width: 200px;">
                        </div>

                        <div class="container-sm mt-3">
                            <a href="{{ route('manager.add-to-stocks', ['id' => $item]) }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary" id="proceed_button">Proceed</button>
                        </div>
                    </form>
                </div>
                <div class="alert alert-warning border border-warning" id="warning" style="display: none;">
                    <b>WARNING:</b> '0' quantity stock batch will not be visible to the stock list.
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

        $("#update_form").submit(function(event) { //this will update the stock batch quantity and lot number
            event.preventDefault();
            var stockId = $(this).data("stock-id");
            var stockQuantity = $("#stock_quantity");
            var quantityInput = $("#quantity");

            var lotNumber = $("#lot_number").val();
            var blockNumber = $("#block_number").val();
            var operation = $("#operation").val();
            var quantity = $("#quantity").val();

            var url = "{{ route('manager.update-stock', ['id' => ':stockId']) }}";
            url = url.replace(':stockId', stockId);

            // alert('Quantity must be greater than 1');

            if (quantity != '' && quantity < 1) {
                alert('Quantity must be greater than 1');
            } else {

                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        lot_number: lotNumber,
                        block_number: blockNumber,
                        operation: operation,
                        quantity: quantity,
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(response) {
                        // console.log(response);

                        const warningDiv = $('#warning');

                        quantityInput.val('');

                        stockQuantity.text(response.new_quantity)

                        if (response.new_quantity === 0) {
                            warningDiv.css('display', 'block');
                        } else {
                            warningDiv.css('display', 'none');
                        }

                        if (response.success) {
                            $('#alert').removeClass('alert-danger').addClass('alert-success').css('display', 'block').text(response.success);
                        } else {
                            $('#alert').removeClass('alert-success').addClass('alert-danger').css('display', 'block').text(response.error);
                        }

                        setTimeout(function() { //this will hide the alert message after showing
                            $('#alert').css('display', 'none');
                        }, 3000);
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            }

        });

        const stockQuantity = parseInt($("#stock_quantity").text()); // Parse the text content to an integer
        console.log(typeof(stockQuantity));
        const warningDiv = $('#warning');

        if (stockQuantity === 0) {
            warningDiv.css('display', 'block');
        } else {
            warningDiv.css('display', 'none');
        }

    });
</script>
@endsection