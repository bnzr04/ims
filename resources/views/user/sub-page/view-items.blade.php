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
                <div class="container-fluid">
                    <a href="{{ route('user.viewRequest',['request' => $request->status, 'filter' => 'today']) }}" class="btn btn-secondary">Back</a>
                </div>
                <div class="container-fluid mt-3 pt-2 pb-2 border shadow lh-1 rounded overflow-auto">
                    <div class="container-fluid m-0 p-0">
                        <h4>Request Details</h4>
                        <table class="table">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th scope="col" class="border border-dark">Request ID</th>
                                    <th scope="col" class="border border-dark">Office</th>
                                    <th scope="col" class="border border-dark">Requester</th>
                                    <th scope="col" class="border border-dark">Request To</th>
                                    <th scope="col" class="border border-dark">Request status</th>
                                    @if($request->status === "completed")
                                    <th scope="col" class="border border-dark">Receiver</th>
                                    @endif
                                    @if(!is_null($request->accepted_by_user_name))
                                    <th scope="col" class="border border-dark">Accepted By</th>
                                    @endif
                                    <th scope="col" class="border border-dark">Request date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="col" class="border border-dark">{{ $request->id }}</th>
                                    <td scope="col" class="text-capitalize border border-dark">{{ $request->office }}</td>
                                    <td scope="col" class="text-capitalize border border-dark">{{ $request->request_by }}</td>
                                    <td scope="col" class="text-capitalize border border-dark">{{ $request->request_to }}</td>
                                    <td scope="col" class="text-capitalize border border-dark">{{ $request->status }}</td>
                                    @if($request->status === "completed")
                                    <td scope="col" class="text-capitalize border border-dark">{{ is_null($request->receiver) ? "-" : $request->receiver }}</td>
                                    @endif
                                    @if(!is_null($request->accepted_by_user_name))
                                    <td scope="col" class="text-capitalize borde border-dark">{{ is_null($request->accepted_by_user_name) ? "-" : $request->accepted_by_user_name }}</td>
                                    @endif
                                    <td scope="col" class="border border-dark">{{ $request->formatted_date }}</td>
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
                                    <th scope="col" class="text-capitalize border border-dark">Date Canceled</th>
                                    <th scope="col" class="text-capitalize border border-dark">Reason of Cancelation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="col" style="border: gray 1px solid;">{{ $canceled->format_date }}</th>
                                    <td scope="col" style="border: gray 1px solid;">{{ $canceled->reason }}</td>
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
                                    <th scope="col" class="border border-dark">Patient Name</th>
                                    <th scope="col" class="border border-dark">Patient Age</th>
                                    <th scope="col" class="border border-dark">Patient Gender</th>
                                    <th scope="col" class="border border-dark">Physician/Nurse Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="col" class="border border-dark">{{ $request->patient_name }}</th>
                                    <td scope="col" class="border border-dark">{{ is_null($request->age) ? "0" : $request->age }}</td>
                                    <td scope="col" class="border border-dark">{{ is_null($request->gender) ? "-" : $request->gender }}</td>
                                    <td scope="col" class="border border-dark">{{ $request->doctor_name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="container-fluid p-0 border border-dark shadow p-3 mt-3 mb-2 bg-body rounded" style="height: auto">
                    <h4>Requested Items</h4>
                    <div class=" container-fluid p-0 mb-2 border" style="height:250px;overflow-y: scroll;">
                        <table class="table">
                            <thead class="text-white bg-secondary" style="position: sticky;top: 0;z-index: 0;">
                                <tr>
                                    <th scope="col">Item ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Stock ID</th>
                                    <th scope="col">Mode Of ACQ</th>
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
                                    <td scope="col" class="text-capitalize">{{ $item->quantity }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->stock_id }}</td>
                                    <td scope="col" class="text-capitalize">{{ $item->mode_acquisition }}</td>
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

                    <div class="container-fluid p-0 m-0">
                        @if($request->status == 'pending')
                        <form action="{{ route('user.cancel-request',['rid' => $request->id]) }}" method="post" class="m-0" id="cancel_request_form">
                            @csrf
                            <button type="submit" class="btn btn-danger" onclick="return cancelRequest()">Cancel Request</button>
                            <textarea name="canceled_reason" id="canceled_reason" class="form-control mt-2" placeholder="Please state the reason of cancelation..." required></textarea>
                        </form>
                        @endif
                        @if($request->status == 'delivered')

                        <form action="{{ route('user.receive-request',['rid' => $request->id]) }}" method="post" class="m-0">
                            @csrf
                            <div class="container-fluid d-flex m-0 p-0" style="flex-direction:column;">
                                <div class="container-md p-0">
                                    <label for="receiver_name" class="px-1" style="letter-spacing: 3px;"><strong>RECEIVER NAME</strong></label>
                                </div>

                                <div class="container-fluid p-0 mt-2">
                                    <div class="container-md">
                                        <label for="requester">Requester</label>
                                        <input type="radio" name="receiver_name" id="requester" onchange="getRequesterValue()" value="{{ $request->request_by }}" checked>
                                    </div>

                                    <div class="container-fluid">
                                        <label for="other">Other</label>
                                        <input type="radio" name="receiver_name" id="other" onchange="getOtherRequesterValue()">
                                        <input type="text" class="form-control" id="receiver_name" style="max-width: 300px;" oninput="onInputReceiverName()">
                                    </div>

                                    <div class="container-fluid mt-3">
                                        <button type="submit" class="btn btn-warning shadow" style="letter-spacing: 3px;">Received</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endif
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

    const requesterRadio = document.getElementById("requester");
    const otherRadio = document.getElementById("other");
    const receiverNameInput = document.getElementById("receiver_name");

    if (requesterRadio.checked) {
        if (requesterRadio.value !== "") {
            console.log(requesterRadio.value);
        }
    }

    function getOtherRequesterValue() {
        if (otherRadio.checked) {
            receiverNameInput.required = true;
            if (receiverNameInput.value.trim() !== "") {
                otherRadio.value = receiverNameInput.value;
                console.log(otherRadio.value);
            } else {
                receiverNameInput.required = true;
            }
        }
    }

    function onInputReceiverName() {
        receiverNameInput.required = true;
        if (receiverNameInput.value.trim() !== "") {
            otherRadio.value = receiverNameInput.value.trim();
            console.log(otherRadio.value);
            receiverNameInput.setCustomValidity("");
        } else {
            receiverNameInput.setCustomValidity("Receiver name is required.");
            console.log(otherRadio.value);
        }
    }

    function getRequesterValue() {
        receiverNameInput.setCustomValidity("");
        receiverNameInput.required = false;
        receiverName = requesterRadio.value;
        requesterRadio.value = receiverName;
        console.log(receiverName);
    }

    function cancelRequest() {
        return confirm("Do you want to cancel this request?");
    }
</script>
@endsection