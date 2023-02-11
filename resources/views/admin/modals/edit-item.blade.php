<!--  this Modal will show when you clicked edit button on item-->
<div class="modal fade" id="editItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Edit item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="">
                    @csrf
                    <div class="container-sm mb-1">
                        <label for="item-id">Item ID</label>
                        <input type="text" class="form-control" name="item-id" id="item-id" disabled value="{{ $item->id }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="item-name">Item name</label>
                        <input type="text" class="form-control" name="itemName" id="item-name" required value="{{ $item->item_name }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="category">Item category</label>
                        <input type="text" class="form-control" name="category" id="category" required value="{{ $item->category }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="item-description">Item description</label>
                        <input type="text" class="form-control" name="itemDescription" id="item-description" required value="{{ $item->item_description }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="item-cost">Cost</label>
                        <input type="text" class="form-control" name="cost" id="item-cost" required value="{{ $item->item_cost }}">
                    </div>

                    <div class="container-sm mb-1">
                        <label for="salvage-cost">Salvage cost</label>
                        <input type="text" class="form-control" name="salvageCost" id="salvage-cost" required value="{{ $item->item_salvage_cost }}">
                    </div>

                    <div class="container-sm">
                        <label for="useful-life">Useful life</label>
                        <input type="number" min='0' class="form-control" name="usefulLife" id="useful-life" required value="{{ $item->item_useful_life }}">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Edit</button>
                </form>
            </div>
        </div>
    </div>
</div>