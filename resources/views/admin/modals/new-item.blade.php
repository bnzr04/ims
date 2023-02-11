<!--  this Modal will show when you add new item-->
<div class="modal fade" id="newItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Add new item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.saveItem') }}" method="POST" id="itemForm">
                @csrf
                <div class="modal-body">
                    <div class="container-sm mb-2">
                        <label for="item-name">Item name</label>
                        <input type="text" class="form-control" name="itemName" id="item-name" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="category">Item category</label>
                        <input type="text" class="form-control" name="category" id="category" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="item-description">Item description</label>
                        <input type="text" class="form-control" name="itemDescription" id="item-description" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="item-cost">Cost</label>
                        <input type="text" class="form-control" name="cost" id="item-cost" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="salvage-cost">Salvage cost</label>
                        <input type="text" class="form-control" name="salvageCost" id="salvage-cost" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="useful-life">Useful life</label>
                        <input type="number" min='0' class="form-control" name="usefulLife" id="useful-life" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>

    </div>

</div>
</div>
</div>