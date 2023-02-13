<aside>
    <nav class="navbar navbar-expand navbar-dark flex-md-column align-items-start py-2">
        <ul class="mb-0 p-0 text-white px-3" style="list-style:none;text-decoration:none;">

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
            <li><a href="{{ route('manager.home') }}" class="nav-link">Dashboard</a></li>
            <li><a href="{{ route('manager.stocks') }}" class="nav-link">Stocks</a></li>
            <li><a href="{{ route('manager.deployment') }}" class="nav-link">Deployment</a></li>
            <li><a href="{{ route('manager.requests') }}" class="nav-link">Requests</a></li>
            @endif

            {{--User links--}}
            @if(Auth::user()->type == 'user')
            <li><a href="{{ route('user.home') }}">Dashboard</a></li>
            <li><a href="{{ route('user.request') }}">My Requests</a></li>
            @endif

            @if(Auth::user())
            <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a></li>
            @endif
        </ul>
    </nav>
</aside>