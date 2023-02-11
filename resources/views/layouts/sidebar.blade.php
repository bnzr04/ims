<aside>
    <nav class="navbar navbar-expand navbar-dark flex-md-column align-items-start py-2">
        <ul class="mb-0 p-0 text-white px-3" style="list-style:none">

            {{--Admin links--}}
            @if( Auth::user()->type == 'admin' )
            <li><a href="" class="nav-link" data-view-name="admin.dashboard">Dashboard</a></li>
            <li><a href="" class="nav-link" data-view-name="admin.users">Users</a></li>
            <li><a href="" class="nav-link" data-view-name="admin.items">Items</a></li>
            <li><a href="" class="nav-link" data-view-name="admin.stocks">Stocks</a></li>
            <li><a href="" class="nav-link" data-view-name="admin.deployment">Deployment</a></li>
            <li><a href="" class="nav-link" data-view-name="admin.userRequest">Requests</a></li>
            <li><a href="" class="nav-link" data-view-name="admin.log">Log</a></li>
            @endif

            {{--Manager links--}}
            @if( Auth::user()->type == 'manager' )
            <li><a href="{{ route('manager.home') }}">Dashboard</a></li>
            <li><a href="{{ route('manager.stocks') }}">Stocks</a></li>
            <li><a href="{{ route('manager.deployment') }}">Deployment</a></li>
            <li><a href="{{ route('manager.requests') }}">Requests</a></li>
            @endif

            {{--User links--}}
            @if(Auth::user()->type == 'user')
            <li><a href="{{ route('user.home') }}">Dashboard</a></li>
            <li><a href="{{ route('user.request') }}">My Requests</a></li>
            @endif


        </ul>
    </nav>
</aside>