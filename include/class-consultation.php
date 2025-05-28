<?php
namespace mvpbrag;

class Consultation
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'form_entry_menu_page_consultation']);
        add_action('wp_ajax_consultation-pagination-load-posts', [$this, 'bb_consultation_pagination_load_posts']);
        add_action('wp_ajax_nopriv_consultation-pagination-load-posts', [$this, 'bb_consultation_pagination_load_posts']);
        add_action('wp_ajax_handle_form_submission', [$this, 'handle_form_submission']);
        add_action('wp_ajax_nopriv_handle_form_submission', [$this, 'handle_form_submission']);
    }

    public static function send_form_data_and_create_post($data, $url, $name, $description, $email, $phone)
    {
        $jsonData = json_encode($data);

        $response = wp_remote_post($url, array(
            'body' => $jsonData,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($jsonData),
            ),
        ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $body = wp_remote_retrieve_body($response);
            $responseData = json_decode($body, true);
        }

        if (isset($responseData['success']) && $responseData['success'] === true) {
            $post_id = wp_insert_post(array(
                'post_title' => $name,
                'post_content' => $description,
                'post_type' => 'form-entries',
                'post_status' => 'publish'
            ));

            if ($post_id) {
                update_post_meta($post_id, 'bb_email', $email);
                update_post_meta($post_id, 'bb_phone', $phone);
                wp_send_json_success('Thank you!');
            } else {
                wp_send_json_error('Form submission failed.');
            }
        }
    }

    public function handle_form_submission()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = sanitize_text_field($_POST['name']);
            $email = sanitize_email($_POST['email']);
            $phone = sanitize_text_field($_POST['phone']);
            $description = sanitize_textarea_field($_POST['description']);
            $api_tokens = get_option('bragbook_api_token');
            $websiteproperty_ids = get_option('bragbook_websiteproperty_id');
            $bb_gallery_stored_pages = get_option('bb_gallery_stored_pages');
            $combine_gallery_slug = get_option('combine_gallery_slug');

            $current_url = $_SERVER['HTTP_REFERER'];
            $parsed_url = parse_url($current_url);
            $parsed_url_parts = $parsed_url['path'];
            $bbragbook_url_trim = trim($parsed_url_parts, '/');
            $parts = explode('/', $bbragbook_url_trim);
            $index = array_search($parts[0], $bb_gallery_stored_pages);

            $data = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'details' => $description
            ];

            if ($parts[0] == $combine_gallery_slug) {
                foreach ($api_tokens as $api_token_index => $api_token_value) {
                    $websiteproperty_id = $websiteproperty_ids[$api_token_index];

                    $url = BB_BASE_URL . "/api/plugin/consultations?apiToken=" . $api_token_value . "&websitepropertyId=" . $websiteproperty_id;
                    self::send_form_data_and_create_post($data, $url, $name, $description, $email, $phone);
                }
            } else {
                $url = BB_BASE_URL . "/api/plugin/consultations?apiToken=" . $api_tokens[$index] . "&websitepropertyId=" . $websiteproperty_ids[$index];
                self::send_form_data_and_create_post($data, $url, $name, $description, $email, $phone);
            }

        } else {
            wp_send_json_error('Form submission failed.');
        }
        die();
    }

    public function bb_consultation_pagination_load_posts()
    {
        global $wpdb;
        $msg = '';
        if (isset($_POST['page'])) {
            $page = sanitize_text_field($_POST['page']);

            $cur_page = $page;
            $page -= 1;

            $per_page = 10;
            $previous_btn = true;
            $next_btn = true;
            $first_btn = true;
            $last_btn = true;
            $start = $page * $per_page;

            $table_name = $wpdb->prefix . "posts";
            $all_blog_posts = $wpdb->get_results($wpdb->prepare("
                SELECT * FROM " . $table_name . " WHERE post_type = 'form-entries' AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d, %d", $start, $per_page));

            $count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(ID) FROM " . $table_name . " WHERE post_type = 'form-entries' AND post_status = 'publish'", array()));

            foreach ($all_blog_posts as $key => $post):
                $post_date = date('Y-m-d H:i:s', strtotime($post->post_date));
                $msg .= '
                <tr>
                    <td>' . $post->post_title . '</td>
                    <td>' . get_post_meta($post->ID, 'bb_email', true) . '</td>
                    <td> ' . get_post_meta($post->ID, 'bb_phone', true) . '</td>
                    <td>' . $post_date . '</td>
                    <td colspan="4">' . $post->post_content . '</td>
                </tr>';
            endforeach;

            $no_of_paginations = ceil($count / $per_page);
            $start_loop = $cur_page;
            if ($no_of_paginations > $cur_page) {
                $end_loop = $cur_page;
            } else {
                $end_loop = $no_of_paginations;
            }
            $pag_container .= "
                <div class='bb-universal-pagination'>
                    <ul>";
            $pag_container .= "<li class='selected'>$count items</li>";
            if ($first_btn && $cur_page > 1) {
                $pag_container .= "<li p='1' class='active'><<</li>";
            } else if ($first_btn) {
                $pag_container .= "<li p='1' class='inactive'><<</li>";
            }

            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                $pag_container .= "<li p='$pre' class='active'><</li>";
            } else if ($previous_btn) {
                $pag_container .= "<li class='inactive'><</li>";
            }

            for ($i = $start_loop; $i <= $end_loop; $i++) {
                if ($cur_page == $i)
                    $pag_container .= "<li p='$i' class = 'selected' >$i of $no_of_paginations</li>";
                else
                    $pag_container .= "<li p='$i' class='active'>$i of $no_of_paginations</li>";
            }

            if ($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                $pag_container .= "<li p='$nex' class='active'>></li>";
            } else if ($next_btn) {
                $pag_container .= "<li class='inactive'>></li>";
            }

            if ($last_btn && $cur_page < $no_of_paginations) {
                $pag_container .= "<li p='$no_of_paginations' class='active'>>></li>";
            } else if ($last_btn) {
                $pag_container .= "<li p='$no_of_paginations' class='inactive'>>></li>";
            }

            $pag_container = $pag_container . "
                    </ul>
                </div>";

            $data = [
                'message' => $msg,
                'pagination' => $pag_container,
            ];
            $json_data = json_encode($data);
            header('Content-Type: application/json');
            echo $json_data;
        }
        exit();
    }

    function display_form_entries()
    {
        ?>
        <div class="content">
            <div class="inner-box content no-right-margin darkviolet">
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                        function bb_load_all_posts(page) {
                            $(".bb_pag_loading").fadeIn().css('background', '#ccc');
                            var data = {
                                page: page,
                                action: "consultation-pagination-load-posts"
                            };

                            $.post(ajaxurl, data, function (response) {
                                $(".bb_universal_container").empty().append(response.message);
                                $(".bb-pagination-nav").empty().append(response.pagination);
                                $(".bb_pag_loading").css({ 'background': 'none', 'transition': 'all 1s ease-out' });
                            });
                        }

                        bb_load_all_posts(1);
                        $(document).on('click', '.bb-universal-pagination li.active', function () {
                            var page = $(this).attr('p');
                            bb_load_all_posts(page);
                        });
                    }); 
                </script>
                <div class="bb_pag_loading">
                    <div class="wrap">
                        <h2>BB Consultation</h2>
                        <table class="wp-list-table widefat fixed striped form-entries-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Date</th>
                                    <th colspan="4">Description</th>
                                </tr>
                            </thead>
                            <tbody class="bb_universal_container">
                            </tbody>
                        </table>
                        <div class="bb-pagination-nav"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function form_entry_menu_page_consultation()
    {
        add_submenu_page(
            'bragbook-settings',
            'Consultation',
            'Consultation',
            'manage_options',
            'bb-consultation',
            array($this, 'display_form_entries')
        );
    }
}