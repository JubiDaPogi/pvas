<?php require_once('./config.php'); ?>
 <!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<style>
  #header{
    height:55vh;
    width:calc(100%);
    position:relative;
  }
  #header:before{
    content:"";
    position:absolute;
    height:calc(100%);
    width:calc(100%);
    background-image:url(<?= validate_image($_settings->info("cover")) ?>);
    background-size:cover;
    background-repeat:no-repeat;
    background-position: center center;
    filter: brightness(.75);
    z-index:0;
  }
  #header:after{
    content:"";
    position:absolute;
    height:calc(100%);
    width:calc(100%);
    background:linear-gradient(180deg, rgba(15,23,32,.25) 0%, rgba(15,23,32,.45) 100%);
    z-index:1;
  }
  #header>div{
    position:absolute;
    height:calc(100%);
    width:calc(100%);
    z-index:2;
  }

  #top-Nav .navbar-nav {
    position: relative;
  }
  #top-Nav .nav-underline {
    position: absolute;
    bottom: 0;
    height: 2px;
    background-color: #f8f9fa;
    transition: left .3s ease, width .3s ease;
    pointer-events: none;
  }
  #top-Nav a.nav-link.active {
    color: #f8f9fa;
    font-weight: 900;
  }
</style>
<?php require_once('inc/header.php') ?>
  <body class="layout-top-nav layout-fixed layout-navbar-fixed"
      data-spy="scroll" data-target="#top-Nav" data-offset="70">
    <div class="wrapper">
     <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home';  ?>
     <?php require_once('inc/topBarNav.php') ?>
     <?php if($_settings->chk_flashdata('success')): ?>
      <script>
        alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
      </script>
      <?php endif;?>    
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper <?php echo ($page == "home" || $page == "about") ? '' : 'pt-5' ?>" style="">
        <?php if($page == "home" || $page == "about"): ?>
          <div id="header" class="shadow mb-4">
              <div class="d-flex justify-content-center h-100 w-100 align-items-center flex-column px-3">
                  <h1 class="w-100 text-center site-title px-5"><?php echo $_settings->info('name') ?></h1>
                  <p class="w-100 text-center site-subtitle px-5 mb-4">Your Pet's Health is our #1 Priority</p>
                  <a href="#appointment-section" class="btn btn-lg btn-primary rounded-pill px-4"><i class="fa fa-calendar-check mr-2"></i>Book an Appointment</a>
              </div>
          </div>
        <?php endif; ?>
        <!-- Main content -->
        <section class="content ">
          <div class="container">
            <?php 
              if(!file_exists($page.".php") && !is_dir($page)){
                  include '404.html';
              }else{
                if(is_dir($page))
                  include $page.'/index.php';
                else
                  include $page.'.php';

              }
            ?>
          </div>
        </section>
        <!-- /.content -->
  <div class="modal fade rounded-0" id="confirm_modal" role='dialog'>
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header rounded-0">
        <h5 class="modal-title">Confirmation</h5>
      </div>
      <div class="modal-body rounded-0">
        <div id="delete_content"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
      </div>
    </div>
  </div>
  <div class="modal fade rounded-0" id="uni_modal" role='dialog'>
    <div class="modal-dialog modal-md modal-dialog-centered rounded-0" role="document">
      <div class="modal-content rounded-0">
        <div class="modal-header rounded-0">
        <h5 class="modal-title"></h5>
      </div>
      <div class="modal-body rounded-0">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
      </div>
    </div>
  </div>
  <div class="modal fade rounded-0" id="uni_modal_right" role='dialog'>
    <div class="modal-dialog modal-full-height  modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header rounded-0">
        <h5 class="modal-title"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span class="fa fa-arrow-right"></span>
        </button>
      </div>
      <div class="modal-body rounded-0">
      </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="viewer_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
              <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
              <img src="" alt="">
      </div>
    </div>
  </div>
      </div>
      <!-- /.content-wrapper -->
      <?php require_once('inc/footer.php') ?>
      <script>
$(function () {
  var $navList = $('#top-Nav .navbar-nav');

  if ($navList.find('.nav-underline').length === 0) {
    $navList.append('<span class="nav-underline"></span>');
  }
  var $indicator = $navList.find('.nav-underline');

  function moveIndicatorTo($link) {
    if (!$link || !$link.length) return;
    $indicator.css({
      left: $link.position().left + 'px',
      width: $link.outerWidth() + 'px'
    });
  }

  function moveIndicator() {
    moveIndicatorTo($navList.find('.nav-link.active').first());
  }

  // set initial position once layout/fonts have settled
  setTimeout(moveIndicator, 150);

  // instant feedback on click, don't wait for scrollspy
  $navList.on('click', '.nav-link', function () {
    $navList.find('.nav-link').removeClass('active');
    $(this).addClass('active');
    moveIndicatorTo($(this));
  });

  // catch scrollspy's class swap while scrolling
  var observer = new MutationObserver(moveIndicator);
  $navList.find('.nav-link').each(function () {
    observer.observe(this, { attributes: true, attributeFilter: ['class'] });
  });

  $(window).on('resize', moveIndicator);
});
</script>
  </body>
</html>
