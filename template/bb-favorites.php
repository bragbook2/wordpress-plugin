<?php
/*
Template Name: Favorites Page Template
*/

get_header();

$bbrag_case_url = strtok($_SERVER["REQUEST_URI"], '?');
$bbragbook_case_url = trim($bbrag_case_url, '/');
$parts = explode('/', $bbragbook_case_url);
$page = get_page_by_path($parts[0]);
$page_id_via_slug = $page->ID;
if (count($parts) >= 3) {
    $bbrag_procedure_title = $parts[2];
    $bbrag_procedure_id = get_option($bbrag_procedure_title . '_id');
}
if (count($parts) >= 4) {
    $bbrag_procedure_title = $parts[2];
    $bbrag_procedure_id = get_option($bbrag_procedure_title . '_id');
    $bbrag_case_id = $parts[3];
}

?>
<div class="bb-container-main">
    <main class="bb-main ">
        <?php include plugin_dir_path(__FILE__) . 'sidebar-template.php';
        ?>
        <?php

        if (
            isset($bbrag_procedure_title) &&
            isset($bbrag_case_id) &&
            $bbrag_case_url == "/" . $parts[0] . "/favorites/" . $bbrag_procedure_title . "/" . $bbrag_case_id . "/" &&
            get_option($bbrag_case_id . "_bb_procedure_id_f_" . $page_id_via_slug) !== ''
        ) {
            // $bbrag_procedure_id = get_option($bbrag_case_id . "_bb_procedure_id_f_" . $page_id_via_slug);
            // $bbrag_case_id = get_option($bbrag_case_id);  
            // $bbrag_procedure_title = get_option($bbrag_procedure_id . '_title'); 
            // $bbrag_patient = 1; 
        
            // $category_to_match =  $bbrag_case_id;
            // $procedure_title = $bbrag_procedure_title;
            // $procedure_id = $bbrag_procedure_id;
            // $patient_id =  $bbrag_patient;
            ?>

            <div class="bb-content-area">
                <div class="bb-filter-attic bb-filter-attic-borderless">
                    <button type="button" class="bb-sidebar-toggle">
                        <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/caret-right-sm.svg" alt="toggle sidebar">
                    </button>
                    <div class="bb-search-container-outer">
                        <form class="search-container mobile-search-container">
                            <input type="text" id="mobile-search-bar">
                            <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg"
                                class="bb-search-icon" alt="search">
                            <ul id="mobile-search-suggestions" class="search-suggestions"></ul>
                        </form>
                    </div>
                </div>
                <div class="bb-patient-box">
                    <div class="bb-patient-left">
                        <div class="bb-patient-row">
                            <?php
                            $bb_seo_detail = '';
                            foreach ($matching_data as $procedure_data) {
                                $bb_seo_detail = isset($procedure_data['caseDetails'][0]) ? $procedure_data['caseDetails'][0] : [];
                            }
                            if (isset($bb_seo_detail['seoHeadline']) && !empty($bb_seo_detail['seoHeadline'])) {
                                echo "<h2>" . $bb_seo_detail['seoHeadline'] . "</h2>";
                            } else {
                                ?>
                                <h2><?= $procedure_title; ?> Patient <span><?= $patient_id ?></span></h2>
                                <?php
                            }
                            if (in_array($procedure_data['photoSets'][0]['caseId'], $favorite_caseIds)) {
                                ?>
                                <img class="bb-heart-icon bb-open-fav-modal"
                                    data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>"
                                    src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg" alt="heart">
                                <?php
                            } else {
                                ?>
                                <img class="bb-heart-icon bb-open-fav-modal"
                                    data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>"
                                    src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart.svg" alt="heart">
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                        $patient_detail = $height = $width = $race = $gender = $age = $bb_seo_detail = '';

                        foreach ($matching_data as $procedure_data) {
                            $patient_detail = !empty($procedure_data['details']) ? $procedure_data['details'] : '';
                            $height = !empty($procedure_data['height']) ? '<li>HEIGHT: ' . strtolower($procedure_data['height']) . '</li>' : '';
                            $width = !empty($procedure_data['weight']) ? '<li>WEIGHT: ' . strtolower($procedure_data['weight']) . '</li>' : '';
                            $race = !empty($procedure_data['ethnicity']) ? '<li>RACE: ' . strtolower($procedure_data['ethnicity']) . '</li>' : '';
                            $gender = !empty($procedure_data['gender']) ? '<li>GENDER: ' . strtolower($procedure_data['gender']) . '</li>' : '';
                            $age = !empty($procedure_data['age']) ? '<li>AGE: ' . strtolower($procedure_data['age']) . '</li>' : '';
                            $timeframe = !empty($procedure_data['after1Timeframe']) && !empty($procedure_data['after1Unit']) ? '<li>POST-OP PERIOD: ' . strtolower($procedure_data['after1Timeframe']) . ' ' . strtolower($procedure_data['after1Unit']) . '</li>' : '';
                            $timeframe2 = !empty($procedure_data['after2Timeframe']) && !empty($procedure_data['after2Unit']) ? '<li>2nd AFTER: ' . strtolower($procedure_data['after2Timeframe']) . ' ' . strtolower($procedure_data['after2Unit']) . '</li>' : '';
                            $revision_surgery = !empty($procedure_data['revisionSurgery']) ? '<li>This case is a revision of a previous procedure.</li>' : '';
                            $bb_seo_detail = isset($procedure_data['caseDetails'][0]) ? $procedure_data['caseDetails'][0] : [];

                            foreach ($procedure_data['photoSets'] as $key => $value) {
                                $bb_new_image_value = isset($value['highResPostProcessedImageLocation']) && !is_null($value['highResPostProcessedImageLocation'])
                                    ? $value['highResPostProcessedImageLocation']
                                    : (isset($value['postProcessedImageLocation']) && !is_null($value['postProcessedImageLocation'])
                                        ? $value['postProcessedImageLocation']
                                        : $value['originalBeforeLocation']);

                                ?>
                                <img class="bbrag_gallery_image" src="<?php echo $bb_new_image_value; ?>"
                                    alt="<?php echo isset($value['seoAltText']) ? $value['seoAltText'] : ''; ?>">
                                <?php
                            }
                        }
                        ?>
                    </div>

                    <div class="bb-patient-right">
                        <div class="bb-patient-row">
                            <?php
                            if (isset($bb_seo_detail['seoHeadline']) && !empty($bb_seo_detail['seoHeadline'])) {
                                echo "<h2>" . $bb_seo_detail['seoHeadline'] . "</h2>";
                            } else {
                                ?>
                                <h2><?= $procedure_title; ?> Patient <span><?= $patient_id ?></span></h2>
                                <?php
                            }
                            if (isset($bb_seo_detail['seoPageTitle']) && !empty($bb_seo_detail['seoPageTitle'])) {
                                $bb_seo_page_title = esc_js($bb_seo_detail['seoPageTitle']);
                                ?>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function () {
                                        var bb_seo_title = "<?php echo $bb_seo_page_title; ?>";
                                        document.querySelector('head title').textContent = bb_seo_title;
                                    });
                                </script>
                                <?php
                            }
                            if (!empty($procedure_data) && is_array($procedure_data)) {
                                if (in_array($procedure_data['photoSets'][0]['caseId'], $favorite_caseIds)) {
                                    ?>
                                    <img class="bb-heart-icon bb-open-fav-modal"
                                        data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>"
                                        src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg" alt="heart">
                                    <?php
                                } else {
                                    ?>
                                    <img class="bb-heart-icon bb-open-fav-modal"
                                        data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>"
                                        src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart.svg" alt="heart">
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <ul class="bb-patient-features">
                            <?php echo $height ?>
                            <?php echo $width ?>
                            <?php echo $race ?>
                            <?php echo $gender ?>
                            <?php echo $age ?>
                            <?php echo $timeframe ?>
                            <?php echo $timeframe2 ?>
                            <?php echo $revision_surgery ?>
                        </ul>
                        <?= $patient_detail ?>
                        <a href="/<?php echo $parts[0]; ?>/consultation/" class="bb-sidebar-btn">REQUEST A CONSULTATION</a>
                        <script>
                            jQuery(document).ready(function ($) {
                                var page_id_via_slug = "<?php echo $page_id_via_slug; ?>";
                                var url = "<?php echo admin_url('admin-ajax.php'); ?>";
                                var page_url = "<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>";
                                $.ajax({
                                    type: 'POST',
                                    url: url,
                                    data: {
                                        action: 'bb_generate_pagination',
                                        page_id_via_slug: page_id_via_slug,
                                        page_url: page_url
                                    },
                                    success: function (response) {
                                        jQuery('.bb-patient-slides').html(response);
                                    },
                                    error: function (error) {
                                        console.log(error);
                                    }
                                });
                            });
                        </script>
                        <div class="bb-patient-slides"></div>
                    </div>
                </div>
            </div>
            <?php
        } else {
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
            <?php
        }
        ?>
    </main>
</div>

<?php
get_footer();

