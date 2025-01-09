<?php
/*
Template Name: Consultation Page Template
*/
?>
<div class="bb-container-main">
<main class="bb-main">
    <?php include plugin_dir_path(__FILE__) . 'sidebar-template.php'; ?> 

    <div class="bb-content-area">
        <div class="bb-filter-attic">
            <button type="button" class="bb-sidebar-toggle">
                <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/caret-right-sm.svg" alt="toggle sidebar">
            </button>
            <h2><span>Consultation Request</span></h2>
        </div>
        <form class="bb-form bb-consultation-form" method="post" action="">
            <input class="bb-is-required" name="name" type="text" placeholder="Name*">
            <span class="bb-is-required-msg">Name field is required</span>
            <input class="bb-is-required" name="email" type="email" placeholder="Email*">
            <span class="bb-is-required-msg">Email field is required</span>
            <input class="bb-is-required" name="phone" type="number" placeholder="Phone*">
            <span class="bb-is-required-msg">Number field is required</span>
            <textarea rows="6" name="description" placeholder="How can we help?"></textarea>
            <button type="submit" name="submit">Submit</button>
        </form>
        <span class="bb-is-required-success"></span>
    </div>
    <div class="bb-bottom-bar">
        <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/fav-logo.svg" alt="logo">
        <p><span>Use the MyFavorites tool</span> to help communicate your specific goals. If a result speaks to you, tap the heart.</p>
    </div>
</main>
</div>
<?php