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
                    <div class="container-lg">
                        <a href="{{ route('manager.requests') }}" class="btn btn-secondary mt-2 mb-1">Back</a>
                        <hr>
                    </div>
                    <form action="{{ route('manager.save-doctor') }}" method="post">
                        @csrf
                        <div class="container-lg d-flex p-0 m-0" style="align-items: center; max-width:450px;justify-content:space-around;flex-wrap:wrap">
                            <label for="doctor_name">New Doctor</label>
                            <input type="text" name="doctor_name" id="doctor_name" class="form-control border border-dark" style="max-width: 200px;">
                            <button class="btn btn-dark">Add Doctor</button>
                        </div>
                    </form>
                    <div class="container-lg mt-2">
                        <h2>Doctors</h2>
                    </div>

                    <div class="container-lg overflow-auto m-0 p-0 mt-1 border border-secondary shadow" style="height: 350px;max-width:500px">
                        <table class="table">
                            <thead class=" bg-dark text-white" style="position: sticky;top:0;">
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody id="doctors_table">
                                <tr>
                                    <td>Dr. Test</td>
                                    <td>
                                        <button>Edit</button>
                                        <button>Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection