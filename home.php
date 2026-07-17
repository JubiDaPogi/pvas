<?php
$categories = $conn->query("SELECT * FROM `category_list` WHERE delete_flag = 0 ORDER BY name ASC");
$category_list = $categories->fetch_all(MYSQLI_ASSOC);
$cat_arr = array_column($category_list, 'name', 'id');

$services = $conn->query("SELECT * FROM `service_list` WHERE delete_flag = 0 ORDER BY name ASC");
$service_list = $services->fetch_all(MYSQLI_ASSOC);

function service_icon($name){
    $name = strtolower($name);
    if(strpos($name,'vaccin') !== false) return 'fa-syringe';
    if(strpos($name,'immuniz') !== false) return 'fa-shield-virus';
    if(strpos($name,'rabies') !== false) return 'fa-shield-virus';
    if(strpos($name,'check') !== false) return 'fa-stethoscope';
    return 'fa-paw';
}

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
        $appt_services = $conn->query("
            SELECT * FROM service_list
            WHERE id IN ({$service_ids})
            ORDER BY name ASC
        ");

        while($row = $appt_services->fetch_assoc()){
            if(!empty($service)) $service .= ", ";
            $service .= $row['name'];
        }

        $service = empty($service) ? "N/A" : $service;

    } else {
        echo "<script>alert('Appointment not found. Please check your details.');</script>";
    }
}
?>
<style>
    .fc-event-title-container{
        text-align:center;
    }
    .fc-event-title.fc-sticky{
        font-size:2em;
    }
</style>
<div class="pvas-page-wrap">

    <section id="about-section" class="pvas-anchor-section pvas-about-section">
        <div class="pvas-about-text">
            <h2>Welcome to <?php echo $_settings->info('name') ?></h2>
            <div class="welcome-content">
                <?php include("welcome.html") ?>
            </div>
        </div>
        <div class="pvas-about-image">
            <img src="<?php echo validate_image($_settings->info('cover')) ?>" alt="Veterinary care">
        </div>
    </section>

    <section id="services-section" class="pvas-anchor-section">
        <div class="pvas-page-heading">
            <h2>Our Services</h2>
            <p>Everything we offer for your pet, filter by type below.</p>
        </div>

        <div class="pvas-service-filters">
            <button type="button" class="pvas-filter-chip active" data-filter="all">All</button>
            <?php foreach($category_list as $cat): ?>
            <button type="button" class="pvas-filter-chip" data-filter="<?= $cat['id'] ?>"><?= ucwords($cat['name']) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="pvas-service-grid">
            <?php foreach($service_list as $row):
                $for = [];
                foreach(explode(',',$row['category_ids']) as $v){
                    if(isset($cat_arr[$v])) $for[] = $cat_arr[$v];
                }
            ?>
            <div class="pvas-service-card" data-categories="<?= $row['category_ids'] ?>">
                <div class="pvas-service-icon"><i class="fa <?= service_icon($row['name']) ?>"></i></div>
                <h4><?= ucwords($row['name']) ?></h4>
                <div class="pvas-service-tags">
                    <?php foreach($for as $f): ?>
                    <span class="pvas-service-tag"><?= $f ?></span>
                    <?php endforeach; ?>
                </div>
                <p class="pvas-service-desc truncate-3"><?= html_entity_decode(strip_tags($row['description'])) ?></p>
                <div class="pvas-service-footer">
                    <span class="pvas-service-fee"><i class="fa fa-tags mr-1"></i><?= number_format($row['fee'],2) ?></span>
                    <a href="#appointment-section" class="pvas-service-cta">Book Now <i class="fa fa-arrow-right ml-1"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($service_list)): ?>
            <p class="text-muted">No services available yet.</p>
            <?php endif; ?>
        </div>
    </section>

    <section id="appointment-section" class="pvas-anchor-section">
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
    </section>

    <section id="faq-section" class="pvas-anchor-section">
        <div class="pvas-page-heading">
            <h2>Frequently Asked Questions</h2>
            <p>Quick answers about booking and visiting the clinic.</p>
        </div>
        <div class="pvas-faq-list">
            <div class="pvas-faq-item">
                <button type="button" class="pvas-faq-question">
                    <span>How do I book an appointment?</span>
                    <i class="fa fa-plus"></i>
                </button>
                <div class="pvas-faq-answer">
                    <p>Go to the <a href="#appointment-section">Appointment</a> section above, pick an open date on the calendar, and fill in your pet's details. Once submitted, you'll get a unique appointment code for tracking your booking.</p>
                </div>
            </div>
            <div class="pvas-faq-item">
                <button type="button" class="pvas-faq-question">
                    <span>What is an appointment code for?</span>
                    <i class="fa fa-plus"></i>
                </button>
                <div class="pvas-faq-answer">
                    <p>It lets you look up your appointment's status anytime using the "View Appointment" option, no account needed. Use the same email address you booked with along with the code.</p>
                </div>
            </div>
            <div class="pvas-faq-item">
                <button type="button" class="pvas-faq-question">
                    <span>What pets can I bring in?</span>
                    <i class="fa fa-plus"></i>
                </button>
                <div class="pvas-faq-answer">
                    <p>We currently accept <?php echo implode(', ', array_map('ucwords', $cat_arr)) ?>. Check <a href="#services-section">Our Services</a> above for what we offer per pet type.</p>
                </div>
            </div>
            <div class="pvas-faq-item">
                <button type="button" class="pvas-faq-question">
                    <span>Is there a limit to daily appointments?</span>
                    <i class="fa fa-plus"></i>
                </button>
                <div class="pvas-faq-answer">
                    <p>Yes, we take up to <?php echo $_settings->info('max_appointment') ?> appointments per day so every pet gets proper attention. The calendar only shows open slots, dates that are full won't be clickable.</p>
                </div>
            </div>
            <div class="pvas-faq-item">
                <button type="button" class="pvas-faq-question">
                    <span>What are your clinic hours?</span>
                    <i class="fa fa-plus"></i>
                </button>
                <div class="pvas-faq-answer">
                    <p>We're open <?php echo $_settings->info('clinic_schedule') ?>. See the footer below for our full contact details and location.</p>
                </div>
            </div>
            <div class="pvas-faq-item">
                <button type="button" class="pvas-faq-question">
                    <span>Can I cancel or reschedule my appointment?</span>
                    <i class="fa fa-plus"></i>
                </button>
                <div class="pvas-faq-answer">
                    <p>Reach out through <a href="./?page=contact_us">Contact Us</a> or call us directly with your appointment code, and our staff will assist you with changes.</p>
                </div>
            </div>
        </div>
    </section>

</div>
<script>
    $(function(){
        $('.pvas-faq-question').click(function(){
            $(this).closest('.pvas-faq-item').toggleClass('active')
        })
        $('.pvas-filter-chip').click(function(){
            $('.pvas-filter-chip').removeClass('active')
            $(this).addClass('active')
            var filter = $(this).data('filter')
            $('.pvas-service-card').each(function(){
                if(filter == 'all'){
                    $(this).show()
                }else{
                    var cats = String($(this).data('categories')).split(',')
                    $(this).toggle(cats.indexOf(String(filter)) > -1)
                }
            })
        })
    })
</script>
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
        $('html, body').animate({scrollTop: $('#appointment-section').offset().top - 100}, 'fast');
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
