@extends('layouts.app')
@include('layouts.header')
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
<div class="container-fluid ">
    <div class="row min-vh-100">
        <div class="col-md-3 col-lg-2 sidebar p-0 bg-dark ">
            @include('layouts.sidebar')
        </div>
        <div class="col-md-9 col-lg-10 p-0">
            <div id="content" class="px-2 py-1">
                <div class="container-fluid">
                    <a href="{{ route('manager.stocks') }}" class="btn btn-secondary mt-2 mb-1">Back</a>
                    <hr>
                    <h2>STOCKS</h2>
                </div>
                <div class="container-fluid">
                    <div class="container-fluid p-0 px- d-flex">
                        <form action="" method="get">
                            <div class="input-group" style="width: 100%;max-width: 400px">
                                <input type="text" class="form-control bg-white" placeholder="Search item name or id" name="search" id="search" value="{{ $search }}">
                                <button type="submit" class="input-group-text btn btn-outline-secondary">Search</button>
                            </div>
                        </form>

                        <form action="" method="get" class="mx-2">
                            <div class="container-sm p-0 d-flex input-group" style="width: 100%;max-width: 400px">
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

                        <div class="container-sm m-0 p-0" style="width:100%;max-width:200px;">
                            <div class="container-sm d-flex p-0" style="align-items: center;">
                                <div class="m-1" style="min-width:40px;width:40px;height:100%;background-color:#00CDCD">&nbsp;</div>
                                <p class="m-0"><a href="{{ route('manager.AllStocks') }}?filter=max" class="level">Over Max Limit</a></p>
                            </div>
                            <div class="container-sm d-flex p-0" style="align-items: center;">
                                <div class="m-1" style="width:40px;height:100%;background-color:#1ea200">&nbsp;</div>
                                <p class="m-0"><a href="{{ route('manager.AllStocks') }}?filter=safe" class="level">Safe Level</a></p>
                            </div>
                            <div class="container-sm d-flex p-0" style="align-items: center;">
                                <div class="m-1" style="width:40px;height:100%;background-color:#d67b00">&nbsp;</div>
                                <p class="m-0"><a href="{{ route('manager.AllStocks') }}?filter=warning" class="level">Warning Level</a></p>
                            </div>
                            <div class="container-sm d-flex p-0" style="align-items: center;">
                                <div class="m-1" style="width:40px;height:100%;background-color:#dc0f00">&nbsp;</div>
                                <p class="m-0"><a href="{{ route('manager.AllStocks') }}?filter=no-stocks" class="level">No Stocks</a></p>
                            </div>
                        </div>
                        <div class="container-sm m-0 p-0" style="width:100%;max-width:300px;">
                            <div class="container-sm d-flex p-0" style="align-items: center;">
                                <div class="m-1" style="min-width:40px;width:40px;height:100%;background-color:#fcd772">&nbsp;</div>
                                <p class="m-0">Has Expired/Expiring Stocks</p>
                            </div>
                        </div>

                        <div class="container-lg p-0 mx-0 d-flex justify-content-between" style="width: 100%;max-width:300px;flex-direction:column-reverse">
                            <div class="container-sm d-flex m-0 p-1">
                                <form action="{{ route('manager.export-stocks') }}" method="post" class="m-0 d-flex">
                                    @csrf
                                    <button class="btn btn-light border border-secondary" title="Download Report"><img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <div class=" container-fluid p-0 border">
                    <table class="table">
                        <thead class="bg-success text-white" style="position: sticky;top: 55;">
                            <tr>
                                <th scope="col">Item ID</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Category</th>
                                <th scope="col">Unit</th>
                                <th scope="col">Batches</th>
                                <th scope="col">Total Stocks</th>
                                <th scope="col">Latest Date Stocked</th>
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
                                <th class="total_quantity" data-warning-level="{{ $stock->warning_level }}" data-max-limit="{{ $stock->max_limit }}">{{ $stock->total_quantity }}</th>
                                <th>{{ $stock->latest_stock }}</th>
                                <td>
                                    <a href="{{ route('manager.add-to-stocks', ['id' => $stock->id]) }}" class="btn btn-outline-secondary">View Batches</a>
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