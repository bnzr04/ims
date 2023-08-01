@php
use Illuminate\Support\Facades\Session
@endphp
@extends('layouts.app')
@include('layouts.header')
@section('content')
<div class="container-fluid ">
    <div class="row min-vh-100">
        <div class="col-md-3 col-lg-2 sidebar p-0 bg-dark">
            @include('layouts.sidebar')
        </div>
        <div class="col-md-9 col-lg-10 p-0">
            <div id="content" class="px-2 py-1">
                <div class="container-fluid mt-1">

                    <div class="container-fluid mt-2">
                        <h2>Edit stock</h2>

                        <h4>ITEM DESCRIPTION:</h4>
                        <table class="table mt-2 mb-4 overflow-x-auto">
                            <thead class="bg-secondary text-white">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>{{ $item->id }}</th>
                                    <td class="text-capitalize">{{ $item->name }}</td>
                                    <td class="text-capitalize">{{ $item->description }}</td>
                                    <td class="text-capitalize">{{ $item->category }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ is_null($item->price) ? "-" : $item->price }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h4>STOCK BATCH:</h4>
                        <table class="table mt-2 mb-4 overflow-x-auto border">
                            <thead class="bg-success text-white">
                                <tr class="border">
                                    <th scope="col" class="border">Stock ID</th>
                                    <th scope="col" class="border">Create Date</th>
                                    <th scope="col" class="border">Update Date</th>
                                    <th scope="col" class="border">Quantity</th>
                                    <th scope="col" class="border">MOA</th>
                                    <th scope="col" class="border">Lot #</th>
                                    <th scope="col" class="border">Block #</th>
                                    <th scope="col" class="border">Exp Date</th>
                                </tr>

                                <!-- this is edit button of mode of acqusition -->
                                <!-- <button id="moa_edit_btn" class="rounded"><img id="edit_button_img" src="{{ asset('/icons/edit.png') }}" alt="edit-icon" width="10px" height="12px"></button> -->

                            </thead>
                            <tbody>
                                <tr>
                                    <th class="border" id="stock_id">{{ $stock->id }}</th>
                                    <td class="border">{{ $stock->formated_created_at }}</td>
                                    <td class="border">{{ $stock->formated_updated_at }}</td>
                                    <td class="border">
                                        <input type="number" min="1" name="stock_qty" id="stock_qty" class="form-control" style="max-width: 100px;" value="{{ $stock->stock_qty }}" required>
                                    </td>
                                    <td class="border">{{ $stock->mode_acquisition }}</td>
                                    <td class="border">
                                        <input type="text" name="lot_num" id="lot_num" class="form-control" style="max-width: 100px;" value="{{ $stock->lot_number }}">
                                    </td>
                                    <td class="border">
                                        <input type="text" name="block_num" id="block_num" class="form-control" style="max-width: 100px;" value="{{ $stock->block_number }}">
                                    </td>
                                    <td class="border">{{ $stock->exp_date }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="alert alert-success" id="alert" style="display: none;">
                            {{ session('success') }}
                        </div>

                        <div class="container-fluid">
                            <a href="{{ route('admin.add-to-stocks', ['id' => $item]) }}" class="btn btn-secondary">Back</a>
                            <button class="btn btn-success" id="update_btn">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    setTimeout(function() {
        document.getElementById("alert").style.display = "none";
    }, 3000);

    $(document).ready(function() {
        window.APP_URL = "{{ url('') }}";

        var stockId = $("#stock_id").html();

        $("#update_btn").on("click", function() {
            event.preventDefault();
            var stockQty = $("#stock_qty").val();
            var lotNumber = $("#lot_num").val();
            var blockNumber = $("#block_num").val();

            // console.log(stockQty);
            if (!confirm("Are you sure you want to update the quantity of this stock batch?")) {
                event.preventDefault();
            } else {
                if (stockQty < 1) {
                    alert('Stock quantity must be greater than 1')
                } else {

                    $.ajax({
                        type: 'POST',
                        url: '/admin/admin-update-stock/' + stockId,
                        data: {
                            stock_id: stockId,
                            stock_qty: stockQty,
                            lot_num: lotNumber,
                            block_num: blockNumber,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // console.log(response.success);
                            if (response.success) {
                                $('#alert').removeClass('alert-danger').addClass('alert-success').css('display', 'block').text(response.success);
                            } else {
                                $('#alert').removeClass('alert-success').addClass('alert-danger').css('display', 'block').text(response.error);
                            }

                            setTimeout(function() {
                                $("#alert").css("display", "none");
                            }, 3000);
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                        }
                    });
                }

            }
        });
    });
</script>
@endsection