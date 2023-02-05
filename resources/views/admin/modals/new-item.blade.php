<!--  this Modal will show when you add new item-->
<div class="modal fade" id="newItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Add new item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.saveItem') }}" method="post">
                    @csrf
                    <div class="container-sm mb-2">
                        <label for="item-name">Item name</label>
                        <input type="text" class="form-control" name="itemName" id="item-name" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="category">Item category</label>
                        <input id="category" type="text" class="form-control" name="category" list="categories" required>
                        <datalist id="categories">
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="item-description">Item description</label>
                        <input type="text" class="form-control" name="itemDescription" id="item-description" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="quantity">Quantity</label>
                        <input type="number" min="0" class="form-control" name="quantity" id="quantity" required>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Item</button>
                </form>
            </div>

        </div>
    </div>
</div>