@extends('layouts.app')
@section('content')
<style>
    .level {
        text-decoration: none;
        color: black;
    }

    .level:hover {
        text-decoration: underline;
        color: black;
    }
</style>
<div id="content" class="px-2 py-1">
    <div class="container-fluid p-2 pt-0 d-flex" style="flex-wrap:wrap;justify-content:center">
        <div class="container-fluid p-0 m-1">
            <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
            <a href="{{ route('dispense') }}" class="btn btn-secondary">Dispense</a>
            <a href="{{ route('transaction') }}" class="btn btn-secondary">Transactions</a>
        </div>
        <div class="container-fluid mt-2">
            <div class="container-fluid p-0 px- d-flex">
                <form action="" method="get">
                    <div class="input-group" style="width: 100%;max-width: 400px">
                        <input type="text" class="form-control bg-white" placeholder="Search item name or id" name="search" id="search" value="{{ $search }}">
                        <button type="submit" class="input-group-text btn btn-outline-secondary">Search</button>
                    </div>
                </form>

                <form action="" method="get" class="mx-2">
                    <div class="container-fluid p-0 d-flex input-group" style="width: 100%;max-width: 400px">
                        <select name="category" class="form-select text-capitalize" id="category">
                            @if($category !== null)
                            <option value="{{ $category }}" class="text-capitalize">{{ $category }}</option>
                            <option value="">All</option>
                            @else
                            <option value="">All</option>
                            @endif
                            @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </div>
                </form>

            </div>

            <div class="container-fluid p-0 mt-2 d-flex">

                <div class="container-fluid m-0 p-0" style="width:100%;max-width:200px;">
                    <div class="container-fluid d-flex p-0" style="align-items: center;">
                        <div class="m-1" style="min-width:40px;width:40px;height:100%;background-color:#00CDCD">&nbsp;</div>
                        <p class="m-0"><a href="{{ route('info') }}?filter=max" class="level">Over Max Limit</a></p>
                    </div>
                    <div class="container-fluid d-flex p-0" style="align-items: center;">
                        <div class="m-1" style="width:40px;height:100%;background-color:#1ea200">&nbsp;</div>
                        <p class="m-0"><a href="{{ route('info') }}?filter=safe" class="level">Safe Level</a></p>
                    </div>
                    <div class="container-fluid d-flex p-0" style="align-items: center;">
                        <div class="m-1" style="width:40px;height:100%;background-color:#d67b00">&nbsp;</div>
                        <p class="m-0"><a href="{{ route('info') }}?filter=warning" class="level">Warning Level</a></p>
                    </div>
                    <div class="container-fluid d-flex p-0" style="align-items: center;">
                        <div class="m-1" style="width:40px;height:100%;background-color:#dc0f00">&nbsp;</div>
                        <p class="m-0"><a href="{{ route('info') }}?filter=no-stocks" class="level">No Stocks</a></p>
                    </div>
                </div>
                <div class="container-fluid m-0 p-0" style="width:100%;max-width:300px;">
                    <div class="container-fluid d-flex p-0" style="align-items: center;">
                        <div class="m-1" style="min-width:40px;width:40px;height:100%;background-color:#fcd772">&nbsp;</div>
                        <p class="m-0">Has Expired/Expiring Stocks</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid px-2 pb-4 mb-4 rounded shadow bg-white">

            <div class=" container-fluid p-0 border" style="overflow:auto;">
                <table class="table">
                    <thead class="bg-success text-white" style="position: sticky;top: 0;">
                        <tr>
                            <th scope="col">Item ID</th>
                            <th scope="col">Item Name</th>
                            <th scope="col">Description</th>
                            <th scope="col">Category</th>
                            <th scope="col">Unit</th>
                            <th scope="col">Batches</th>
                            <th scope="col">Total Stocks</th>
                            @if($filter !== 'no-stocks')
                            <th scope="col">Last Date Stocked</th>
                            @endif
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse( $stocks as $stock )
                        <tr style="border-bottom: 1px black solid;{{ $stock->hasExpiredStocks || $stock->isExpiringSoon ? 'background-color:#fcd772' : ''}}">
                            <th scope="row">{{ $stock->id }}</th>
                            <th>{{ $stock->name }}</th>
                            <td>{{ $stock->description }}</td>
                            <td>{{ $stock->category }}</td>
                            <td>{{ $stock->unit }}</td>
                            <td>{{ $stock->stocks_batch}}</td>
                            @if($stock->total_quantity !== null)
                            <th class="total_quantity" data-warning-level="{{ $stock->warning_level }}" data-max-limit="{{ $stock->max_limit }}">{{ $stock->total_quantity }}</th>
                            @else
                            <th class="text-danger">No stocks</th>
                            @endif
                            @if($filter !== 'no-stocks')
                            <th>{{ $stock->latest_stock }}</th>
                            @endif
                            <td>
                                <a href="{{ route('add-to-stocks', ['id' => $stock->id]) }}" class="btn btn-outline-secondary">View Batches</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                No Item stock...
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    function threshold() {
        const totalQuantity = document.getElementsByClassName("total_quantity");

        for (let i = 0; i < totalQuantity.length; i++) {

            var warningLevel = totalQuantity[i].getAttribute('data-warning-level');
            var maxLimit = totalQuantity[i].getAttribute('data-max-limit');
            var totalQuantityValue = parseInt(totalQuantity[i].innerHTML);

            var warningLevel = warningLevel / 100;

            var warningQty = maxLimit * warningLevel;

            console.log(maxLimit);

            if (totalQuantityValue <= warningQty) {
                totalQuantity[i].style.color = "#d67b00";
            } else if (totalQuantityValue === 0) {
                totalQuantity[i].style.color = "#c6ae00 ";
            } else if (totalQuantityValue > maxLimit) {
                totalQuantity[i].style.color = "#00CDCD";
            } else {
                totalQuantity[i].style.color = "#1ea200";
            }
        }
    }

    threshold();
</script>
@endsection