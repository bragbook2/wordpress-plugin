<?php
/*
Template Name: Case List Page Template
*/
?>
<div class="bb-container-main bb-container-main-v2">
    <main class="bb-main bb-main-v2">
        <?php include plugin_dir_path(__FILE__) . 'sidebar-template.php'; ?>
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
                    <h1 id="procedure-title"><span></span></h1>
                </div>
                <div class="actions-box" style="display:none;">
                    <div class="action-box actions-filter ">
                        <div class="action-box-toggle toggle-on-click toggle-actions-filter-box">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="2 2 20 20">
                                <path
                                    d="M3 4.6C3 4.03995 3 3.75992 3.10899 3.54601C3.20487 3.35785 3.35785 3.20487 3.54601 3.10899C3.75992 3 4.03995 3 4.6 3H19.4C19.9601 3 20.2401 3 20.454 3.10899C20.6422 3.20487 20.7951 3.35785 20.891 3.54601C21 3.75992 21 4.03995 21 4.6V6.33726C21 6.58185 21 6.70414 20.9724 6.81923C20.9479 6.92127 20.9075 7.01881 20.8526 7.10828C20.7908 7.2092 20.7043 7.29568 20.5314 7.46863L14.4686 13.5314C14.2957 13.7043 14.2092 13.7908 14.1474 13.8917C14.0925 13.9812 14.0521 14.0787 14.0276 14.1808C14 14.2959 14 14.4182 14 14.6627V17L10 21V14.6627C10 14.4182 10 14.2959 9.97237 14.1808C9.94787 14.0787 9.90747 13.9812 9.85264 13.8917C9.7908 13.7908 9.70432 13.7043 9.53137 13.5314L3.46863 7.46863C3.29568 7.29568 3.2092 7.2092 3.14736 7.10828C3.09253 7.01881 3.05213 6.92127 3.02763 6.81923C3 6.70414 3 6.58185 3 6.33726V4.6Z"
                                    stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                </path>
                            </svg>
                            <span>Filter</span>
                        </div>
                        <div class="actions-filter-box bb-filter-content bb-filter-content-mobile">
                            <div>
                                <div class="bb-filter-content-attic">
                                    <div class="attic-col-left">
                                        <button
                                            class="toggle-on-click toggle-actions-filter-box toggle-action-box-toggle"><img
                                                src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/cross-icon-new.svg"
                                                alt="icon"></button>
                                    </div>
                                    <div class="attic-col-right">
                                        <span class="clear-attic" id="clearButton">Clear All</span>
                                    </div>
                                </div>
                                <div class="bb-filter-content-inner">



                                </div>
                            </div>
                            <div class="actions-filter-box-inner actions-filter-box2">
                                <div class="bb-filter-content-attic">
                                    <div class="attic-col-left">
                                        <button class="toggle-on-click-multiple-close toggle-actions-filter-box2"><img
                                                src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/arrow-left.svg"
                                                alt="icon"></button>
                                        <span>Filter by:</span>
                                    </div>
                                    <div class="attic-col-right">
                                        <span class="clear-attic" id="advanceClearButton">Clear All</span>
                                    </div>
                                </div>

                                <form class="search-container">
                                    <input type="text" id="search-bar">
                                    <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg"
                                        class="bb-search-icon" alt="search">
                                    <ul id="search-suggestions" class="search-suggestions"
                                        style="display: none !important;">
                                    </ul>
                                </form>

                                <div class="bb-filter-checkbox-list" id="filter-options-container">
                                    <!-- filter list will be show here using JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bbrag-gallery">

                </div>

                <div class="ajax-load-more ajax-load-more-v2" id="load-container" style="display: none;">
                    <button class="bb_ajax-load-more-btn" data-offset="2">VIEW MORE RESULTS</button>
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
