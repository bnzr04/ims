<!-- ADD NEW USER MODAL -->
<div class="modal fade" id="new-user-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add new user</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="clearForm()"></button>
            </div>
            <form action="{{ route('admin.save-user') }}" method="POST" id="user_form">
                @csrf
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
                        <input type="password" class="form-control" name="password" id="password" required>
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

                    <div class="container-sm mb-2" id="department_div" style="display: none;">
                        <label for="userdept">Department</label>
                        <select name="userdept" class="form-control" id="userdept">
                            <option value="0">Pharmacy</option>
                            <option value="1">Csr</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="clearForm()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add user</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    deptShow();

    function deptShow() {
        const userTypeSelect = document.getElementById("usertype");
        const departmentDiv = document.getElementById("department_div");

        userTypeSelect.addEventListener("change", function() {
            if (userTypeSelect.value === "0") {
                departmentDiv.style.display = "block";
            } else {
                departmentDiv.style.display = "none";
            }
        });
    }

    function clearForm() {
        document.getElementById("user_form").reset();
    }
</script>