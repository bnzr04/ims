<!-- ADD NEW USER MODAL -->
<div class="modal fade" id="new-user-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add new user</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="user_form">
                <div class="modal-body">
                    <div class="container-sm mb-2">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="password">Password</label>
                        <input type="text" class="form-control" name="password" id="password" required>
                    </div>

                    <div class="container-sm mb-2">
                        <label for="usertype">User type</label>
                        <select name="usertype" class="form-control" id="usertype">
                            <option value="">Select</option>
                            <option value="0">User</option>
                            <option value="1">Manager</option>
                            <option value="2">Admin</option>
                        </select>
                    </div>

                    <div class="container-sm mb-2" id="department_div">
                        <label for="user_dept">Department</label>
                        <select name="user_dept" class="form-control" id="user_dept">
                            <option value="">Select</option>
                            <option value="0">Department 1</option>
                            <option value="1">Department 2</option>
                            <option value="2">Department 3</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add user</button>
                </div>
            </form>
        </div>
    </div>
</div>