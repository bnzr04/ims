<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IMS') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- CSS Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">


    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])


</head>

<body>
    <div id="app">
        <div>
            @yield('content')
        </div>
    </div>

    <!--Jquery-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            adminSidebar();
            adminHome();

            /* ADMIN FUNCTIONS START */

            function adminSidebar() {
                $('.nav-link').click(function(event) {
                    event.preventDefault();

                    var viewName = $(this).data('view-name');
                    var url = '/admin/sidebar/' + viewName;

                    $.get(url, function(data) {
                        $('#content').html(data);
                    });
                });
            }

            function adminHome() {
                $.get("{{ route('admin.dashboard')}}", function(data) {
                    $('#content').html(data);
                });
            }

            $('#itemForm').on('submit', function(event) {
                event.preventDefault();

                var url = $(this).attr('action');
                var form = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: form,
                    success: function(response) {
                        console.log(response)
                        $('#newItem').modal('hide');
                        $('#itemForm')[0].reset();
                        alert('Item added');
                    },
                    error: function(error) {
                        console.log(error);
                        alert('Data not inserted');
                    }
                });
            });

        });
    </script>
</body>

</html>