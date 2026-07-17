<div class="pvas-page-wrap">
    <div class="pvas-page-heading">
        <h2>About Us</h2>
        <p>Getting to know <?php echo $_settings->info('name') ?>.</p>
    </div>
    <div class="pvas-message-card">
        <div class="card rounded-0 card-outline card-navy shadow">
            <div class="card-body rounded-0">
                <div>
                    <?= file_get_contents("about_us.html") ?>
                </div>
            </div>
        </div>
    </div>
</div>
