<aside class="border border-danger" style="width:12.5rem;">
    <nav class="navbar navbar-expand navbar-dark flex-md-column flex-row align-items-start py-2">
        <ul class="mb-0 p-0" style="list-style:none;color:white">
            <li><a href="">Dashboard</a></li>
            @if(Auth::user()->type == 'user')
            <li><a href="">My Requests</a></li>
            @endif
            @if( Auth::user()->type == 'admin' || Auth::user()->type == 'manager')
            <li><a href="">Stocks</a></li>
            <li><a href="">Deployment</a></li>
            <li><a href="">Requests</a></li>
            @if( Auth::user()->type == 'admin')
            <li><a href="">Users</a></li>
            @endif
            <li><a href="">Log</a></li>
            @endif
        </ul>
    </nav>
</aside>