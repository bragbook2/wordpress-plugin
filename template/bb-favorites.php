<?php
/*
Template Name: favorites Page Template
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
     
        if (isset($bbrag_procedure_title) &&     
            isset($bbrag_case_id) && 
            $bbrag_case_url == "/".$parts[0]."/favorites/".$bbrag_procedure_title."/". $bbrag_case_id . "/" && 
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
                <?php 
                // $matching_data = []; 
                // $case_id_list = [];
                // if(!empty($properties_data) && is_array($properties_data)) {
                //     foreach ($properties_data as $token_key_bb => $token_bb) {
                //         foreach ($token_bb as $bb_website_id => $website_id_bb) { 
                //             foreach($website_id_bb as $api_item) {
                //                 foreach($api_item['api_data'] as $item){
                //                     if($category_to_match == '') {
                //                         $category_to_match = $item['photoSets'][0]['caseId'];
                //                     }
                //                     if ($item['photoSets'][0]['caseId'] == $category_to_match) {
                //                         $matching_data[] = $item;
                //                     }
                //                     if (in_array($procedure_id, $item['procedureIds'])) {
                //                         if(!empty($item['photoSets'])) {
                //                             $case_id_list[] = $item['photoSets'][0]['caseId'];
                //                         }
                //                     } 
                //                 }
                //             }
                //         }
                //     }
                // }

                // $case_page_title = isset($parts[2]) ? $parts[2] : '';
                // if(!empty($case_page_title)) {
                //     $case_page_title = str_replace('-', ' ', $case_page_title);
                //     $case_page_title = ucfirst($case_page_title);
                // }

                // $images = [];
                // $setup_wizard = '';
                // $procedure_description = '';
                // $default_and_seo_page_title = '';
                // foreach($matching_data as $procedure_data) {
                //     if(empty($procedure_description)) {
                //         $procedure_description = isset($procedure_data['caseDetails'][0]['seoPageDescription']) 
                //         && !empty($procedure_data['caseDetails'][0]['seoPageDescription']) ? $procedure_data['caseDetails'][0]['seoPageDescription'] : '';
                //     }
                //     if(empty($default_and_seo_page_title)) {
                //         $default_and_seo_page_title = isset($procedure_data['caseDetails'][0]['seoPageTitle']) 
                //         && !empty($procedure_data['caseDetails'][0]['seoPageTitle']) ? $procedure_data['caseDetails'][0]['seoPageTitle'] : '';
                //     }

                //     $bb_angle_count = 0;
                //     foreach ($procedure_data['photoSets'] as $key => $case) {
                //         $bb_angle_count++;
                //         $bb_new_image_case = isset($case['highResPostProcessedImageLocation']) && !is_null($case['highResPostProcessedImageLocation'])
                //             ? $case['highResPostProcessedImageLocation'] 
                //                 : (isset($case['postProcessedImageLocation']) && !is_null($case['postProcessedImageLocation']) 
                //                     ? $case['postProcessedImageLocation'] 
                //                     : $case['originalBeforeLocation']);

                //         $images[] = [
                //             '@type' => 'ImageObject',
                //             'url' => $bb_new_image_case,
                //             'caption' => empty($default_and_seo_page_title) ? "$case_page_title - angle $bb_angle_count" : "$default_and_seo_page_title - angle $bb_angle_count", 
                //             'representativeOfPage' => true,
                //             'thumbnailUrl' => $bb_new_image_case
                //         ];
                //     }
                // }

                // $bb_pro_cat_page = isset($parts[2]) ? home_url() . '/' . $parts[0] . '/favorites/' . $parts[2] . '/' : home_url();
                // $bb_gallery_page_title = get_the_title($page_id_via_slug);
                // $default_and_seo_page_title = empty($default_and_seo_page_title) ? $bb_gallery_page_title : $default_and_seo_page_title;
                // if(isset($parts[3])) {
                //     $bb_input = $parts[3];
                //     if (isset($bb_input) && preg_match('/^\d+$/', $bb_input)) {
                //         $bb_case_url_title = $case_page_title;
                //     }else {
                //         $bb_case_url_title = $bb_input;
                //     }
                // }
                ?>
                <div class="bb-filter-attic bb-filter-attic-borderless">
                    <button type="button" class="bb-sidebar-toggle">
                    <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/caret-right-sm.svg" alt="toggle sidebar">
                    </button>
                    <div class="bb-search-container-outer">
                        <form class="search-container mobile-search-container">
                            <input type="text" id="mobile-search-bar">
                            <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg" class="bb-search-icon" alt="search">
                            <ul id="mobile-search-suggestions" class="search-suggestions"></ul>
                        </form>
                    </div>
                </div>
                <div class="bb-patient-box">
                    <div class="bb-patient-left">
                        <div class="bb-patient-row">
                            <?php 
                            $bb_seo_detail = '';
                            foreach($matching_data as $procedure_data) {
                                $bb_seo_detail = isset($procedure_data['caseDetails'][0]) ? $procedure_data['caseDetails'][0] : [];
                            }    
                            if(isset($bb_seo_detail['seoHeadline']) && !empty($bb_seo_detail['seoHeadline'])) {
                                echo "<h2>" . $bb_seo_detail['seoHeadline'] . "</h2>";  
                            } else {
                            ?>
                            <h2><?=$procedure_title;?> Patient <span><?=$patient_id?></span></h2>
                            <?php 
                            }
                            if(in_array($procedure_data['photoSets'][0]['caseId'], $favorite_caseIds)) {
                            ?>
                            <img class="bb-heart-icon bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg" alt="heart">
                            <?php
                            } else {
                            ?>
                            <img class="bb-heart-icon bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart.svg" alt="heart">
                            <?php
                            }
                            ?>
                        </div>
                        <?php
                        $patient_detail = $height = $width = $race = $gender = $age = $bb_seo_detail = '';
                        
                        foreach($matching_data as $procedure_data) {
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
                                <img class="bbrag_gallery_image testing-image" src="<?php echo $bb_new_image_value; ?>" alt="<?php echo isset($value['seoAltText']) ? $value['seoAltText'] : ''; ?>">
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
                            <h2><?=$procedure_title;?> Patient <span><?=$patient_id?></span></h2>
                            <?php 
                            }
                            if(isset($bb_seo_detail['seoPageTitle']) && !empty($bb_seo_detail['seoPageTitle'])) {
                                $bb_seo_page_title = esc_js($bb_seo_detail['seoPageTitle']);
                            ?>
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    var bb_seo_title = "<?php echo $bb_seo_page_title; ?>";
                                    document.querySelector('head title').textContent = bb_seo_title;
                                });
                            </script>
                            <?php 
                            }
                            if(!empty($procedure_data) && is_array($procedure_data)) {
                                if(in_array($procedure_data['photoSets'][0]['caseId'], $favorite_caseIds)) {
                                ?>
                                <img class="bb-heart-icon bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg" alt="heart">
                                <?php
                                } else {
                                ?>
                                <img class="bb-heart-icon bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart.svg" alt="heart">
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
                        <?=$patient_detail?>
                        <a href="/<?php echo $parts[0]; ?>/consultation/" class="bb-sidebar-btn">REQUEST A CONSULTATION</a>
                        <script>
                            jQuery(document).ready(function($) {
                                var page_id_via_slug = "<?php echo $page_id_via_slug; ?>";
                                var url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
                                var page_url = "<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>";
                                $.ajax({
                                    type: 'POST',
                                    url: url,
                                    data: {
                                        action: 'bb_generate_pagination',
                                        page_id_via_slug: page_id_via_slug,
                                        page_url: page_url
                                    },
                                    success: function(response) {
                                        jQuery('.bb-patient-slides').html(response);
                                    },
                                    error: function(error) {
                                        console.log(error);
                                    }
                                });
                            });
                        </script>
                        <div class="bb-patient-slides"></div>
                    </div>
                </div>
                <?php
                $bb_current_case_page_count = get_option('bb_current_case_page_count_f');
                $schema = [
                    "@context" => "https://schema.org",
                    "@type" => "ImageGallery",
                    "name" => "Before and After Gallery $bb_case_url_title : Patient $bb_current_case_page_count",
                    "description" => "Photo gallery of $bb_case_url_title results showing before and after photos from different angles.",
                    'mainEntity' => [
                        '@type' => 'MedicalProcedure',
                        'name' => $default_and_seo_page_title,
                        'description' => $procedure_description,
                        'procedureType' => 'CosmeticProcedure',
                        'medicalSpecialty' => 'PlasticSurgery',
                        
                    ],
                    'image' => $images,
                    'breadcrumb' => array(
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => array(
                            array(
                                '@type' => 'ListItem',
                                'position' => 1,
                                'name' => 'Home',
                                'item' => home_url()
                            ),
                            array(
                                '@type' => 'ListItem',
                                'position' => 2,
                                'name' => $bb_gallery_page_title,
                                'item' => home_url() . '/' . $parts[0] . '/'
                            ),
                            array(
                                '@type' => 'ListItem',
                                'position' => 3,
                                'name' => 'Before and After ' . $case_page_title . ' Gallary',
                                'item' =>  $bb_pro_cat_page
                            ),
                            array(
                                '@type' => 'ListItem',
                                'position' => 4,
                                'name' => $default_and_seo_page_title,
                                'item' =>  home_url() . $bbrag_case_url
                            )
                        )
                    ),
                    'url' =>  home_url() . $bbrag_case_url
                ];
                $schema_json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                ?>
                <script type="application/ld+json">
                    <?php echo $schema_json; ?>
                </script>
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

