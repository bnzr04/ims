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
                <h1>Users</h1>
                <div class="mt-3 mb-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#new-user-modal">New User</button>
                    @include('admin.modals.user.new-user')

                    <div class="input-group flex-nowrap" style="width: 270px;">
                        <span class="input-group-text" id="addon-wrapping">Search</span>
                        <input type="text" class="form-control bg-white" placeholder="" aria-label="search" aria-describedby="addon-wrapping">
                    </div>
                </div>
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
                                Password
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
                        @foreach($users as $user)
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
                                -----
                            </td>
                            <td>
                                {{ $user->type }}
                            </td>
                            <td>
                                {{ $user->department }}
                            </td>
                            <td>
                                <div class="d-grid gap-2 d-md-block">
                                    <a href="{{ route('admin.edit-user', ['id' => $user->id]) }}" class="btn btn-success" type="button">Edit</a>
                                    <button class="btn btn-danger" type="button">Delete</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </td>
                </tr>
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection