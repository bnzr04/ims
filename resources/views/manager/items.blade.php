@php
use Illuminate\Support\Facades\Session
@endphp
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
            <div id="content" class="px-2 pt-1 pb-5 container-fluid">
                <h2>ITEMS</h2>
                <div class="container-fluid p-0 mt-1 d-flex justify-content-between" style="flex-wrap: wrap;">
                    <div class="container-sm p-0 m-0" style="display: flex;flex-wrap:wrap">
                        <a href="{{ route('manager.new-item') }}" class="btn btn-success m-1">New Item</a>
                        <a href="{{ route('manager.stocks') }}" class="btn btn-secondary m-1">All Stocks</a>
                    </div>
                </div>

                <div class="container-fluid d-flex p-0 mt-2" style="flex-wrap:wrap">
                    <form action="" method="get" class="d-flex mx-1 my-1 m-0">
                        <div class="input-group flex-nowrap m-0 p-0" style="width: 300px;z-index:0;">
                            <input type="text" class="form-control bg-white" placeholder="Search name..." aria-label="search" aria-describedby="addon-wrapping" name="search" id="search" value="{{ $search == true ? $search : ''}}">
                            <button type="submit" class="btn btn-outline-secondary">Search</button>
                        </div>
                    </form>

                    <form action="{{ route('manager.items') }}" method="get" style="z-index:0;" class="mx-1 my-1 m-0">
                        <div class="container-sm p-0 input-group" style="width: 300px;">
                            <select name="category" class="form-select text-capitalize" id="category">
                                @if($category)
                                <option value="{{ $category }}">{{ $category }}</option>
                                @endif
                                <option value="">All</option>
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
                            <p class="m-0"><a href="{{ route('manager.items') }}?filter=max" class="level">Over Max Limit</a></p>
                        </div>
                        <div class="container-sm d-flex p-0" style="align-items: center;">
                            <div class="m-1" style="width:40px;height:100%;background-color:#1ea200">&nbsp;</div>
                            <p class="m-0"><a href="{{ route('manager.items') }}?filter=safe" class="level">Safe Level</a></p>
                        </div>
                        <div class="container-sm d-flex p-0" style="align-items: center;">
                            <div class="m-1" style="width:40px;height:100%;background-color:#d67b00">&nbsp;</div>
                            <p class="m-0"><a href="{{ route('manager.items') }}?filter=warning" class="level">Warning Level</a></p>
                        </div>
                        <div class="container-sm d-flex p-0" style="align-items: center;">
                            <div class="m-1" style="width:40px;height:100%;background-color:#dc0f00">&nbsp;</div>
                            <p class="m-0"><a href="{{ route('manager.items') }}?filter=no-stocks" class="level">No Stocks</a></p>
                        </div>
                    </div>
                    <div class="container-sm m-0 p-0" style="width:100%;max-width:300px;">
                        <div class="container-sm d-flex p-0" style="align-items: center;">
                            <div class="m-1" style="min-width:40px;width:40px;height:100%;background-color:#fcd772">&nbsp;</div>
                            <p class="m-0">Has Expired/Expiring Stocks</p>
                        </div>
                    </div>
                    <div class="container-sm m-0 p-0" style="width:100%;max-width:300px;">
                        <div class="container-sm" style="letter-spacing: 2px;">
                            <h5>Mode of acquisition</h5>
                        </div>
                        <div class="container-sm d-flex p-0" style="align-items: center;">
                            <p class="m-0"><a href="{{ route('manager.items') }}" class="level">All</a></p>
                        </div>
                        <div class="container-sm d-flex p-0" style="align-items: center;">
                            <p class="m-0"><a href="{{ route('manager.items') }}?moa=petty-cash" class="level">Petty Cash</a></p>
                        </div>
                        <div class="container-sm d-flex p-0" style="align-items: center;">
                            <p class="m-0"><a href="{{ route('manager.items') }}?moa=donation" class="level">Donation</a></p>
                        </div>
                        <div class="container-sm d-flex p-0" style="align-items: center;">
                            <p class="m-0"><a href="{{ route('manager.items') }}?moa=lgu" class="level">LGU</a></p>
                        </div>
                    </div>

                    <div class="container-sm d-flex m-0 p-0" style="width:100%;max-width:200px;flex-direction:column-reverse">
                        <form class="m-0" action="{{ route('manager.export-items') }}" method="post">
                            @csrf
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                            <button class="btn btn-light border border-secondary" title="Download Report"><img src="{{ asset('/icons/excel-icon.png') }}" alt="excel-icon" width="20px"></button>
                        </form>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif

                @if(request()->input('moa'))
                <div class="container-fluid mt-2 p-1 bg-secondary">
                    @if(request()->input('moa') == 'petty-cash')
                    <h5 class="m-0 text-white">ITEMS WITH PETTY CASH STOCK BATCH:</h5>
                    @endif

                    @if(request()->input('moa') == 'donation')
                    <h5 class="m-0 text-white">ITEMS WITH DONATION STOCK BATCH:</h5>
                    @endif

                    @if(request()->input('moa') == 'lgu')
                    <h5 class="m-0 text-white">ITEMS WITH LGU STOCK BATCH:</h5>
                    @endif
                </div>
                @endif

                <div class="mt-2 p-0 border">
                    <table class="table" id="items_table">
                        <thead class="bg-success text-white" style="position: sticky;top: 55;">
                            <tr>
                                <th scope="col">Item ID</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Category</th>
                                <th scope="col">Unit</th>
                                <th scope="col">Price</th>
                                <th scope="col">Current Stock</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                            <tr style="border-bottom: 1px black solid;{{ $item->hasExpiredStocks || $item->isExpiringSoon ? 'background-color:#fcd772' : '' }}">
                                <th>{{ $item->id }}</th>
                                <td class="text-capitalize">{{ $item->name }}</td>
                                <td class="text-capitalize">{{ $item->description }}</td>
                                <td class="text-capitalize">{{ $item->category }}</td>
                                <td>{{ $item->unit }}</td>
                                <td>{{ is_null($item->price) ? "-" : $item->price}}</td>
                                @if($item->total_quantity !== null)
                                <th class="total_quantity" data-warning-level="{{ $item->warning_level }}" data-max-limit="{{ $item->max_limit }}">{{ $item->total_quantity }}</th>
                                @else
                                <th class="text-danger">No stocks</th>
                                @endif

                                <td class="d-flex border-0" style="flex-direction:column;">
                                    <a href="{{route('manager.add-to-stocks', ['id' => $item->id])}}" class="btn btn-secondary m-1" title="Add stocks">Stocks</a>
                                    <a href="{{route('manager.show-item', ['id' => $item->id])}}" class="btn btn-success m-1">Edit</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8">
                                    No items...
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
    function deleteUser() {
        if (!confirm("Are you sure you want to delete this item?\nThis item will be deleted even in the stocks if it's added.")) {
            event.preventDefault();
        };
    }

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

    // const warningLevel = totalQuantity.getAttribute('data-warning-level');
    // const totalQuantityValue = totalQuantity.innerHTML;
</script>
@endsection