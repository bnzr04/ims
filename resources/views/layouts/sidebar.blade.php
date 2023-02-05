<aside class="border border-danger" style="width:12.5rem;">
    <nav class="navbar navbar-expand navbar-dark flex-md-column flex-row align-items-start py-2">
        <ul class="mb-0 p-0" style="list-style:none;color:white">

            {{--Admin links--}}
            @if( Auth::user()->type == 'admin' )
            <li><a href="{{ route('admin.home') }}">Dashboard</a></li>
            <li><a href="{{ route('admin.users') }}">Users</a></li>
            <li><a href="{{ route('admin.items') }}">Items</a></li>
            <li><a href="{{ route('admin.stocks') }}">Stocks</a></li>
            <li><a href="{{ route('admin.deployment') }}">Deployment</a></li>
            <li><a href="{{ route('admin.requests') }}">Requests</a></li>
            <li><a href="{{ route('admin.log') }}">Log</a></li>
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