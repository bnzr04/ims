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
                    <h2 class="modal-title" id="exampleModalLabel">Edit user</h2>
                    <form action="{{ route('admin.update-user', ['id' => $user->id ]) }}" method="POST" id="user_form">
                        @csrf
                        <div class="modal-body mb-4">
                            <div class="container-sm mb-2">
                                <label for="name">User ID</label>
                                <input type="text" class="form-control" name="name" id="name" value="{{ $user->id }}" disabled>
                            </div>
                            <div class="container-sm mb-2">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" name="name" id="name" value="{{ $user->name }}">
                                @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-2">
                                <label for="type">User type</label>
                                <select name="type" class="form-control" id="type" style="text-transform:capitalize;">
                                    <!-- if $user->type is equal to user value = 0 else if $user->type is equal to admin value = 1 else if the $user->type is manager the value = 2 -->
                                    <option value="{{ $user->type == 'user' ? 0 : ( $user->type == 'admin' ? 1 : ( $user->type == 'manager' ? 2 : '') )  }}">{{ $user->type }}</option>
                                    <option value="0">User</option>
                                    <option value="1">Admin</option>
                                    <option value="2">Manager</option>
                                </select>
                                @error('usertype')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm mb-4" id="department_div" style="display: none;">
                                <label for="department">Department</label>
                                <select name="department" class="form-control" id="department" style="text-transform:capitalize;">
                                    <option value="{{ $user->department == 'pharmacy' ? 0 : ( $user->department == 'csr' ? 1 : '' ) }}">{{ $user->type == 'user' ? $user->department : '' }}</option>
                                    <option value="0">Pharmacy</option>
                                    <option value="1">Csr</option>
                                </select>
                                @error('userdept')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="container-sm">
                                <h3>Account Update</h3>
                                <div class="container-sm mb-2">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" name="username" id="username" value="{{ $user->username }}">
                                    @error('username')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="container-sm mb-2">
                                    <label for="password">New Password</label>
                                    <input type="password" class="form-control" name="password" id="password">
                                    @error('new_password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="container-sm mb-2">
                                    <label for="admin_password">Admin Password</label>
                                    <input type="password" class="form-control" name="admin_password" id="admin_password">
                                    @error('admin_password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
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

                        </div>
                        <div class="container-sm">
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Update</button>
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

        if (userTypeSelect.value === '0') {
            departmentDiv.style.display = "block";
        }

        userTypeSelect.addEventListener("change", function() {
            if (userTypeSelect.value === "0") {
                departmentDiv.style.display = "block";
            } else {
                departmentDiv.style.display = "none";
            }
        });
    }
</script>
@endsection