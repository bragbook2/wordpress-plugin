<?php
/*
Template Name: Case Detail Page Template
*/
?>
<div class="bb-container-main">
    <main class="bb-main ">
        <?php
        include plugin_dir_path(__FILE__) . 'sidebar-template.php';
        ?>

        <div class="bb-content-area">
            <div class="bb-filter-attic bb-filter-attic-borderless">
                <button type="button" class="bb-sidebar-toggle">
                    <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/menu-icon.svg" style="padding:3px;"
                        alt="toggle sidebar">
                </button>
                <div class="bb-search-container-outer">
                    <form class="search-container mobile-search-container">
                        <input type="text" id="mobile-search-bar" placeholder="Search Procedures">
                        <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg"
                            class="bb-search-icon" alt="search">
                        <ul id="mobile-search-suggestions" class="search-suggestions"></ul>
                    </form>
                </div>
            </div>
            <div class="bb-patient-box">
                <div class="bb-patient-left">
                </div>
                <div class="bb-patient-right">

                </div>
            </div>
        </div>
        <div class="bb-fav-modal">
            <div class="bb-fav-modal-inner">
                <p class="bb-fav-modal-close-button">X</p>
                <img class="bb-thumbnail" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/myfavs-logo.svg"
                    alt="logo">
                <h3>You are Loving these!</h3>
                <p>To improve the communication between us, keep hearting cases that speak to you. During our
                    consultation, we'll review this collection together so we can discuss your specific goals and
                    concerns.</p>
                <form method="post">
                    <div class="bb-input-group">
                        <label for="name">Name</label>
                        <input class="bb-is-required" placeholder="Name" type="text" name="name">
                        <span class="bb-is-required-msg">Name is required</span>
                    </div>
                    <div class="bb-input-group">
                        <label for="email">Email Address</label>
                        <input class="bb-is-required" placeholder="Email" type="email" name="email">
                        <span class="bb-is-required-msg">Email is required</span>
                    </div>
                    <div class="bb-input-group">
                        <label for="number">Phone</label>
                        <input class="bb-is-required" placeholder="Phone" type="tel" name="phone">
                        <span class="bb-is-required-msg">Phone is required</span>
                    </div>
                    <input type="hidden" name="case-id">
                    <input type="hidden" name="api-token">
                    <input type="hidden" name="website-id">
                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>
    </main>
</div>
<?php
get_footer();
