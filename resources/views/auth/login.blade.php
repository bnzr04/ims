@extends('layouts.app')

@include('layouts.header')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center m-0">
        <div class="col-md-8" style="align-items: center;">
            <div class="card shadow-lg" style="color:white;">
                <div class="card-header text-center" style="background-color:#77ab59;letter-spacing: 5px; align-items:center">
                    <img src="{{asset('logos/amante-logo.png')}}" alt="">
                    <h3 class="mt-3">LOGIN</h3>
                </div>

                <div class="card-body" style="background-color:#36802d;">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="username" class="col-md-4 col-form-label text-md-end" style="letter-spacing: 3px;">{{ __('Username') }}</label>

                            <div class="col-md-6">
                                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

                                @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end" style="letter-spacing: 3px;">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn" style="background-color:#c9df8a;letter-spacing: 3px;">
                                    {{ __('Login') }}
                                </button>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="container-lg my-3" style="text-align: center;">
                    <a href="{{ route('info') }}" class="btn p-2" style="text-decoration: none;color:#36802d;border:#36802d 2px solid;border-radius:75%" onmouseover="this.style.color='#c9df8a';this.style.borderColor='#c9df8a';" onmouseout="this.style.color='#36802d';this.style.borderColor='#36802d';">Info</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection