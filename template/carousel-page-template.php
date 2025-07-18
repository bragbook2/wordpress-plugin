<?php
/*
Template Name: Carousel Page Template
*/
?>
<div class="bb-container-main">
    <?php
    $mainClass = get_option('bb_design_plugin_selector') === 'v2' ? 'bb-main bb-main-v2' : 'bb-main';
    ?>
    <main class="<?php echo esc_attr($mainClass); ?>">
       <?php include plugin_dir_path(__FILE__) . 'sidebar-template.php';
        
        $brag_page_data = get_option('bragbook_landing_page_text');
        $explode_string = explode('[', $brag_page_data);
        $input_string = $explode_string['1'];
        $pattern = '/category="([^"]*)"/';
        if (preg_match($pattern, $input_string, $matches)) {
            $carousel_category = $matches[1];
        }
        $bbrag_procedure_title = preg_replace('/[^a-zA-Z0-9]+/', '-', $carousel_category);
        $bbrag_procedure_title = strtolower($bbrag_procedure_title);
        $category_title_ = get_option($bbrag_procedure_title);
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
            <?php
            if (isset($explode_string[0])) {
                $content = $explode_string[0];
                $lines = preg_split("/\r\n|\n|\r/", $content);
                $output = '';
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if ($trimmedLine === '') {
                        continue;
                    }
                    $output .= ($trimmedLine === strip_tags($trimmedLine)) ? "<p>{$trimmedLine}</p>" : $trimmedLine;
                }
                echo $output;
            }

            $output = array_map(function ($item) {
                $item = str_replace("\r\n", "\n", $item);
                $pos = strpos($item, ']');

                if ($pos !== false) {
                    $shortcode = substr($item, 0, $pos + 1);
                    $after = substr($item, $pos + 1);
                    $afterLines = explode("\n", $after);
                    $afterLines = array_map(function ($line) {
                        $trimmed = trim($line);
                        if ($trimmed === '')
                            return $line;
                        return ($trimmed === strip_tags($trimmed)) ? '<p>' . $trimmed . '</p>' : $line;
                    }, $afterLines);
                    return $shortcode . "\n" . implode("\n", $afterLines);
                } else {
                    $lines = explode("\n", $item);
                    $lines = array_map(function ($line) {
                        $trimmed = trim($line);
                        if ($trimmed === '')
                            return $line;
                        return ($trimmed === strip_tags($trimmed)) ? '<p>' . $trimmed . '</p>' : $line;
                    }, $lines);
                    return implode("\n", $lines);
                }
            }, $explode_string);

            foreach ($output as $string) {
                if (strpos($string, 'shortcode') !== false) {
                    echo do_shortcode('[' . $string);
                }
            }
            ?>
            <a href="/<?php echo $parts[0]; ?>/consultation/" class="bb-sidebar-btn mobile-footer">REQUEST A
                CONSULTATION</a>
            <div class="bb-bottom-bar">
                <img src="<?php echo BB_PLUGIN_DIR_PATH ?>assets/images/myfavs-logo.png" alt="logo">
                <p>
                    <span>Use the MyFavorites tool</span> to help communicate your specific goals. If a result speaks to
                    you, tap the heart.
                </p>
            </div>
        </div>
    </main>
</div>
<?php
get_footer();
