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
                        @if($request === 'pending')
                        <h5>Pending requests</h5>
                        @endif
                        @if($request === 'completed')
                        <h5>Completed / Received requests</h5>
                        @endif
                    </div>
                    <div class="container-md overflow-auto" style="height: 400px;">
                        <table class="table">
                            <thead class="bg-success text-white" style="position: sticky;top:0;">
                                <tr>
                                    <th scope="col">Req ID</th>
                                    <th scope="col">Date-time</th>
                                    <th scope="col">Office</th>
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