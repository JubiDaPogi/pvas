<style>
    .fc-event-title-container{
        text-align:center;
    }
    .fc-event-title.fc-sticky{
        font-size:2em;
    }
</style>

<?php
$appointments = $conn->query("
    SELECT * FROM `appointment_list`
    WHERE `status` IN (0,1)
    AND DATE(schedule) >= '".date("Y-m-d")."'
");

$appoinment_arr = [];

while($row = $appointments->fetch_assoc()){
    if(!isset($appoinment_arr[$row['schedule']]))
        $appoinment_arr[$row['schedule']] = 0;

    $appoinment_arr[$row['schedule']] += 1;
}

$appointment_found = false;

if(isset($_POST['view_details'])){

    $appointment_code = trim($_POST['appointment_code']);
    $email = trim($_POST['email']);

    $qry = $conn->query("
        SELECT a.*, c.name AS pet_type
        FROM appointment_list a
        INNER JOIN category_list c ON a.category_id = c.id
        WHERE a.code = '{$appointment_code}'
        AND a.email = '{$email}'
    ");

    if($qry->num_rows > 0){

        $appointment_found = true;
        $res = $qry->fetch_assoc();

        foreach($res as $k => $v){
            $$k = $v;
        }

        $service = "";
        $services = $conn->query("
            SELECT * FROM service_list
            WHERE id IN ({$service_ids})
            ORDER BY name ASC
        ");

        while($row = $services->fetch_assoc()){
            if(!empty($service)) $service .= ", ";
            $service .= $row['name'];
        }

        $service = empty($service) ? "N/A" : $service;

    } else {
        echo "<script>alert('Appointment not found. Please check your details.');</script>";
    }
}
?>

<div class="pvas-page-wrap">
    <div class="pvas-page-heading">
        <h2>Schedule an Appointment</h2>
        <p>Pick an open date on the calendar below to book a visit, or look up an existing booking.</p>
    </div>
    <div class="pvas-appointment-layout">

        <div id="calendarContainer">
            <div class="card card-outline card-primary rounded-0 shadow">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Schedule an Appointment</h4>

                        <button type="button" class="btn btn-primary" id="viewAppointmentBtn">
                            View Appointment
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div id="appointmentCalendar"></div>
                </div>
            </div>
        </div>

        <div id="appointmentContainer" style="display:none;">
            <div class="card card-outline card-primary rounded-0 shadow">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">View Appointment</h4>

                        <button type="button" class="btn btn-primary" id="backBtn">
                            Schedule an Appointment
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST">

                        <div class="form-group mb-3">
                            <label>Appointment Code</label>
                            <input type="text" class="form-control" name="appointment_code" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Email Address</label>
                            <input type="email" class="form-control" name="email" placeholder="EMAIL@DOMAIN.COM" required>
                        </div>

                        <button type="submit" name="view_details" class="btn btn-primary">
                            VIEW DETAILS
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <div id="resultContainer" style="display:none;">
            <div class="card card-outline card-primary rounded-0 shadow">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Appointment Details</h4>

                        <button type="button" class="btn btn-secondary" id="closeResultBtn">
                            Close
                        </button>
                    </div>
                </div>

                <div class="card-body">

                    <?php if($appointment_found): ?>

                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Appointment Code</th>
                            <td><?= $code ?></td>
                        </tr>
                    </table>

                    <br>

                    <div class="row">

                        <div class="col-md-6">
                            <fieldset>
                                <legend>Owner Information</legend>
                                <table class="table table-bordered">
                                    <tr><th>Name</th><td><?= ucwords($owner_name) ?></td></tr>
                                    <tr><th>Contact</th><td><?= $contact ?></td></tr>
                                    <tr><th>Email</th><td><?= $email ?></td></tr>
                                    <tr><th>Address</th><td><?= $address ?></td></tr>
                                </table>
                            </fieldset>
                        </div>

                        <div class="col-md-6">
                            <fieldset>
                                <legend>Pet Information</legend>
                                <table class="table table-bordered">
                                    <tr><th>Pet Type</th><td><?= $pet_type ?></td></tr>
                                    <tr><th>Breed</th><td><?= $breed ?></td></tr>
                                    <tr><th>Age</th><td><?= $age ?></td></tr>
                                    <tr><th>Services</th><td><?= $service ?></td></tr>
                                </table>
                            </fieldset>
                        </div>

                    </div>

                    <div class="mt-3">
                        <strong>Status:</strong>
                        <?php
                        switch($status){
                            case 0: echo '<span class="badge badge-primary">Pending</span>'; break;
                            case 1: echo '<span class="badge badge-success">Confirmed</span>'; break;
                            case 2: echo '<span class="badge badge-danger">Cancelled</span>'; break;
                        }
                        ?>
                    </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
var calendar;
var appointment = $.parseJSON('<?= json_encode($appoinment_arr) ?>') || {};

$(function(){

    function showCalendar(){
        $('#appointmentContainer').hide();
        $('#resultContainer').hide();
        $('#calendarContainer').show();

        setTimeout(function(){
            if(calendar){
                calendar.updateSize();
            }
        }, 100);
    }

    function showSearch(){
        $('#calendarContainer').hide();
        $('#resultContainer').hide();
        $('#appointmentContainer').show();
    }

    $('#viewAppointmentBtn').click(function(){
        showSearch();
    });

    $('#backBtn').click(function(){
        showCalendar();
    });

    $('#closeResultBtn').click(function(){
        showSearch();
    });

    <?php if($appointment_found): ?>
        $('#calendarContainer').hide();
        $('#appointmentContainer').hide();
        $('#resultContainer').show();
    <?php endif; ?>

    var date = new Date();

    calendar = new FullCalendar.Calendar(
        document.getElementById('appointmentCalendar'),
        {
            headerToolbar:{
                left:false,
                center:'title'
            },
            selectable:true,
            themeSystem:'bootstrap',

            events:[{
                daysOfWeek:[0,1,2,3,4,5,6],
                title:'<?= $_settings->info('max_appointment') ?>',
                allDay:true
            }],

            eventClick:function(info){
                if($(info.el).find('.fc-event-title.fc-sticky').text() > 0){
                    uni_modal(
                        "Set an Appointment",
                        "add_appointment.php?schedule=" + info.event.startStr,
                        "mid-large"
                    );
                }
            },

            validRange:{
                start: moment(date).format("YYYY-MM-DD")
            },

            eventDidMount:function(info){

                if(appointment[info.event.startStr]){
                    var available =
                        parseInt(info.event.title) -
                        parseInt(appointment[info.event.startStr]);

                    $(info.el)
                    .find('.fc-event-title.fc-sticky')
                    .text(available);
                }

                end_loader();
            },

            editable:true
        }
    );

    calendar.render();
});
</script>