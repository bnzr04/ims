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
                    <div class="container-sm mb-2">
                        <label for="item-id">Item ID</label>
                        <input type="text" class="form-control" name="item-id" id="item-id" disabled>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="item-name">Item name</label>
                        <input type="text" class="form-control" name="item-name" id="item-name">
                    </div>

                    <div class="container-sm mb-2">
                        <label for="category">Item category</label>
                        <input id="category" type="text" class="form-control" name="category" list="categories">
                        <datalist id="categories">
                            <option value="Medical Supplies">
                            <option value="Equipment">
                        </datalist>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Edit</button>
            </div>
        </div>
    </div>
</div>