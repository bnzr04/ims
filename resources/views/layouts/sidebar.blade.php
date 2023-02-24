<aside>
    <nav class="navbar navbar-expand navbar-dark flex-md-column align-items-start py-2">
        <ul class="mb-0 p-0 text-white px-3" style="list-style:none;text-decoration:none;">

            {{--Admin links--}}
            @if( Auth::user()->type == 'admin' )
            <li><a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a></li>
            <li><a href="{{ route('admin.users') }}" class="nav-link">Users</a></li>
            <li><a href="{{ route('admin.items') }}" class="nav-link">Items</a></li>
            <li><a href="{{ route('admin.deployment') }}" class="nav-link">Deployment</a></li>
            <li><a href="{{ route('admin.requests') }}" class="nav-link">Requests</a></li>
            <li><a href="{{ route('admin.log') }}" class="nav-link">Log</a></li>
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
<!-- <script type="text/javascript">
    $(document).ready(function() {

        adminSidebar();

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

    });
</script> -->