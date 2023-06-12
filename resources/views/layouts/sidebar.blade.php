<aside style="position:fixed;">
    <nav class="navbar navbar-expand navbar-dark flex-md-column align-items-start py-2">
        <ul class="mb-0 p-0 text-white px-3" style="list-style:none;text-decoration:none;">

            {{--Admin links--}}
            @if( Auth::user()->type == 'admin' )
            <li><a href="{{ route('admin.dashboard') }}" class="nav-link text-white">Dashboard</a></li>
            <li><a href="{{ route('admin.users') }}" class="nav-link text-white">Users</a></li>
            <li><a href="{{ route('admin.items') }}" class="nav-link text-white">Items & Stocks</a></li>
            <li><a href="{{ route('admin.requests') }}" class="nav-link text-white">Requests</a></li>
            <li><a href="{{ route('admin.all-transaction') }}" class="nav-link text-white">Transactions</a></li>
            <li><a href="{{ route('admin.dispense') }}" class="nav-link text-white">Dispense</a></li>
            <li><a href="{{ route('admin.log-index') }}" class="nav-link text-white">Log</a></li>
            @endif

            {{--Manager links--}}
            @if( Auth::user()->type == 'manager' )
            <li><a href="{{ route('manager.home') }}" class="nav-link text-white">Dashboard</a></li>
            <li><a href="{{ route('manager.stocks') }}" class="nav-link text-white">Items & Stocks</a></li>
            <li><a href="{{ route('manager.requests') }}" class="nav-link text-white">Requests</a></li>
            <li><a href="{{ route('manager.all-transaction') }}" class="nav-link text-white">Transactions</a></li>
            <li><a href="{{ route('manager.dispense') }}" class="nav-link text-white">Dispense</a></li>
            @endif

            {{--User links--}}
            @if(Auth::user()->type == 'user')
            <li><a href="{{ route('user.home') }}" class="nav-link text-white">Dashboard</a></li>
            <li><a href="{{ route('user.request') }}" class="nav-link text-white">Request</a></li>
            @endif

            @if(Auth::user())
            <li>
                <a class="nav-link text-white" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>
            </li>
            @endif
        </ul>
    </nav>
</aside>