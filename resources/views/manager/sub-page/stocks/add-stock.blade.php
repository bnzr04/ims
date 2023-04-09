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
                                <th scope="col">Exp Date</th>
                            </tr>

                            <!-- this is edit button of mode of acqusition -->
                            <!-- <button id="moa_edit_btn" class="rounded"><img id="edit_button_img" src="{{ asset('/icons/edit.png') }}" alt="edit-icon" width="10px" height="12px"></button> -->
                        </thead>
                        <tbody>
                            <tr>
                                <th>{{ $stock->id }}</th>
                                <td>{{ $stock->formated_created_at }}</td>
                                <td>{{ $stock->formated_updated_at }}</td>
                                <td>{{ $stock->stock_qty }}</td>
                                <td id="moa_data">{{ $stock->mode_acquisition }}</td>
                                <td>{{ $stock->exp_date }}</td>
                            </tr>
                        </tbody>
                    </table>

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

                    <form action="{{ route('manager.update-stock', ['id' => $stock->id]) }}" method="post">
                        @csrf
                        <div class="container-sm mb-3">
                            <label for="operation">Operation</label>
                            <select name="operation" id="operation" class="form-select" style="width: 200px;">
                                <option value="return">To return</option>
                                <option value="remove">To remove</option>
                            </select>
                        </div>

                        <div class="container-sm">
                            <label for="new_stock">Quantity:</label>
                            <input type="number" class="form-control" min="1" name="new_stock" id="new_stock" style="width: 200px;">
                        </div>

                        <div class="container-sm mt-3">
                            <a href="{{ route('manager.add-to-stocks', ['id' => $item]) }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Proceed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    setTimeout(function() {
        document.getElementById('alert').style.display = 'none';
    }, 3000);

    ///////////////////////Mode of acquisition////////////////////////
    // function modeOfAcquisition(){
    // const moaEditButton = document.getElementById('moa_edit_btn');
    // const moaData = document.getElementById('moa_data').innerHTML;
    // const editBtnImg = document.getElementById('edit_button_img');

    // moaEditButton.addEventListener('click', function() {
    //     if (editBtnImg.src === "{{ asset('/icons/edit.png') }}") {
    //         document.getElementById('moa_data').innerHTML = "<input id='moa_input' type='text' class='form-control' style='width:100px' value='" + moaData + "'>";
    //         editBtnImg.src = "{{ asset('/icons/check.png') }}"
    //     } else {
    //         const new_moa = document.getElementById('moa_input').value;
    //         document.getElementById('moa_data').innerHTML = moaData;
    //         editBtnImg.src = "{{ asset('/icons/edit.png') }}";


    //     }
    //     // alert(moaData);
    // });
    // }

    // modeOfAcquisition();
</script>
@endsection