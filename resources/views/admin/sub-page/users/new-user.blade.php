@php
use Illuminate\Support\Facades\Session;
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
                <div class="container-md">
                    <h2 class="modal-title" id="exampleModalLabel">New user</h2>
                    <form action="{{ route('admin.save-user') }}" method="POST" id="user_form">
                        @csrf
                        <div class="modal-body">
                            <div class="container-sm mb-2">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}">
                                @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-2">
                                <label for="type">User type</label>
                                <select name="type" class="form-control" id="type">
                                    <option value="">Select</option>
                                    <option value="0">User</option>
                                    <option value="1">Admin</option>
                                    <option value="2">Manager</option>
                                </select>
                                @error('type')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-4" id="department_div" style="display: none;">
                                <label for="department">Department</label>
                                <select name="department" class="form-control" id="department">
                                    <option value="">Select</option>
                                    <option value="0">Pharmacy</option>
                                    <option value="1">Csr</option>
                                </select>
                                @error('department')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-2">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" name="username" id="username" value="{{ old('username') }}">
                                @error('username')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-2">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" name="password" id="password" value="{{ old('password') }}">
                                @error('password')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-4">
                                <label for="admin_password">Admin password</label>
                                <input type="password" class="form-control" name="admin_password" id="admin_password">
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

                            <div class="container-sm">
                                <a href="{{ route('admin.users') }}" class="btn btn-secondary">Back</a>
                                <button type="submit" class="btn btn-primary">Add user</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    deptShow();

    function deptShow() {
        const userTypeSelect = document.getElementById("type");
        const departmentDiv = document.getElementById("department_div");

        userTypeSelect.addEventListener("change", function() {
            if (userTypeSelect.value === "0") {
                departmentDiv.style.display = "block";
            } else {
                departmentDiv.style.display = "none";
            }
        });
    }

    function click() {
        alert("clicked");
    }
</script>
@endsection