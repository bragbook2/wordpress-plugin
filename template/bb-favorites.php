<?php
/*
Template Name: favorites Page Template
*/

get_header();

?>
<div class="bb-container-main">
    <main class="bb-main ">
        <?php
        include plugin_dir_path(__FILE__) . 'sidebar-template.php'; 
        ?>
        <div class="bb-content-area">
            <div class="bb-filter-attic">
                <button type="button" class="bb-sidebar-toggle">
                    <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/caret-right-sm.svg" alt="toggle sidebar">
                </button>
                <h2><span>My Favorites</span></h2>
            </div>
            <div class="bb-content-boxes-sm">
                <div class="bb-content-boxes" id="bb-content-boxes-ajax">
                         
                </div> 
            </div>
        </div>
    </main>
</div>

<?php
get_footer();

