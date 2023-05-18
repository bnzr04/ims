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
                <div class="container-lg">
                    <a href="{{ route('user.viewRequest',['request' => $request->status, 'filter' => 'today']) }}" class="btn btn-secondary">Back</a>
                </div>
                <div class="container-md mt-3 pt-2 pb-2 shadow lh-1 rounded overflow-auto">
                    <h4>Request Details</h4>
                    <table class="table">
                        <thead class="bg-success text-white">
                            <tr>
                                <th scope="col" style="border: white 1px solid;">Request ID</th>
                                <th scope="col" style="border: white 1px solid;">Office</th>
                                <th scope="col" style="border: white 1px solid;">Requester</th>
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
                                <td scope="col" style="border: gray 1px solid;">{{ $request->id }}</td>
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
                <div class="container-lg p-0 border border-dark shadow p-3 mt-3 mb-2 bg-body rounded" style="height: auto">
                    <h4>Requested Items</h4>
                    <div class=" container-lg p-0 mb-2 border" style="height:230px;overflow-y: auto;">
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
                    @if($request->status == 'delivered')

                    <form action="{{ route('user.receive-request',['rid' => $request->id]) }}" method="post">
                        @csrf
                        <div class="container-md d-flex m-0 p-0" style="flex-direction:column;">
                            <div class="container-md p-0">
                                <label for="receiver_name" class="px-1"><strong>RECEIVER NAME</strong></label>
                            </div>

                            <div class="container-md p-0 mt-2">
                                <div class="container-md">
                                    <label for="requester">Requester</label>
                                    <input type="radio" name="receiver_name" id="requester" value="{{ $request->request_by }}" onchange="checkOtherOption()">
                                </div>

                                <div class="container-md">
                                    <label for="other">Other</label>
                                    <input type="radio" name="receiver_name" id="other" onchange="checkOtherOption()">
                                    <input type="text" class="form-control" name="receiver_name" id="receiver_name" style="max-width: 400px; display:none;">
                                </div>

                                <div class="container-md mt-1">
                                    <button type="submit" class="btn btn-warning shadow">Received</button>
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
<script>
    function checkOtherOption() {
        const requesterRadio = document.getElementById("requester");
        const otherRadio = document.getElementById("other");
        const receiverNameInput = document.getElementById("receiver_name");

        if (otherRadio.checked) {
            const receiverName = receiverNameInput.value;
            receiverNameInput.style.display = "block";
            console.log(receiverName);
        } else if (requesterRadio.checked) {
            receiverNameInput.value = "";
            receiverNameInput.style.display = "none";
            console.log(requesterRadio.value);
        }
    }
</script>
@endsection