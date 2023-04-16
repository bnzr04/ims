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
                <div class="container-lg mt-2">
                    <a href="{{ route('user.home') }}" class="btn btn-secondary">Back</a>
                </div>
                <div class="container-lg mt-3 p-2 pt-3 rounded shadow">
                    <div class="container-md">
                        @if($request === 'completed')
                        <h5>Completed / Received requests</h5>
                        @endif
                    </div>
                    <div class="container-md d-flex">
                        <form id="pending_form" class="mx-1" method="get">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Pending (1)</button>
                        </form>
                        <form id="accepted_form" class="mx-1" method="get">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Accepted (1)</button>
                        </form>
                        <form id="delivered_form" class="mx-1" method="get">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Delivered (1)</button>
                        </form>
                    </div>
                    <div class="container-md overflow-auto" style="height: 400px;">
                        <table class="table">
                            <thead class="bg-success text-white" style="position: sticky;top:0;">
                                <tr>
                                    <th scope="col">Req ID</th>
                                    <th scope="col">Date-time</th>
                                    <th scope="col">Office</th>
                                    <th scope="col">Patient Name</th>
                                    <th scope="col">Request By</th>
                                    <th scope="col">Request to</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>{{$item->id}}</td>
                                    <td>{{$item->formatted_date}}</td>
                                    <td>{{$item->office}}</td>
                                    <td>{{$item->patient_name}}</td>
                                    <td>{{$item->request_by}}</td>
                                    <td>{{$item->request_to}}</td>
                                    <td>{{$item->status}}</td>
                                    <td>
                                        <a href="{{ route('user.request-items',['id' => $item->id]) }}" class="btn btn-secondary">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7">No Request...</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection