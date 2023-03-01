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
                <h2>Users</h2>
                <div class="mt-3 mb-3 d-flex justify-content-between">
                    <a href="{{ route('admin.new-user') }}" class="btn btn-success">Add User</a>

                    <div class="input-group flex-nowrap" style="width: 270px;">
                        <span class="input-group-text" id="addon-wrapping">Search</span>
                        <input type="text" class="form-control bg-white" placeholder="" aria-label="search" aria-describedby="addon-wrapping">
                    </div>
                </div>
                <div class="container-lg p-0 mb-2">
                    <ul class="list-group list-group-horizontal">
                        <li class="list-group-item"><a href="">All</a></li>
                        <li class="list-group-item"><a href="">Admins</a></li>
                        <li class="list-group-item"><a href="">Managers</a></li>
                        <li class="list-group-item"><a href="">Users</a></li>
                    </ul>
                </div>
                <table class="table">
                    <thead class="bg-success text-white">
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
                            <th scope="col">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
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
                                {{ $user->dept == '' ? '------' : $user->dept }}
                            </td>
                            <td>
                                <div class="d-grid gap-2 d-md-block">
                                    <a href="{{ route('admin.show-user', ['id' => $user->id]) }}" class="btn btn-success">Edit</a>
                                    <a href="{{ route('admin.to-delete-user', ['id' => $user->id])}}" class="btn btn-danger" onclick="deleteUser()">Delete</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr colspan="7">
                            No User data
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(session('success'))
            <div class="container-sm alert alert-success" id="deleteSuccess">
                {{ session('success') }}
            </div>
            @endif

        </div>
    </div>
</div>
<script>
    deleteMessage();

    function deleteUser() {
        if (!confirm('Are you want to delete this user?')) {
            event.preventDefault();
        };
    }

    function deleteMessage() {
        var deleteSuccessMessage = document.getElementById('deleteSuccess');

        setTimeout(function() {
            deleteSuccessMessage.classList.add('hidden');
        }, 2000);
    }
</script>
@endsection