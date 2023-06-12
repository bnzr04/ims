@extends('layouts.app')
<div class="container-lg pt-3 m-0" style="width: 100%;">
    <h2>New Requests</h2>
    <div class="container-lg p-0 m-0" style="overflow-y: auto;top: 0;height: 180px;">
        <table class="table m-0" style="font-size: 10px;">
            <thead class="bg-secondary text-white border" id="table_head" style="position:sticky;top:0;">
                <tr>
                    <th scope="col" class="border">RID</th>
                    <th scope="col" class="border">Date & Time</th>
                    <th scope="col" class="border">Office</th>
                    <th scope="col" class="border">Patient Name</th>
                    <th scope="col" class="border">Doctor Name</th>
                    <th scope="col" class="border">Request By</th>
                    <th scope="col" class="border">Status</th>
                </tr>
            </thead>
            <tbody id="request_table">

            </tbody>
        </table>
    </div>
</div>
<!-- Sound notificaition -->
<audio id="notificationSound">
    <source src="{{ asset('/sound/light-hearted-message-tone.mp3') }}" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>
<script>
    window.APP_URL = "{{ url('') }}";

    const requestTbody = document.querySelector('#request_table');
    const tableHead = document.querySelector('#table_head');

    function playNotificationSound() {
        var audio = document.getElementById('notificationSound');
        audio.play();
    }

    var previousCount = 0;

    function pendingUpdate() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "{{ route('manager.show-pending-requests') }}");
        xhr.responseType = "json";
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = xhr.response;
                // console.log(data.pending.length);

                var currentCount = data.pending.length;

                if (currentCount > previousCount) {
                    playNotificationSound();
                    // console.log(currentCount);
                }

                previousCount = currentCount;

                // Update the table with the new data
                requestTbody.innerHTML = "";

                if (data.pending.length > 0) {
                    for (let i = 0; i < data.pending.length; i++) {
                        var row = data.pending[i];
                        var url = window.APP_URL + '/manager/requested-items/' + row.id;
                        if (row.accepted_by_user_name === null) {
                            row.accepted_by_user_name = "-";
                        }
                        requestTbody.innerHTML += "<tr class='border'><td class='border'>" + row.id + "</td><td class='border'>" + row.formatted_date + "</td><td class='border'>" + row.office + "</td><td class='border'>" + row.patient_name + "</td><td class='border'>" + row.doctor_name + "</td><td class='border'>" + row.request_by + "</td><td class='border'>" + row.status + "</td></tr>";
                    }
                } else {
                    requestTbody.innerHTML += "<tr><td colspan='7'>No request...</td></tr>";
                }
            } else {
                console.log('Error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    pendingUpdate();
    setInterval(pendingUpdate, 30000);
</script>