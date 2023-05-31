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
                <h2 style="letter-spacing: 3px;">DASHBOARD</h2>
                <div class="container-lg p-2 d-flex" style="flex-wrap:wrap;">
                    <div class="m-2 p-2" style="width: 200px;height: 120px;background-color:	#005073;color:white">
                        Pending Request
                        <hr>
                        <h3><a href="{{ route('user.viewRequest', ['request' => 'pending', 'filter' => 'today']) }}" id="pending-request" style="color:white;"></a></h3>
                    </div>
                    <div class="m-2 p-2" style="width: 200px;height: 120px;background-color:	#107dac;color:white">
                        Accepted Request
                        <hr>
                        <h3><a href="{{ route('user.viewRequest', ['request' => 'accepted', 'filter' => 'today']) }}" id="accepted-request" style="color:white;"></a></h3>
                    </div>
                    <div class="m-2 p-2" style="width: 200px;height: 120px;background-color:	#189ad3;color:white">
                        Delivered Request
                        <hr>
                        <h3><a href="{{ route('user.viewRequest', ['request' => 'delivered', 'filter' => 'today']) }}" id="delivered-request" style="color:white;"></a></h3>
                    </div>
                    <div class="m-2 p-2" style="width: 200px;height: 120px;background-color:	#1ebbd7;color:white">
                        Completed Request
                        <hr>
                        <h3><a href="{{ route('user.viewRequest', ['request' => 'completed', 'filter' => 'today']) }}" id="completed-request" style="color:white;"></a></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const pending = document.getElementById("pending-request");
    const accepted = document.getElementById("accepted-request");
    const delivered = document.getElementById("delivered-request");
    const completed = document.getElementById("completed-request");

    function dataUpdate() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', "{{ route('user.dashboard-data') }}", true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                // console.log(data);

                pending.innerHTML = "";
                pending.innerHTML = data.pending;

                accepted.innerHTML = "";
                accepted.innerHTML = data.accepted;

                delivered.innerHTML = "";
                delivered.innerHTML = data.delivered;

                completed.innerHTML = "";
                completed.innerHTML = data.completed;

            } else {
                console.log('Error: ' + xhr.status);
            }
        };

        xhr.send();
    }

    dataUpdate();
    setInterval(dataUpdate, 10000);
</script>
@endsection