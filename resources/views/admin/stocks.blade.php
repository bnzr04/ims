@section('content')
<div class="col-9">
    <h2>ITEM STOCKS</h2>
    <button type="button" class="btn btn-success">Add Item</button>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Item ID</th>
                <th scope="col">Item Name</th>
                <th scope="col">Category</th>
                <th scope="col">Stocks</th>
                <th scope="col">Last Stocked</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th scope="row">1</th>
                <td>Rgb Mouse</td>
                <td>Computer Equipment</td>
                <td>5</td>
                <td>01-25-2023</td>
                <td>
                    <button type="button" class="btn btn-primary">Edit</button>
                    <button type="button" class="btn btn-danger">Delete</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection