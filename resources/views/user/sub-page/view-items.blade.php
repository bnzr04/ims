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
                    @if($request->status !== 'completed')
                    <a href="/user/view-request/all" class="btn btn-secondary">Back</a>
                    @endif

                    @if($request->status === 'completed')
                    <a href="/user/view-request/completed" class="btn btn-secondary">Back</a>
                    @endif
                </div>
                <div class="container-md mt-3 pt-2 pb-2 shadow lh-1 rounded overflow-auto">
                    <h4>Request Details</h4>
                    <table class="table">
                        <thead class="bg-success text-white">
                            <tr>
                                <th scope="col">Request ID</th>
                                <th scope="col">Office</th>
                                <th scope="col">Request By</th>
                                <th scope="col">Request To</th>
                                <th scope="col">Request status</th>
                                <th scope="col">Request date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="col">{{ $request->id }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->office }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->request_by }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->request_to }}</td>
                                <td scope="col" class="text-capitalize">{{ $request->status }}</td>
                                <td scope="col">{{ $request->formatted_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Stock ID</th>
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
                                    <td scope="col" class="text-capitalize">{{ $item->exp_date }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8">
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
                        <button type="submit" class="btn btn-warning shadow">Received</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection