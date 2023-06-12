@php
use Illuminate\Support\Facades\Session
@endphp
@extends('layouts.app')
@section('content')
<div class="container-fluid ">
    <div id="content" class="px-2 py-1">
        <div class="container-fluid">
            <div class="container-fluid mt-3 pt-2 pb-2 shadow lh-1 rounded overflow-auto">
                <div class="container-fluid m-0 p-0">
                    <h4>Request Details</h4>
                    <table class="table">
                        <thead class="bg-success text-white">
                            <tr>
                                <th scope="col" style="border: white 1px solid;">Request ID</th>
                                <th scope="col" style="border: white 1px solid;">Office</th>
                                <th scope="col" style="border: white 1px solid;">Request By</th>
                                <th scope="col" style="border: white 1px solid;">Request To</th>
                                <th scope="col" style="border: white 1px solid;">Request status</th>
                                @if(!is_null($request->accepted_by_user_name))
                                <th scope="col" style="border: white 1px solid;">Accepted By</th>
                                @endif
                                <th scope="col" style="border: white 1px solid;">Request date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="col" style="border: gray 1px solid;">{{ $request->id }}</th>
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ $request->office }}</td>
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ $request->request_by }}</td>
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ $request->request_to }}</td>
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ $request->status }}</td>
                                @if(!is_null($request->accepted_by_user_name))
                                <td scope="col" class="text-capitalize" style="border: gray 1px solid;">{{ is_null($request->accepted_by_user_name) ? "-" : $request->accepted_by_user_name }}</td>
                                @endif
                                <td scope="col" style="border: gray 1px solid;">{{ $request->formatted_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @if($request->status === "canceled")
                <div class="container-fluid m-0 p-0">
                    <h4>Cancelation Details</h4>
                    <table class="table">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th scope="col" style="border: white 1px solid;">Date Canceled</th>
                                <th scope="col" style="border: white 1px solid;">Reason of Cancelation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="col" style="border: gray 1px solid;">{{ $canceled->format_date }}</th>
                                <td scope="col" style="border: gray 1px solid;">{{ ucfirst($canceled->reason) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
                <div class="container-fluid m-0 p-0">
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
                                <th scope="col" style="border: gray 1px solid;">{{ ucwords($request->patient_name) }}</th>
                                <td scope="col" style="border: gray 1px solid;">{{ is_null($request->age) ? "0" : $request->age }}</td>
                                <td scope="col" style="border: gray 1px solid;">{{ is_null($request->gender) ? "-" : $request->gender }}</td>
                                <td scope="col" style="border: gray 1px solid;">{{ ucwords($request->doctor_name) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

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

            <div class="container-fluid p-0 border border-dark shadow p-3 mt-3 mb-2 bg-body rounded" style="height: 340px;max-height:400px">
                <h4>Requested Items</h4>
                <div class=" container-fluid p-0" style="height:230px;overflow-y: auto;">
                    <table class="table">
                        <thead class="text-white bg-secondary" style="position: sticky;top: 0;z-index: 0;">
                            <tr>
                                <th scope="col" class="border">Item ID</th>
                                <th scope="col" class="border">Name</th>
                                <th scope="col" class="border">Description</th>
                                <th scope="col" class="border">Category</th>
                                <th scope="col" class="border">Unit</th>
                                <th scope="col" class="border">Price</th>
                                <th scope="col" class="border">Quantity</th>
                                <th scope="col" class="border">Stock ID</th>
                                <th scope="col" class="border">Mode Of Acq</th>
                                <th scope="col" class="border">Expiration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requestItems as $item)
                            <tr>
                                <td scope="col" class="border">{{ $item->item_id }}</td>
                                <td scope="col" class="text-capitalize border">{{ $item->name }}</td>
                                <td scope="col" class="text-capitalize border">{{ $item->description }}</td>
                                <td scope="col" class="text-capitalize border">{{ $item->category }}</td>
                                <td scope="col" class="text-capitalize border">{{ $item->unit }}</td>
                                <td scope="col" class="text-capitalize border">{{ is_null($item->price) ? "-" : $item->price }}</td>
                                <td scope="col" class="text-capitalize border">{{ $item->quantity }}</td>
                                <td scope="col" class="text-capitalize border">{{ $item->stock_id }}</td>
                                <td scope="col" class="text-capitalize border">{{ $item->mode_acquisition }}</td>
                                <td scope="col" class="text-capitalize border">{{ $item->exp_date }}</td>
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