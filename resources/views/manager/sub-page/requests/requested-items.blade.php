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
                @if($request->status === 'pending' || $request->status === 'delivered')
                <div class="container-lg p-0">
                    <a href="{{ route('manager.requests') }}" class="btn btn-secondary">Back</a>
                </div>
                @endif

                @if($request->status === 'accepted')
                <div class="container-lg p-0">
                    <a href="{{ route('manager.requests') }}" class="btn btn-secondary" onclick="alert()">Back</a>
                </div>
                @endif

                @if($request->status === 'completed')
                <div class="container-lg p-0">
                    <a href="{{ route('manager.transaction') }}" class="btn btn-secondary">Back</a>
                </div>
                @endif

                <div class="container-md mt-3 pt-2 pb-2 shadow lh-1 rounded overflow-auto">
                    <h4>Request Details</h4>
                    <table class="table">
                        <thead class="bg-success text-white">
                            <tr>
                                <th scope="col" style="border: white 1px solid;">Request ID</th>
                                <th scope="col" style="border: white 1px solid;">Office</th>
                                <th scope="col" style="border: white 1px solid;">Requester</th>
                                <th scope="col" style="border: white 1px solid;">Request status</th>
                                @if(!is_null($request->accepted_by_user_name))
                                <th scope="col" style="border: white 1px solid;">Accepted By</th>
                                @endif
                                @if($request->status === "completed")
                                <th scope="col" style="border: white 1px solid;">Receiver</th>
                                @endif
                                <th scope="col" style="border: white 1px solid;">Request date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="col" style="border: gray 1px solid;">{{ $request->id }}</td>
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ $request->office }}</td>
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ $request->request_by }}</td>
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ $request->status }}</td>
                                @if(!is_null($request->accepted_by_user_name))
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ is_null($request->accepted_by_user_name) ? "-" : $request->accepted_by_user_name }}</td>
                                @endif
                                @if($request->status === "completed")
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ is_null($request->receiver) ? "-" : $request->receiver }}</td>
                                @endif
                                <td scope="col" style="border: gray 1px solid;">{{ $request->formatted_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <h4>Patient Info</h4>
                    <table class="table">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th scope="col" style="border: white 1px solid;">Patient Name</th>
                                <th scope="col" style="border: white 1px solid;">Patient Age</th>
                                <th scope="col" style="border: white 1px solid;">Patient Gender</th>
                                <th scope="col" style="border: white 1px solid;">Physician/Nurse Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="col" style="border: gray 1px solid;">{{ $request->patient_name }}</th>
                                <td scope="col" style="border: gray 1px solid;">{{ is_null($request->age) ? "0" : $request->age }}</td>
                                <td scope="col" style="border: gray 1px solid;">{{ is_null($request->gender) ? "-" : $request->gender }}</td>
                                <td scope="col" style="border: gray 1px solid;">{{ $request->doctor_name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- <div class="container-md mt-3 pt-2 pb-2 shadow lh-1 rounded overflow-auto">
                    <h4>Patient Details</h4>
                    <table class="table">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th scope="col">Patient name</th>
                                <th scope="col">Age</th>
                                <th scope="col">Gender</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="col" style="border: gray 1px solid;">{{ $request->patient_name }}</td>
                                <td scope="col" style="border: gray 1px solid;">{{ $request->patient_name }}</td>
                                <td scope="col" style="border: gray 1px solid;">{{ $request->patient_name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div> -->

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
                                    <th scope="col">Price</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Stock ID</th>
                                    <th scope="col">Mode Of Acq</th>
                                    <th scope="col">Lot #</th>
                                    <th scope="col">Block #</th>
                                    <th scope="col">Expiration</th>
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
                                    <td scope="col" class="text-capitalize">{{ is_null($item->price) ? "-" : $item->price }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->quantity }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->stock_id }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->mode_acquisition }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->lot_number ?? "-" }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->block_number ?? "-" }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->exp_date }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9">
                                        No Items...
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($request->status == 'pending')
                    <form action="{{ route('manager.accept-request',['rid' => $request->id]) }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-primary shadow">Accept request</button>
                    </form>
                    @endif
                    @if($request->status == 'accepted')
                    <form action="{{ route('manager.deliver-request',['rid' => $request->id]) }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-warning shadow">Mark as delivered</button>
                    </form>
                    @endif
                    @if($request->status == 'completed' || $request->status == 'delivered')
                    <form action="{{ route('manager.generate-receipt',['rid' => $request->id]) }}" target="_blank" method="get" style="float: right;">
                        @csrf
                        <button type="submit" class="btn btn-success shadow">Print</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    setTimeout(function() {
        document.getElementById('alert').style.display = 'none';
    }, 3000);

    function remove() {
        if (!confirm('Do you want to remove this item?')) {
            event.preventDefault();
        }
    }

    function alert() {
        if (!confirm('Do you want to go back and finish the dispensing later?')) {
            event.preventDefault();
        }
    }
</script>
@endsection