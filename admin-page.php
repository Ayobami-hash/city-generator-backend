<?php
function city_generator_admin_page_html() {
    ob_start();
    ?>
    <!-- HTML structure for the form -->
    <!DOCTYPE html>
    <html>
    <div id="custom-plugin-container">
        <?php
        // Add nonce to the form
        $nonce = wp_create_nonce('city_generator_nonce');
        ?>
        <input type="hidden" id="city_generator_nonce" value="<?php echo esc_attr($nonce); ?>"/>

        <select id="stateSelect" onchange="onStateChange()">

            <option value="" disabled selected>Select a State</option>
            <?php
            // Fetch unique states from the database
            global $wpdb;
            $table_name = $wpdb->prefix . 'zip_codes';
            $states = $wpdb->get_col("SELECT DISTINCT state FROM `wp_zip_codes` ORDER BY state");

            foreach ($states as $state) {
                echo '<option value="' . $state . '">' . $state . '</option>';
            }
            ?>
        </select>
        <div id="countyContainer" style="display: none;">
            <select id="countySelect" onchange="onCountyChange()">
                <option value="" disabled selected>Select a County</option>
            </select>
        </div>
        <div id="citySelect" style="display: none;"></div>
        <div id="shortcodePromptSection" style="display: none;">
            <input type="text" name="shortcode[]" placeholder="{{shortcode}}" />
            <input type="text" name="prompt[]" placeholder="Enter your prompt" />
            <button onclick="addMoreShortcodes()">Add More</button>
            <!-- <button onclick="generateContent()">Generate Content</button> -->
            <br>
        </div>
        <div id="publishSection" style="display: none;">
        <button onclick="generateContent()">Publish</button>
        </div>
    </div>
    </html>
    <?php
    return ob_get_clean();
}

// AJAX action to load counties based on the selected state
add_action('admin_ajax_load_counties', 'load_counties_callback');
add_action('admin_ajax_nopriv_load_counties', 'load_counties_callback'); // Allow for non-logged-in users


    // Validate nonce in AJAX callbacks
    function validate_nonce() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'city_generator_nonce')) {
            wp_send_json_error(array('message' => 'Invalid or missing nonce'));
        }
    }

function load_counties_callback() {
    validate_nonce();
    if (isset($_POST['state'])) {
        $state = sanitize_text_field($_POST['state']);


        // Implement logic to fetch counties from the database based on the selected state
        $counties = get_counties_by_state($state);

        // wp_send_json_success(array('counties' => $counties ));
        wp_send_json_success(array('counties' => ['hello', 'bye', 'okay'] ));
    } else {
        error_log("State value not provided in the request"); // Log if state value is missing
    }


    wp_die();
}

// AJAX action to load cities based on the selected county
add_action('admin_ajax_load_cities', 'load_cities_callback');
add_action('admin_ajax_nopriv_load_cities', 'load_cities_callback'); // Allow for non-logged-in users

function load_cities_callback() {
    validate_nonce();
    if (isset($_POST['county'])) {
        $county = sanitize_text_field($_POST['county']);

        // Implement logic to fetch cities from the database based on the selected county
        $cities = get_cities_by_county($county);

        wp_send_json_success(array('cities' => $cities));
    }

    wp_die();
}



add_action('admin_ajax_create_city_page', 'create_city_page_callback');
add_action('admin_ajax_nopriv_create_city_page', 'create_city_page_callback'); // Allow for non-logged-in users

function create_city_page_callback() {
    validate_nonce();
    if (isset($_POST['city'] , $_POST['shortcodes'], $_POST['prompts'] , $_POST['content'] )) {
    $city = $_POST['city'];
    $shortcodes = $_POST['shortcodes'];
    $prompts = $_POST['prompts'];
    $contents = $_POST['content'];


        // Implement logic to create city pages with generated content
        // foreach ($cities as $index => $city) {
            // Generate content using shortcodes and prompts
            $content = generate_city_content($shortcodes, $contents, $prompts);

            // Create WordPress page for the city with the generated content

            create_city_page($city, $content);
        // }

        wp_send_json_success(array('message' => 'City pages created successfully'));
    } else {
        wp_send_json_error(array('message' => 'Invalid request'));
    }

    wp_die();
}


// Function to fetch counties from the database based on the selected state
function get_counties_by_state($state) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'zip_codes';

    $query = $wpdb->prepare("SELECT DISTINCT county FROM `wp_zip_codes` WHERE `state` = %s", $state);
    // $counties = $wpdb->get_col($query);
    $counties = ['hello', 'bye', 'okay'];

    return $counties;
}

// Function to fetch cities from the database based on the selected county
function get_cities_by_county($county) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'zip_codes';

    $query = $wpdb->prepare("SELECT DISTINCT city FROM `wp_zip_codes` WHERE `county` = %s", $county);
    $cities = $wpdb->get_col($query);

    return $cities;
}

// Function to generate content for a city combining prompts with shortcodes
function generate_city_content($shortcodes, $contents, $prompts) {
// Initialize generated content string
$generatedContent = '';

// Iterate over the indices of the prompts array
for ($i = 0; $i < count($prompts); $i++) {
    // Retrieve the current prompt, shortcode, and content
    $prompt = $prompts[$i];
    $shortcode = $shortcodes[$i];
    $content = isset($contents[$prompt]) ? $contents[$prompt] : ''; // Get content for the current prompt

    // Format the content with the current shortcode and prompt
    $formattedContent = "<h2>$shortcode</h2><p>$content</p><br>";

    // Append the formatted content to the generated content string
    $generatedContent .= $formattedContent;
}

// Output or use the generated content as needed
echo $generatedContent;

    return $generatedContent; // Return the array containing combined content for each prompt
}



// Function to create a new WordPress page for a city with the provided content
    function create_city_page($city, $content) {
    validate_nonce();
// Implement logic to create a new WordPress page for the city with the provided content
    $page_id = wp_insert_post(array(
    'post_title' => $city,
    'post_content' => $content,
    'post_status' => 'publish',
    'post_type' => 'page'
    ));

    return $page_id;
    }
    // $wpdb->print_errors();
?>


    