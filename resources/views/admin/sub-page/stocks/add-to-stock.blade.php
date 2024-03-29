@php
use Illuminate\Support\Facades\Session
@endphp
@php
use Carbon\Carbon
@endphp
@extends('layouts.app')
@include('layouts.header')
@section('content')
<style>
    #filter_link {
        text-decoration: none;
        color: black;
    }

    #filter_link:hover {
        text-decoration: underline;
        color: black;
    }
</style>
<div class="container-fluid ">
    <div class="row min-vh-100">
        <div class="col-md-3 col-lg-2 sidebar p-0 bg-dark ">
            @include('layouts.sidebar')
        </div>
        <div class="col-md-9 col-lg-10 p-0">
            <div class="container-fluid pt-2">
                <a href="{{ route('admin.items') }}" class="btn btn-secondary">Back to Items</a>
                <a href="{{ route('admin.stocks') }}" class="btn btn-secondary">Back to Stocks</a>
            </div>

            <div id="content" class="p-3 container-fluid">

                <div class="container-fluid">
                    <h4>ITEM DESCRIPTION:</h4>
                    <table class="table mt-2 mb-4 overflow-x-auto">
                        <thead class="bg-success text-white">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Category</th>
                                <th scope="col">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>{{ $item->id }}</th>
                                <td class="text-capitalize">{{ $item->name }}</td>
                                <td class="text-capitalize">{{ $item->description }}</td>
                                <td class="text-capitalize">{{ $item->category }}</td>
                                <td>{{ $item->unit }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>


                <div class="container-fluid px-3">
                    <h4>TOTAL STOCKS: <span class="total_quantity" data-warning-level="{{ $item->warning_level }}" data-max-limit="{{ $item->max_limit }}">{{ $total_stocks }}</span></h4>
                </div>

                <div class="container-fluid p-0 mt-2 d-flex">
                    <div class="container-fluid m-0">
                        <div class="container-fluid m-0 p-0" style="width:100%;max-width:400px;">
                            <div class="container-fluid d-flex p-0" style="align-items: center;">
                                <div class="m-1" style="min-width:40px;width:40px;height:100%;background-color:#fa9a93">&nbsp;</div>
                                <p class="m-0">Expired</p>
                            </div>
                            <div class="container-fluid d-flex p-0" style="align-items: center;">
                                <div class="m-1" style="min-width:40px;width:40px;height:100%;background-color:#fa9f50">&nbsp;</div>
                                <p class="m-0">Less Than Month Before Expiration</p>
                            </div>
                            <div class="container-fluid d-flex p-0" style="align-items: center;">
                                <div class="m-1" style="min-width:40px;width:40px;height:100%;background-color:#fcc74c">&nbsp;</div>
                                <p class="m-0">1 Month Before Expiration</p>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid m-0">
                        <div class="container-fluid d-flex p-0" style="align-items: center;">
                            <p class="m-0"><a id="filter_link" href="{{ route('admin.add-to-stocks',['id' => $item->id]) }}">All</a></p>
                        </div>
                        <div class="container-fluid d-flex p-0" style="align-items: center;">
                            <p class="m-0"><a id="filter_link" href="{{ route('admin.add-to-stocks',['id' => $item->id]) }}?petty-cash=1">Petty Cash</a></p>
                        </div>
                        <div class="container-fluid d-flex p-0" style="align-items: center;">
                            <p class="m-0"><a id="filter_link" href="{{ route('admin.add-to-stocks',['id' => $item->id]) }}?donation=1">Donation</a></p>
                        </div>
                        <div class="container-fluid d-flex p-0" style="align-items: center;">
                            <p class="m-0"><a id="filter_link" href="{{ route('admin.add-to-stocks',['id' => $item->id]) }}?lgu=1">LGU</a></p>
                        </div>
                    </div>
                </div>

                <div class="container-fluid p-0 pb-3 pt-2 px-2 shadow-lg rounded">
                    <div class="container-fluid m-0 p-0" style="display: flex;align-items:center;letter-spacing:2px;">
                        @if(request()->input('petty-cash') == 1)
                        <h4>Mode Of Acquisition: Petty Cash</h4>
                        @endif

                        @if(request()->input('donation') == 1)
                        <h4>Mode Of Acquisition: Donation</h4>
                        @endif

                        @if(request()->input('lgu') == 1)
                        <h4>Mode Of Acquisition: LGU</h4>
                        @endif
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

                    <div class="container-fluid p-0 border overflow-auto" style="max-height: 300px;">
                        <table class="table">

                            @if($stocks->isEmpty())
                            <tr>
                                <td colspan="6" class="text-danger">No Stocks...</td>
                            </tr>
                            @else
                            <thead class="bg-secondary text-white" style="position:sticky;top:0;">
                                <tr>
                                    <th scope="col" class="border">Stock ID</th>
                                    <th scope="col" class="border">Create Date</th>
                                    <th scope="col" class="border">Update Date</th>
                                    <th scope="col" class="border">Quantity</th>
                                    <th scope="col" class="border">MOA</th>
                                    <th scope="col" class="border">Lot #</th>
                                    <th scope="col" class="border">Block #</th>
                                    <th scope="col" class="border">Exp Date</th>
                                    <th scope="col" class="border">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($stocks as $stock)
                                <tr style="{{ $stock->exp_date <= Carbon::now()->format('Y-m-d') ? 'background-color:#fa9a93' : ($stock->exp_date < Carbon::now()->addMonth()->format('Y-m-d') ? 'background-color:#fa9f50' : ($stock->exp_date <= Carbon::now()->addMonth()->format('Y-m-d') ? 'background-color:#fcc74c' : '')) }}">
                                    <th>{{ $stock->id }}</th>
                                    <td>{{ $stock->created_at }}</td>
                                    <td>{{ $stock->updated_at }}</td>
                                    <td>{{ $stock->stock_qty }}</td>
                                    <td>{{ $stock->mode_acquisition }}</td>
                                    <td>{{ $stock->lot_number ?? "-" }}</td>
                                    <td>{{ $stock->block_number ?? "-" }}</td>
                                    <td>{{ $stock->exp_date }}</td>
                                    <td>
                                        <a href="{{ route('admin.add-stock', ['id' => $stock->id]) }}" class="btn btn-primary">+</a>
                                        <a href="{{ route('admin.edit-stock',['id' => $stock->id] )}}" class="btn btn-success">Edit</a>
                                        <a href="{{ route('admin.delete-stock', ['id' => $stock->id]) }}" class="btn btn-danger" onclick="deleteStock()">Dispose</a>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="container-fluid p-3 rounded shadow-lg" style="width: 100%;max-width: 500px;display:flex;flex-direction:column;align-items:center">
                    <form action="{{ route('admin.save-stock') }}" method="post" id="save_stock_form">
                        @csrf
                        <div class="modal-body p-2" style="width:100%;max-width:300px">
                            <h5>ADD NEW STOCKS BATCH:</h5>
                            <input type="hidden" class="form-control" name="item_id" id="item_id" value="{{ $item->id }}">

                            <div class="container-sm mb-1">
                                <label for="stock_qty">Quantity</label>
                                <input type="number" min='1' class="form-control" name="stock_qty" id="stock_qty" required>
                            </div>

                            <div class="container-sm mb-1">
                                <label for="exp_date">Expiration Date</label>
                                <input type="date" class="form-control" name="exp_date" id="exp_date" required>
                            </div>

                            <div class="container-sm mb-1">
                                <label for="mode_acq">Mode of acquisition</label>
                                <input type="text" class="form-control" name="mode_acq" id="mode_acq_input" list="mode_acq" required>
                                <datalist id="mode_acq">
                                    <option value="Petty Cash">
                                    <option value="LGU">
                                    <option value="Donation">
                                </datalist>

                                <div class="matching_options"></div>
                            </div>

                            <div class="container-sm mb-1">
                                <label for="lot_num">Lot Number</label>
                                <input type="text" class="form-control" name="lot_num" id="lot_num">
                            </div>

                            <div class="container-sm mb-1">
                                <label for="lot_num">Block Number</label>
                                <input type="text" class="form-control" name="block_num" id="block_num">
                            </div>
                        </div>

                        <div class="container-sm mt-4">
                            <button type="submit" class="btn btn-primary" id="unique">Add Stock</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    setTimeout(function() { //this function will set alert display to none after showing
        document.getElementById("alert").style.display = "none";
    }, 3000);

    function deleteStock() { //this function will prevent disposing stock batch if the user click 'cancel' on the confirm
        if (!confirm('Are you sure that you want to dispose this stock batch?\nThis will remove permanently to our database.')) {
            event.preventDefault();
        }
    }

    const modeAcqInput = document.getElementById("mode_acq_input");
    const modeAcq = document.getElementById("mode_acq").options;
    const matchingOptionsContainer = document.getElementById("matching_options");

    modeAcqInput.addEventListener("input", function() {
        const inputValue = this.value.toLowerCase();
        let matchingOptionsHTML = "";

        for (let i = 0; i < modeAcq.length; i++) {
            const optionValue = modeAcq[i].value.toLowerCase();

            if (optionValue.includes(inputValue)) {
                matchingOptionsHTML = `<div>${modeAcq[i].value}</div>`;
            }
        }

        matchingOptionsContainer.innerHTML = matchingOptionsHTML;
    });


    function threshold() {
        const totalQuantity = document.getElementsByClassName("total_quantity");

        for (let i = 0; i < totalQuantity.length; i++) {

            var warningLevel = totalQuantity[i].getAttribute('data-warning-level');
            var maxLimit = totalQuantity[i].getAttribute('data-max-limit');
            var totalQuantityValue = parseInt(totalQuantity[i].innerHTML);

            var warningLevel = warningLevel / 100;

            var warningQty = maxLimit * warningLevel;

            if (totalQuantityValue <= warningQty) {
                totalQuantity[i].style.color = "#d67b00";
            } else if (isNaN(totalQuantityValue)) {
                totalQuantity[i].innerHTML = 0;
                totalQuantity[i].style.color = "#fa0505 ";
            } else if (totalQuantityValue > maxLimit) {
                totalQuantity[i].style.color = "#00CDCD";
            } else if (totalQuantityValue > warningLevel && totalQuantityValue < maxLimit) {
                totalQuantity[i].style.color = "#1ea200";
            }
        }
    }

    threshold();

    function preventZeroQuantity() {
        const stockQty = document.getElementById('stock_qty');

        const saveStockForm = document.getElementById("save_stock_form");

        saveStockForm.addEventListener('submit', function(event) {
            if (stockQty.value < 1) {
                event.preventDefault();
                alert('Quantity must be greater than or equal to 1')
            }
        });
    }

    preventZeroQuantity();
</script>
@endsection