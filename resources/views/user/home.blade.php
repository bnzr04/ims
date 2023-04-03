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
                <h1>Dashboard</h1>
                <div class="container-fluid d-flex">
                    <div class="container1 rounded p-3 mx-1 bg-primary text-white" style="width: 150px;height:150px;display: flex;justify-content: center;align-items: center;flex-direction: column;letter-spacing:3px">
                        <h5>Pending</h5>
                        <h1><a href="{{ route('user.viewRequest', ['request' => 'pending']) }}" style="color:white;">{{ $pending }}</a></h1>
                    </div>
                    <div class="container2 rounded p-3 mx-1 bg-success text-white" style="width: 150px;height:150px;display: flex;justify-content: center;align-items: center;flex-direction: column;letter-spacing:3px">
                        <h5>Received</h5>
                        <h1><a href="{{ route('user.viewRequest', ['request' => 'completed']) }}" style="color:white;">{{ $completed }}</a></h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection