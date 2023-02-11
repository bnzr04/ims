<div class="col-10 px-3 py-2 border border-dark">
    <h2>ITEM STOCKS</h2>
    <div class="mt-3 d-flex justify-content-between">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newItem">New Item</button>
        @include('admin.modals.new-item')

        <div class="input-group flex-nowrap" style="width: 270px;">
            <span class="input-group-text" id="addon-wrapping">Search</span>
            <input type="text" class="form-control bg-white" placeholder="" aria-label="search" aria-describedby="addon-wrapping">
        </div>
    </div>

    <table class="table mt-2">
        <thead>
            <tr>
                <th scope="col">Item ID</th>
                <th scope="col">Item Name</th>
                <th scope="col">Description</th>
                <th scope="col">Category</th>
                <th scope="col">Stocks</th>
                <th scope="col">Last Stocked</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <th scope="row">{{ $item->id }}</th>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->item_description }}</td>
                <td>{{ $item->category}}</td>
                <td>5.</td>
                <td>01-25-2023.</td>
                <td>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addStocks">Add Stocks</button>
                    @include('admin.modals.add-stocks')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editItem">Edit</button>
                    @include('admin.modals.edit-item')
                    <button type="button" class="btn btn-danger">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>