@php
use Illuminate\Support\Facades\Session
@endphp
@php
use Carbon\Carbon
@endphp
@extends('layouts.app')
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
<div class="container-fluid">
    <div class="container-fluid pt-2">
        <a href="{{ route('info') }}" class="btn btn-secondary">Back</a>
    </div>

    <div id="content container-fluid" class="p-3">

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
            <div class="container-fluid m-0">
                <div class="container-fluid d-flex p-0" style="align-items: center;">
                    <p class="m-0"><a id="filter_link" href="{{ route('add-to-stocks',['id' => $item->id]) }}">All</a></p>
                </div>
                <div class="container-fluid d-flex p-0" style="align-items: center;">
                    <p class="m-0"><a id="filter_link" href="{{ route('add-to-stocks',['id' => $item->id]) }}?petty-cash=1">Petty Cash</a></p>
                </div>
                <div class="container-fluid d-flex p-0" style="align-items: center;">
                    <p class="m-0"><a id="filter_link" href="{{ route('add-to-stocks',['id' => $item->id]) }}?donation=1">Donation</a></p>
                </div>
                <div class="container-fluid d-flex p-0" style="align-items: center;">
                    <p class="m-0"><a id="filter_link" href="{{ route('add-to-stocks',['id' => $item->id]) }}?lgu=1">LGU</a></p>
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

            <div class="container-fluid p-0 overflow-auto" style="max-height: 280px;">

                <table class="table">

                    @if($stocks->isEmpty())
                    <tr>
                        <td colspan="7" class="text-danger">No Stocks...</td>
                    </tr>
                    @else
                    <thead class="bg-secondary text-white" style="position:sticky;top:0;">
                        <tr>
                            <th scope="col">Stock ID</th>
                            <th scope="col">Create Date</th>
                            <th scope="col">Update Date</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">MOA</th>
                            <th scope="col">Exp Date</th>
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
                            <td id="exp-date">{{ Carbon::parse($stock->exp_date)->format('m-d-Y') }}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    setTimeout(function() {
        document.getElementById("alert").style.display = "none";
    }, 3000);

    function deleteStock() {
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
                console.log("safe");
                totalQuantity[i].style.color = "#1ea200";
            }
        }
    }

    threshold();

    // const expDateOutput = document.getElementById('exp-date');

    // // Create a new Date object
    // var today = new Date();

    // // Get the day, month, and year
    // var day = today.getDate();
    // var month = today.getMonth() + 1; // Months are zero-based, so we add 1
    // var year = today.getFullYear();

    // // Add leading zeros if necessary
    // if (day < 10) {
    //     day = '0' + day;
    // }

    // if (month < 10) {
    //     month = '0' + month;
    // }

    // // Format the date today as "DD-MM-YYYY"
    // var formattedDate = month + '-' + day + '-' + year;

    // console.log(formattedDate);
    // console.log(expDateOutput.innerHTML);
</script>
@endsection