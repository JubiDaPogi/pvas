<div class="pvas-page-wrap">
    <div class="pvas-page-heading">
        <h2>Get in Touch</h2>
        <p>Have a question about your pet's care or an upcoming visit? Send us a message and our team will get back to you.</p>
    </div>
    <div class="pvas-message-card">
        <div class="card rounded-0 card-outline card-navy shadow">
            <div class="card-body rounded-0">
                <h2 class="text-center">Message Us</h2>
                <center><hr class="bg-navy border-navy w-25 border-2"></center>
                <?php if($_settings->chk_flashdata('pop_msg')): ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check mr-2"></i> <?= $_settings->flashdata('pop_msg') ?>
                    </div>
                    <script>
                        $(function(){
                            $('html, body').animate({scrollTop:0})
                        })
                    </script>
                <?php endif; ?>
                <form action="" id="message-form" class="d-flex flex-column">
                    <input type="hidden" name="id">
                    <div class="pvas-form-row">
                        <div class="pvas-form-field">
                            <input type="text" class="form-control form-control-sm form-control-border" id="fullname" name="fullname" required placeholder="Juan Cruz">
                            <small class="px-3 text-muted">Full Name</small>
                        </div>
                        <div class="pvas-form-field">
                            <input type="text" class="form-control form-control-sm form-control-border" id="contact" name="contact" required placeholder="+00 000 000 0000">
                            <small class="px-3 text-muted">Contact Number</small>
                        </div>
                    </div>
                    <div class="pvas-form-row">
                        <div class="pvas-form-field">
                            <input type="email" class="form-control form-control-sm form-control-border" id="email" name="email" required placeholder="email@domain.com">
                            <small class="px-3 text-muted">Email</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <small class="text-muted">Message</small>
                        <textarea name="message" id="message" rows="4" class="form-control form-control-sm rounded-0" required placeholder="Write your message here"></textarea>
                    </div>
                    <div class="form-group text-center mb-0">
                        <button class="btn btn-primary rounded-pill px-5">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('#message-form').submit(function(e){
            e.preventDefault();
            var _this = $(this)
            $('.pop-msg').remove()
            var el = $('<div>')
                el.addClass("pop-msg alert")
                el.hide()
            start_loader();
            $.ajax({
                url:_base_url_+"classes/Master.php?f=save_message",
				data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
				error:err=>{
					console.log(err)
					alert_toast("An error occured",'error');
					end_loader();
				},
                success:function(resp){
                    if(resp.status == 'success'){
                        location.reload();
                    }else if(!!resp.msg){
                        el.addClass("alert-danger")
                        el.text(resp.msg)
                        _this.prepend(el)
                    }else{
                        el.addClass("alert-danger")
                        el.text("An error occurred due to unknown reason.")
                        _this.prepend(el)
                    }
                    el.show('slow')
                    $('html, body').animate({scrollTop:0},'fast')
                    end_loader();
                }
            })
        })
    })
</script>
