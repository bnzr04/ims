<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>IMS PHARMACY</title>

    <!-- CSS bootstrap Link -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Font -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Jquery link -->
    <script src="{{ asset('js/jquery-3.7.0.min.js') }}"></script>

    <!-- select 2 css link-->
    <link rel="stylesheet" href="{{ asset('select2/dist/css/select2.min.css') }}">

    <!-- css bootstrap js -->
    <script src="{{ asset('bootstrap/js/bootstrap.min.js') }}"></script>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        * {
            font-family: 'Montserrat', sans-serif;
        }

        .hidden {
            display: none;
        }
    </style>

</head>

<body>
    @yield('content')

    <!-- select2 js -->
    <script src="{{ asset('select2/dist/js/select2.min.js') }}"></script>
</body>

</html>