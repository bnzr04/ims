<h2>ITEMS</h2>
<div class="mt-3 d-flex justify-content-between">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#new_item">New Item</button>
    @include('admin.modals.new-item')

    <div class="input-group flex-nowrap" style="width: 270px;">
        <span class="input-group-text" id="addon-wrapping">Search</span>
        <input type="text" class="form-control bg-white" placeholder="" aria-label="search" aria-describedby="addon-wrapping">
    </div>

</div>

<table class="table mt-2 overflow-x-auto">
    <thead>
        <tr>
            <th scope="col">Item ID</th>
            <th scope="col">Item Name</th>
            <th scope="col">Description</th>
            <th scope="col">Category</th>
            <th scope="col">Cost</th>
            <th scope="col">S. Cost</th>
            <th scope="col">Useful Life</th>
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
            <td>{{ $item->item_cost }}</td>
            <td>{{ $item->item_salvage_cost }}</td>
            <td>{{ $item->item_useful_life }}</td>
            <td>
                <button type="button" class="btn btn-success" id="edit_item_btn" data-bs-toggle="modal" data-bs-target="#edit_item_modal" data-id="{{ $item->id }}">Edit</button>
                @include('admin.modals.edit-item')
                <button type="button" class="btn btn-danger delete_btn" id="delete_item_btn" data-id="{{ $item->id }}">Delete</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<script type="text/javascript">
    $(document).ready(function() {

        deleteItem();

        function deleteItem() { //This function will delete an item
            $(".delete_btn").click(function() {
                if (confirm("Are you sure you want to delete this item?") == true) {
                    var id = $(this).data('id');
                    $.ajax({
                        type: "GET",
                        url: "/admin/delete-item/" + id,
                        success: function(response) {
                            alert("Item deleted");
                        },
                        error: function(error) {
                            console.log(error);
                            alert("Item not deleted");
                        }
                    });

                }
            });
        }



    });
</script>