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
                    <h2 class="modal-title" id="exampleModalLabel">Delete user</h2>
                    <form action="{{ route('admin.delete-user', ['id' => $user->id ]) }}" method="POST" id="user_form">
                        @csrf
                        <div class="modal-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            User ID
                                        </th>
                                        <th scope="col">
                                            Name
                                        </th>
                                        <th scope="col">
                                            Username
                                        </th>
                                        <th scope="col">
                                            Type
                                        </th>
                                        <th scope="col">
                                            Department
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            {{ $user->id }}
                                        </th>
                                        <td>
                                            {{ $user->name }}
                                        </td>
                                        <td>
                                            {{ $user->username }}
                                        </td>
                                        <td>
                                            {{ $user->type }}
                                        </td>
                                        <td>
                                            {{ $user->dept }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="container-sm">
                                <div class="alert alert-warning">Deleting this account will be removed to database.</div>
                            </div>
                            <div class="container-sm mb-2">
                                <label for="admin_password">Admin Password</label>
                                <input type="password" class="form-control" name="admin_password" id="admin_password">
                                @error('admin_password')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                            @endif

                            <div class="container-sm">
                                <a href="{{ route('admin.users') }}" class="btn btn-secondary">Back</a>
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection