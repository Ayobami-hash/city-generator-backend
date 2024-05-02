<?php
     /*
     Plugin Name: City Generator Backend
     Description: A back-end plugin to generate city pages with custom content.
     Version: 1.0
     */

     function city_generator_activation() {
        // Perform initialization tasks, e.g., import SQL data.
        global $wpdb;

        // Path to SQL file
        $sql_file_path = plugin_dir_path(__FILE__) . 'sql/zip_codes.sql';
    
        if (file_exists($sql_file_path)) {
            // Read SQL file contents
            $sql_queries = file_get_contents($sql_file_path);
    
            // Split SQL queries (assuming ';' delimiter)
            $queries = explode(';', $sql_queries);
    
            // Execute each SQL query
            foreach ($queries as $query) {
                $query = trim($query); // Remove whitespace
                if (!empty($query)) {
                    // Execute SQL query
                    $wpdb->query($query);
                }
            }
        } else {
            error_log("SQL file not found: " . $sql_file_path);
        }
    }
    
    register_activation_hook(__FILE__, 'city_generator_activation');

    add_action('admin_menu', 'city_generator_admin_menu');
    function city_generator_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'City Generator',
            'City Generator',
            'manage_options',
            'city-generator',
            'city_generator_admin_page'
        );
    }

    function city_generator_admin_page() {
        require_once plugin_dir_path(__FILE__) . 'admin-page.php';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php
            // Render the form HTML structure
            echo city_generator_admin_page_html();
            ?>
        </div>
        <?php

    }
    
    add_action('admin_enqueue_scripts', 'city_generator_admin_enqueue_scripts');
    function city_generator_admin_enqueue_scripts($hook) {
        // Enqueue scripts and styles only on the plugin's admin page
        if ($hook === 'settings_page_city-generator') {
            wp_enqueue_script('city-generator-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), null, true);
            // wp_enqueue_style('city-generator-style', plugin_dir_url(__FILE__) . 'assets/style.css');

            // Localize script to pass AJAX URL and other data to JavaScript
            wp_localize_script('city-generator-script', 'city_generator', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('city_generator_nonce'),
            ));
        }
    }


?>