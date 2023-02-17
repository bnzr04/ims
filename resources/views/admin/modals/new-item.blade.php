<!-- Add New Item Modal-->
<div class="modal fade" id="new_item" data-bs-backdrop="true" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Add new item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.saveItem') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="container-sm mb-2">
                        <label for="name">Item name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="category">Item category</label>
                        <input type="text" class="form-control" name="category" id="category" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="description">Item description</label>
                        <textarea class="form-control" name="description" id="description" required></textarea>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="cost">Cost</label>
                        <input type="text" class="form-control" name="cost" id="cost" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="salvage_cost">Salvage cost</label>
                        <input type="text" class="form-control" name="salvage_cost" id="salvage_cost" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="useful_life">Useful life</label>
                        <input type="number" min='0' class="form-control" name="useful_life" id="useful_life" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="unique">Add Item</button>
                </div>
            </form>
        </div>

    </div>

</div>