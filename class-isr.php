<?php

    /**
     * Gravity ISR plugin class file.
     *
     * @package Gravity ISR
     * @author Derek Olalehe
     * @license GPL2
     * @copyright 2021
     */

    class ISR
    {

        public static string $version = '1.1.0';
        public static string $plugin_slug = 'gravity-isr';
        public static ?ISR $instance = null;

        public function __construct()
        {
            global $wpdb;

            require_once('includes/user-profile-custom-fields.php');
            require_once('includes/ajax-methods.php');
            require_once('includes/admin-pages.php');
            require_once('page-templating.php');

            add_action('wp_enqueue_scripts', [$this, 'isr_scripts_styles']);
            add_action('admin_enqueue_scripts', [$this, 'isr_admin_scripts_styles']);


            add_action('wp_ajax_recalc_data', 'recalc_data');
            add_action('wp_ajax_nopriv_recalc_data', 'recalc_data');
            add_action('wp_ajax_fetch_data', 'fetch_data');
            add_action('wp_ajax_nopriv_fetch_data', 'fetch_data');
        }

        public function isr_scripts_styles(): void
        {

            wp_enqueue_script('jquery', '', false, true);

            //Make ajax url available on the front end
            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $params = array(
                'ajaxurl' => admin_url('admin-ajax.php', $protocol),
                'home_url' => home_url(),
                'theme_url' => get_template_directory_uri(),
                'plugins_url' => plugins_url(),
            );


            if (is_page_template('report-page.php')) {
                // Primary JS, CSS and Localization of Variables
                wp_enqueue_script('isr-main', plugins_url('assets/js/main.js?v=' . microtime(), __FILE__), array('jquery'), self::$version, true);
                wp_enqueue_style('isr-style', plugins_url('assets/css/style.css?v=' . microtime(), __FILE__), false, self::$version);
                wp_localize_script('isr-main', 'isr_urls', $params);

                // Bootstrap CDN
                wp_enqueue_style('isr-bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', false, self::$version);
                wp_enqueue_script('isr-bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', false, self::$version, true);
            }


        }

        public function isr_admin_scripts_styles(): void
        {

            //Make ajax url available on the front end
            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $params = array(
                'ajaxurl' => admin_url('admin-ajax.php', $protocol),
                'home_url' => home_url(),
                'theme_url' => get_template_directory_uri(),
                'plugins_url' => plugins_url(),
            );

            wp_enqueue_script('isr-admin', plugins_url('assets/js/admin.js?v=' . microtime(), __FILE__), array('jquery'), self::$version, true);
            wp_localize_script('isr-admin', 'isr_urls', $params);
        }


        public static function get_instance(): ?ISR
        {
            if (self::$instance == null) {
                self::$instance = new self;
            }

            return self::$instance;
        }
    }



