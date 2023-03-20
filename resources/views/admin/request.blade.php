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
                    <h2>Requests</h2>
                </div>
                <div class="container-lg mt-3 p-2 pt-3 rounded shadow">
                    <div class="container-md">
                        <h5>Pending requests</h5>
                    </div>
                    <table class="table">
                        <thead class="bg-success text-white">
                            <tr>
                                <th scope="col">Req ID</th>
                                <th scope="col">Date-time</th>
                                <th scope="col">Office</th>
                                <th scope="col">Request by</th>
                                <th scope="col">Request to</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pending as $pending)
                            <tr>
                                <td>{{$pending->id}}</td>
                                <td>{{$pending->formatted_date}}</td>
                                <td>{{$pending->office}}</td>
                                <td>{{$pending->request_by}}</td>
                                <td>{{$pending->request_to}}</td>
                                <td>{{$pending->status}}</td>
                                <td>
                                    <a href="{{ route('admin.requested-items',['id' => $pending->id]) }}" class="btn btn-secondary">View</a>
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

                <div class="container-lg mt-3 p-2 pt-3 rounded shadow">
                    <div class="container-md">
                        <h5>Accepted requests</h5>
                    </div>
                    <table class="table">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th scope="col">Req ID</th>
                                <th scope="col">Date-time</th>
                                <th scope="col">Office</th>
                                <th scope="col">Request by</th>
                                <th scope="col">Request to</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accepted as $accepted)
                            <tr>
                                <td>{{$accepted->id}}</td>
                                <td>{{$accepted->formatted_date}}</td>
                                <td>{{$accepted->office}}</td>
                                <td>{{$accepted->request_by}}</td>
                                <td>{{$accepted->request_to}}</td>
                                <td>{{$accepted->status}}</td>
                                <td>
                                    <a href="{{ route('admin.requested-items',['id' => $accepted->id]) }}" class="btn btn-secondary">View</a>
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

                <div class="container-lg mt-3 p-2 pt-3 rounded shadow">
                    <div class="container-md">
                        <h5>Delivered requests</h5>
                    </div>
                    <table class="table">
                        <thead class="bg-warning text-white">
                            <tr>
                                <th scope="col">Req ID</th>
                                <th scope="col">Date-time</th>
                                <th scope="col">Office</th>
                                <th scope="col">Request by</th>
                                <th scope="col">Request to</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($delivered as $delivered)
                            <tr>
                                <td>{{$delivered->id}}</td>
                                <td>{{$delivered->formatted_date}}</td>
                                <td>{{$delivered->office}}</td>
                                <td>{{$delivered->request_by}}</td>
                                <td>{{$delivered->request_to}}</td>
                                <td>{{$delivered->status}}</td>
                                <td>
                                    <a href="{{ route('admin.requested-items',['id' => $delivered->id]) }}" class="btn btn-secondary">View</a>
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

                <div class="container-lg mt-3 p-2 pt-3 rounded shadow">
                    <div class="container-md">
                        <h5>Completed requests</h5>
                    </div>
                    <table class="table">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th scope="col">Req ID</th>
                                <th scope="col">Date-time</th>
                                <th scope="col">Office</th>
                                <th scope="col">Request by</th>
                                <th scope="col">Request to</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completed as $completed)
                            <tr>
                                <td>{{$completed->id}}</td>
                                <td>{{$completed->formatted_date}}</td>
                                <td>{{$completed->office}}</td>
                                <td>{{$completed->request_by}}</td>
                                <td>{{$completed->request_to}}</td>
                                <td>{{$completed->status}}</td>
                                <td>
                                    <a href="{{ route('admin.requested-items',['id' => $completed->id]) }}" class="btn btn-secondary">View</a>
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
@endsection