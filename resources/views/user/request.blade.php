@extends('layouts.app')
@include('layouts.header')
@section('content')
<div class="container-fluid ">
    <div class="row min-vh-100">
        <div class="col-md-3 col-lg-2 sidebar p-0 bg-dark ">
            @include('layouts.sidebar')
        </div>
        <div class="col-md-9 col-lg-10 p-0">
            <div id="content" class="px-2 py-3">
                <div class="container-lg px-0 mt-2">
                    <h4><strong>REQUEST ITEM</strong></h4>
                </div>
                <div class="container-lg mt-3 mb-3 p-0">

                    <div class="container-lg p-0 d-flex" style="flex-direction:column">
                        <div class="container-lg " id="add-item-div">
                            <h5 style="letter-spacing: 3px;">ITEM NAME: </h5>
                            <div class="container-lg p-0 m-0 d-flex align-items-center" style="width:100%;max-width:500px">
                                <select id="nameSearch" class="text-capitalize m-1" name="nameSearch" style="width: 280px;" required>
                                    <option></option>
                                    @foreach($items as $item)
                                    <option data-item-id="{{ $item->id }}" class="text-capitalize" data-item-name="{{ $item->name }}" data-item-category="{{ $item->category }}" data-item-unit="{{ $item->unit }}" data-stock-id="{{ $item->item_stock_id }}" data-mode-acq="{{ $item->mode_acquisition }}" data-stock-exp="{{ $item->formatted_exp_date }}" data-stock-qty="{{ $item->stock_qty }}">{{ $item->name }} - {{ $item->category }} {{ $item->unit === "-" ? "" : "- " . $item->unit }} ({{ $item->formatted_exp_date }}) ({{ $item->mode_acquisition }}) - {{ $item->stock_qty }}</option>
                                    @endforeach
                                </select>

                                <input type="number" id="quantity" class="form-control m-1" name="quantity" min="1" max="" style="width:100%;max-width:120px;" placeholder="Quantity" required>
                                <input type="hidden" id="stock_id" name="stock_id" style="min-width:120px;">
                                <input type="hidden" id="exp_date" name="exp_date" style="min-width:120px;">
                                <button type="button" id="add-item-btn" class="btn btn-secondary py-1 px-2">Add</button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="container-lg p-0 border border-dark shadow-lg p-3 mt-3 mb-2 bg-body rounded" style="min-height: 360px;">
                    <h4>Requested Items</h4>
                    <div class="container-md p-0 border rounded shadow">
                        <div class=" container-lg p-0" style="height:230px;overflow-y: auto;">
                            <table class="table" id="requested-item-table">
                                <thead class="text-white bg-secondary" style="position: sticky;top: 0;z-index: 0;">
                                    <tr>
                                        <th scope="col">Item ID</th>
                                        <th scope="col">Item Name</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Unit</th>
                                        <th scope="col">Stock ID</th>
                                        <th scope="col">Mode Of ACQ</th>
                                        <th scope="col">Expiration Date</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col" id="action-header">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="requested-item-table-body">

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="container-lg p-0 d-flex my-2">
                        <div class="container-lg d-flex" style="flex-wrap:wrap">
                            <div class="container-lg mb-3 p-0">
                                <div class="container-lg m-0">
                                    <label for="patient_name">Patient Name:</label>
                                    <input type="text" name="patient_name" id="patient_name" class="form-control border border-secondary" style="width: 100%;max-width:320px">
                                </div>

                                <div class="container-sm m-0 mt-2 p-0">
                                    <div class="container-lg m-0 d-flex" style="align-items: center;">
                                        <label for="p_age" class="mx-1">Age:</label>
                                        <input type="text" name="p_age" id="p_age" class="form-control border border-secondary" style="width: 100%;max-width:50px">
                                    </div>
                                    <div class="container-lg m-0 mt-1 d-flex" style="align-items: center;flex-wrap:wrap">
                                        <label for="" class="m-1">Sex:</label>
                                        <div class="d-flex mx-1" style="align-items: center;">
                                            <label for="p_sex_m">Male</label>
                                            <input type="radio" name="p_sex" class="" id="p_sex_m" value="Male">
                                        </div>
                                        <div class="d-flex mx-1" style="align-items: center;">
                                            <label for="p_sex_f">Female</label>
                                            <input type="radio" name="p_sex" id="p_sex_f" value="Female">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="container-lg m-0">
                                <label for="doctor_name">Physician/Nurse Name:</label>
                                <input type="text" name="doctor_name" id="doctor_name" class="form-control border border-secondary" style="width: 100%;max-width:320px">
                            </div>
                            <div class="container-lg m-0">
                                <label for="request_by">Requester Name:</label>
                                <input type="text" name="request_by" id="request_by" class="form-control border border-secondary" style="width: 100%;max-width:320px">
                            </div>
                        </div>

                        <form action="" method="post" class="m-0 d-flex" style="flex-direction: column-reverse;">
                            @csrf
                            <button type="button" id="submit-request-btn" class="btn btn-primary" style="width: 200px;">Submit request</button>
                            <strong><span id="request_id_span"></span></strong>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        window.APP_URL = "{{ url('') }}";


        $('#nameSearch').select2({
            placeholder: 'select item',
            allowClear: true,
        });

        //get the table body
        const tableBody = $("#requested-item-table-body");

        //If no data exist 'No items' will appear in table
        if (tableBody.children().length === 0) {
            const row = $("<tr>").appendTo(tableBody);
            const cell = $("<td colspan='6'>").text("No items").appendTo(row);
        }

        $('#nameSearch').on('change', function() {
            var selectedOptionValue = $('#nameSearch option:selected');
            var itemId = selectedOptionValue.data('item-id');
            var stockId = selectedOptionValue.data('stock-id');
            var modeOfAcq = selectedOptionValue.data('mode-acq');
            var stockExpDate = selectedOptionValue.data('stock-exp');
            var quantity = selectedOptionValue.data('stock-qty');
            $('#stock_id').val(stockId);
            $('#exp_date').val(stockExpDate);
            $('#quantity').attr('max', quantity);
        });

        //Initiate selected item array
        var selectedItem = [];

        $('#add-item-btn').on('click', function() {

            var selectedOptionValue = $('#nameSearch option:selected');
            var itemId = selectedOptionValue.data('item-id');
            var itemName = selectedOptionValue.data('item-name');
            var itemCategory = selectedOptionValue.data('item-category');
            var itemUnit = selectedOptionValue.data('item-unit');
            var stockId = selectedOptionValue.data('stock-id');
            var modeOfAcq = selectedOptionValue.data('mode-acq');
            var stockExpDate = selectedOptionValue.data('stock-exp');
            var stockQty = selectedOptionValue.data('stock-qty');
            var quantity = $('#quantity').val();

            //check the inputs if not empty
            if (selectedOptionValue !== '' && itemId !== '' && itemName !== '' && stockId !== '' && modeOfAcq !== '' && stockExpDate !== '' && quantity !== '') {
                $('#stock_id').val(stockId);
                $('#exp_date').val(stockExpDate);

                //check if the stock id is added in array
                for (var i = 0; i < selectedItem.length; i++) {
                    if (selectedItem[i].stock_id === stockId) {
                        alert(itemName + ' (' + stockExpDate + ')' + ' is already selected!');
                        var stock = true;
                        break; // Exit the loop if a match is found
                    }
                }

                //if the stock is not selected, this will push the item to array
                if (!stock == true) {

                    //if requested quantity is greater than the stock quatity
                    if (quantity > stockQty) {
                        alert('Quantity must be less than or equal to ' + stockQty + '.');
                        var quantityExceed = true;
                    }

                    //if requested quantity is less than stock quantity
                    if (!quantityExceed == true) {
                        selectedItem.push({
                            item_id: itemId,
                            item_name: itemName,
                            stock_id: stockId,
                            mode_acquisition: modeOfAcq,
                            quantity: quantity,
                            exp_date: stockExpDate,
                        });
                        var row = '<tr><td class="align-items-center">' + itemId + '</td><td>' + itemName + '</td><td>' + itemCategory + '</td><td>' + itemUnit + '</td><td>' + stockId + '</td><td>' + modeOfAcq + '</td><td>' + stockExpDate + '</td><td>' + quantity + '</td><td><button id="remove-item-btn" class="btn btn-danger" data-stock-id="' + stockId + '">✘</button></td></tr>';

                        //this will remove the 'No items' if data is added
                        $("#requested-item-table-body td:contains('No items')").parent().remove();
                        //this will add a requested item in a row on table body
                        $('#requested-item-table tbody').append(row);


                        //this will empty the inputs you clicked the add button
                        $("#nameSearch").val(null).trigger("change");
                        $('#stock_id').val('');
                        $('#exp_date').val('');
                        $('#quantity').val('');
                    }
                }

                // console.log(selectedItem);
            } else {
                alert('Please fill all inputs to add the item!');
            }
        });


        //remove button function
        $('#requested-item-table').on('click', '#remove-item-btn', function() {
            var stockId = $(this).data('stock-id');

            $(this).closest('tr').remove();

            // remove the item from selectedItem array
            selectedItem = selectedItem.filter(function(item) {
                return item.stock_id !== stockId;
            });

            // console.log(selectedItem);
        });

        $('#submit-request-btn').on('click', function() {

            if (selectedItem.length !== 0) {
                var requestByInput = $("#request_by");
                var patientNameInput = $("#patient_name");
                var patientAgeInput = $("#p_age");
                var doctorNameInput = $("#doctor_name");

                var requestBy = requestByInput.val();
                var patientName = patientNameInput.val();
                var patientAge = patientAgeInput.val();

                // Get the radio button elements
                var maleRadio = document.getElementById("p_sex_m");
                var femaleRadio = document.getElementById("p_sex_f");

                // Check if either radio button is selected
                if (maleRadio.checked) {
                    // Male radio button is selected
                    var sexValue = maleRadio.value;
                } else if (femaleRadio.checked) {
                    // Female radio button is selected
                    var sexValue = femaleRadio.value;
                } else {
                    // Neither radio button is selected
                    var sexValue = null;
                }

                var patientGender = sexValue;

                var doctorName = doctorNameInput.val();

                var requestedItems = JSON.stringify(selectedItem);
                var $btn = $(this);


                if (requestBy !== "" && patientName !== "" && doctorName !== "") {
                    if ($btn.prop('disabled')) {
                        return false; // don't submit if button is already disabled
                    }
                    $btn.prop('disabled', true); // disable button to prevent double-clicking

                    $.ajax({
                        type: 'POST',
                        url: window.APP_URL + '/user/submit-request',
                        data: {
                            requestBy: requestBy,
                            patientName: patientName,
                            patientAge: patientAge,
                            patientGender: patientGender,
                            doctorName: doctorName,
                            requestedItems: requestedItems,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            requestByInput.prop('disabled', true);
                            patientNameInput.prop('disabled', true);
                            patientAgeInput.prop('disabled', true);
                            doctorNameInput.prop('disabled', true);
                            $btn.text('Pending').css('background-color', 'green');
                            $('#add-item-btn').prop('disabled', true);
                            $('#nameSearch').prop('disabled', true);
                            $('#quantity').prop('disabled', true);
                            $('#action-header').remove();
                            $('#requested-item-table tbody tr').each(function() {
                                $(this).find('#remove-item-btn').parent('td').remove();
                                $(this).find('#item-row-qty').prop('readonly', true);
                            });
                            $('#request_id_span').text('Request ID: ' + response.request_id);
                            // console.log(response);
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                            $btn.prop('disabled', false); // enable button again on error
                        }
                    });
                } else if (requestBy === "") {
                    alert("Please enter requester name");
                } else if (patientName === "") {
                    alert("Please enter patient name");
                } else if (doctorName === "") {
                    alert("Please enter Doctor's name");
                }

            } else {
                alert('Please add an item to proceed.');
                $btn.prop('disabled', false); // enable button again on alert
            }
        });


        // function requesterNameAlert() {
        //     var requestNameInput = document.getElementById("#request_by");
        //     var requestName = requestNameInput.value;

        //     if (requestName !== "") {
        //         alert('test')
        //     }
        // }

        // requesterNameAlert();
    });
</script>
@endsection