<?php
/**
 * Plugin Name: Coulomb Technology Pages
 * Plugin URI:  https://coulombtechnology.com
 * Description: Delivers the Coulomb Technology homepage, Series-B product page, and Contact page with proper CSS enqueuing and unfiltered HTML shortcodes.
 * Version:     1.5.9
 * Author:      Coulomb Technology
 */
if ( ! defined( 'ABSPATH' ) ) exit;
define( 'COULOMB_PAGES_VERSION', '1.5.9' );

// ─── 1. Enqueue CSS on the correct pages ────────────────────────────────────
function coulomb_enqueue_page_styles() {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) ) return;
    // Home page (ID 683)
    if ( $post->ID == 683 ) {
        wp_enqueue_style(
            'coulomb-home',
            plugin_dir_url( __FILE__ ) . 'css/home.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // Series-B page (ID 2363)
    if ( $post->ID == 2363 ) {
        wp_enqueue_style(
            'coulomb-seriesb',
            plugin_dir_url( __FILE__ ) . 'css/seriesb.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // Contact page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === 'contact' ) {
        wp_enqueue_style(
            'coulomb-contact',
            plugin_dir_url( __FILE__ ) . 'css/contact.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // Commercial & Industrial page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === 'commercial-industrial' ) {
        wp_enqueue_style(
            'coulomb-ci',
            plugin_dir_url( __FILE__ ) . 'css/ci.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // Defense & Government page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === 'defense-government' ) {
        wp_enqueue_style(
            'coulomb-def',
            plugin_dir_url( __FILE__ ) . 'css/def.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // Motive & Fleet page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === 'motive-fleet' ) {
        wp_enqueue_style(
            'coulomb-mf',
            plugin_dir_url( __FILE__ ) . 'css/mf.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // 48V Traction Battery page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === '48v-traction-battery' ) {
        wp_enqueue_style(
            'coulomb-tb48',
            plugin_dir_url( __FILE__ ) . 'css/tb48.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // Series-DC page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === '279v-series-dc' ) {
        wp_enqueue_style(
            'coulomb-seriesdc',
            plugin_dir_url( __FILE__ ) . 'css/seriesdc.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // 48V Series R page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === '48v-series-r' ) {
        wp_enqueue_style(
            'coulomb-seriesr',
            plugin_dir_url( __FILE__ ) . 'css/seriesr.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // Series-M page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === 'series-m' ) {
        wp_enqueue_style(
            'coulomb-seriesm',
            plugin_dir_url( __FILE__ ) . 'css/seriesm.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // Series-S page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === 'series-s' ) {
        wp_enqueue_style(
            'coulomb-seriess',
            plugin_dir_url( __FILE__ ) . 'css/seriess.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
    // All Products page — matched by slug
    if ( is_a( $post, 'WP_Post' ) && $post->post_name === 'all-products' ) {
        wp_enqueue_style(
            'coulomb-allproducts',
            plugin_dir_url( __FILE__ ) . 'css/allproducts.css',
            array(),
            COULOMB_PAGES_VERSION
        );
    }
}
add_action( 'wp_enqueue_scripts', 'coulomb_enqueue_page_styles', 9999 );

// Inline nonce for contact form AJAX (separate hook to ensure style is registered first)
function coulomb_contact_inline_nonce() {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) ) return;
    if ( $post->post_name === 'contact' ) {
        echo '<script>window.coulombContactNonce = ' . json_encode( wp_create_nonce( 'coulomb_contact_nonce' ) ) . ';</script>' . "\n";
    }
}
add_action( 'wp_head', 'coulomb_contact_inline_nonce' );

// ─── 2. Hide Avada header/footer on all Coulomb pages ───────────────────────
function coulomb_hide_avada_header_footer() {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) ) return;
    $coulomb_ids = array( 683, 2363 );
    if ( $post->post_name === 'contact' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'commercial-industrial' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'defense-government' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'motive-fleet' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === '48v-traction-battery' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === '279v-series-dc' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === '48v-series-r' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'series-m' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'series-s' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'all-products' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( in_array( $post->ID, $coulomb_ids ) ) {
        remove_action( 'avada_header', 'avada_header_content' );
        remove_action( 'avada_footer', 'avada_footer_content' );
        update_post_meta( $post->ID, 'pyre_display_header', 'no' );
        update_post_meta( $post->ID, 'pyre_footer_100_width', 'no' );
        update_post_meta( $post->ID, 'pyre_display_copyright', 'no' );
    }
}
add_action( 'wp', 'coulomb_hide_avada_header_footer' );

// ─── 3. Disable wpautop on all Coulomb pages ────────────────────────────────
function coulomb_disable_wpautop( $content ) {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) ) return $content;
    $coulomb_ids = array( 683, 2363 );
    if ( $post->post_name === 'contact' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'commercial-industrial' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'defense-government' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'motive-fleet' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === '48v-traction-battery' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === '279v-series-dc' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === '48v-series-r' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'series-m' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'series-s' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( $post->post_name === 'all-products' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( in_array( $post->ID, $coulomb_ids ) ) {
        remove_filter( 'the_content', 'wpautop' );
        remove_filter( 'the_content', 'wptexturize' );
    }
    return $content;
}
add_filter( 'the_content', 'coulomb_disable_wpautop', 1 );

// ─── 4. Shortcodes for page HTML content ────────────────────────────────────
function coulomb_home_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/home-body.html';
    if ( file_exists( $file ) ) {
        return file_get_contents( $file );
    }
    return '<!-- Coulomb home HTML not found -->';
}
add_shortcode( 'coulomb_home', 'coulomb_home_shortcode' );

function coulomb_seriesb_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/seriesb-body.html';
    if ( file_exists( $file ) ) {
        return file_get_contents( $file );
    }
    return '<!-- Coulomb Series-B HTML not found -->';
}
add_shortcode( 'coulomb_seriesb', 'coulomb_seriesb_shortcode' );

function coulomb_contact_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/contact-body.html';
    if ( file_exists( $file ) ) {
        return file_get_contents( $file );
    }
    return '<!-- Coulomb Contact HTML not found -->';
}
add_shortcode( 'coulomb_contact', 'coulomb_contact_shortcode' );

function coulomb_ci_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/ci-body.html';
    if ( file_exists( $file ) ) {
        $html = file_get_contents( $file );
        $img_url = plugin_dir_url( __FILE__ ) . 'images/';
        $html = str_replace( 'PLUGIN_IMG_URL/', $img_url, $html );
        return $html;
    }
    return '<!-- Coulomb C&I HTML not found -->';
}
add_shortcode( 'coulomb_ci', 'coulomb_ci_shortcode' );

function coulomb_def_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/def-body.html';
    if ( file_exists( $file ) ) {
        $html = file_get_contents( $file );
        $img_url = plugin_dir_url( __FILE__ ) . 'images/';
        $html = str_replace( 'PLUGIN_IMG_URL/', $img_url, $html );
        return $html;
    }
    return '<!-- Coulomb Defense HTML not found -->';
}
add_shortcode( 'coulomb_def', 'coulomb_def_shortcode' );

function coulomb_mf_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/mf-body.html';
    if ( file_exists( $file ) ) {
        $html = file_get_contents( $file );
        $img_url = plugin_dir_url( __FILE__ ) . 'images/';
        $html = str_replace( 'PLUGIN_IMG_URL/', $img_url, $html );
        return $html;
    }
    return '<!-- Coulomb Motive & Fleet HTML not found -->';
}
add_shortcode( 'coulomb_mf', 'coulomb_mf_shortcode' );

function coulomb_tb48_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/tb48-body.html';
    if ( file_exists( $file ) ) {
        $html = file_get_contents( $file );
        $img_url = plugin_dir_url( __FILE__ ) . 'images/';
        $html = str_replace( 'PLUGIN_IMG_URL/', $img_url, $html );
        return $html;
    }
    return '<!-- Coulomb 48V Traction Battery HTML not found -->';
}
add_shortcode( 'coulomb_tb48', 'coulomb_tb48_shortcode' );

function coulomb_seriesdc_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/seriesdc-body.html';
    if ( file_exists( $file ) ) {
        return file_get_contents( $file );
    }
    return '<!-- Coulomb Series-DC HTML not found -->';
}
add_shortcode( 'coulomb_seriesdc', 'coulomb_seriesdc_shortcode' );

function coulomb_seriesr_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/seriesr-body.html';
    if ( file_exists( $file ) ) {
        return file_get_contents( $file );
    }
    return '<!-- Coulomb 48V Series R HTML not found -->';
}
add_shortcode( 'coulomb_seriesr', 'coulomb_seriesr_shortcode' );
function coulomb_seriesm_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/seriesm-body.html';
    if ( file_exists( $file ) ) {
        return file_get_contents( $file );
    }
    return '<!-- Coulomb Series-M HTML not found -->';
}
add_shortcode( 'coulomb_seriesm', 'coulomb_seriesm_shortcode' );
function coulomb_seriess_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/seriess-body.html';
    if ( file_exists( $file ) ) {
        return file_get_contents( $file );
    }
    return '<!-- Coulomb Series-S HTML not found -->';
}
add_shortcode( 'coulomb_seriess', 'coulomb_seriess_shortcode' );

function coulomb_allproducts_shortcode() {
    $file = plugin_dir_path( __FILE__ ) . 'html/allproducts-body.html';
    if ( file_exists( $file ) ) {
        return file_get_contents( $file );
    }
    return '<!-- Coulomb All Products HTML not found -->';
}
add_shortcode( 'coulomb_allproducts', 'coulomb_allproducts_shortcode' );

// ─── 5. Allow unfiltered HTML from shortcodes ───────────────────────────────
add_filter( 'no_texturize_shortcodes', function( $shortcodes ) {
    $shortcodes[] = 'coulomb_home';
    $shortcodes[] = 'coulomb_seriesb';
    $shortcodes[] = 'coulomb_contact';
    $shortcodes[] = 'coulomb_ci';
    $shortcodes[] = 'coulomb_def';
    $shortcodes[] = 'coulomb_mf';
    $shortcodes[] = 'coulomb_tb48';
    $shortcodes[] = 'coulomb_seriesdc';
    $shortcodes[] = 'coulomb_seriesr';
    $shortcodes[] = 'coulomb_seriesm';
    $shortcodes[] = 'coulomb_seriess';
    $shortcodes[] = 'coulomb_allproducts';
    return $shortcodes;
});

add_filter( 'the_content', function( $content ) {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) ) return $content;
    $coulomb_ids = array( 683, 2363 );
    if ( isset( $post->post_name ) && $post->post_name === 'contact' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( isset( $post->post_name ) && $post->post_name === 'commercial-industrial' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( isset( $post->post_name ) && $post->post_name === 'defense-government' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( isset( $post->post_name ) && $post->post_name === 'motive-fleet' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( isset( $post->post_name ) && $post->post_name === '48v-traction-battery' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( isset( $post->post_name ) && $post->post_name === '279v-series-dc' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( isset( $post->post_name ) && $post->post_name === '48v-series-r' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( isset( $post->post_name ) && $post->post_name === 'series-m' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( isset( $post->post_name ) && $post->post_name === 'series-s' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( isset( $post->post_name ) && $post->post_name === 'all-products' ) {
        $coulomb_ids[] = $post->ID;
    }
    if ( in_array( $post->ID, $coulomb_ids ) ) {
        remove_filter( 'the_content', 'wpautop' );
        remove_filter( 'the_content', 'wptexturize' );
        remove_filter( 'the_content', 'convert_smilies' );
        remove_filter( 'the_content', 'convert_chars' );
    }
    return $content;
}, 0 );

// ─── 6. AJAX handler — Contact Form email to sales@coulombtechnology.com ────
function coulomb_handle_contact_form() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'coulomb_contact_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed.' ) );
    }

    // Sanitize inputs
    $first_name     = sanitize_text_field( $_POST['first_name'] ?? '' );
    $last_name      = sanitize_text_field( $_POST['last_name'] ?? '' );
    $email          = sanitize_email( $_POST['email'] ?? '' );
    $phone          = sanitize_text_field( $_POST['phone'] ?? '' );
    $company        = sanitize_text_field( $_POST['company'] ?? '' );
    $title_field    = sanitize_text_field( $_POST['title'] ?? '' );
    $industry       = sanitize_text_field( $_POST['industry'] ?? '' );
    $industry_other = sanitize_text_field( $_POST['industry_other'] ?? '' );
    $message        = sanitize_textarea_field( $_POST['message'] ?? '' );
    $inquiries      = isset( $_POST['inquiry'] ) ? (array) $_POST['inquiry'] : array();
    $inquiries      = array_map( 'sanitize_text_field', $inquiries );

    // Validate required fields
    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $company ) || empty( $industry ) ) {
        wp_send_json_error( array( 'message' => 'Required fields missing.' ) );
    }
    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Invalid email address.' ) );
    }

    // Build inquiry label
    $inquiry_labels = array(
        'system-design' => 'Request System Design',
        'demo'          => 'Schedule a Demo',
        'quote'         => 'Request a Quote',
        'consultation'  => 'General Inquiry / Consultation',
        'partner'       => 'Become a Partner',
        'media'         => 'Media & PR Inquiry',
    );
    $inquiry_list = array();
    foreach ( $inquiries as $inq ) {
        $inquiry_list[] = isset( $inquiry_labels[ $inq ] ) ? $inquiry_labels[ $inq ] : ucfirst( $inq );
    }
    $inquiry_str = ! empty( $inquiry_list ) ? implode( ', ', $inquiry_list ) : 'Not specified';

    // Industry display
    $industry_display = ( $industry === 'other' && ! empty( $industry_other ) ) ? $industry_other : $industry;

    // Build email to sales team
    $to      = 'sales@coulombtechnology.com';
    $subject = '[Coulomb Contact] ' . $inquiry_str . ' — ' . $company;

    $body  = "New contact form submission from coulombtechnology.com\n";
    $body .= str_repeat( '-', 50 ) . "\n\n";
    $body .= "INQUIRY TYPE:  " . $inquiry_str . "\n\n";
    $body .= "CONTACT INFO\n";
    $body .= "Name:          " . $first_name . ' ' . $last_name . "\n";
    $body .= "Email:         " . $email . "\n";
    $body .= "Phone:         " . ( $phone ?: 'Not provided' ) . "\n";
    $body .= "Company:       " . $company . "\n";
    $body .= "Title:         " . ( $title_field ?: 'Not provided' ) . "\n";
    $body .= "Industry:      " . $industry_display . "\n\n";
    if ( ! empty( $message ) ) {
        $body .= "PROJECT DETAILS\n";
        $body .= $message . "\n\n";
    }
    $body .= str_repeat( '-', 50 ) . "\n";
    $body .= "Submitted: " . current_time( 'mysql' ) . "\n";
    $body .= "IP Address: " . ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) . "\n";

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $first_name . ' ' . $last_name . ' <' . $email . '>',
        'From: Coulomb Website <noreply@coulombtechnology.com>',
    );

    $sent = wp_mail( $to, $subject, $body, $headers );

    if ( $sent ) {
        // Auto-reply to submitter
        $reply_subject = 'We received your message — Coulomb Technology';
        $reply_body  = "Hi " . $first_name . ",\n\n";
        $reply_body .= "Thank you for reaching out to Coulomb Technology. We've received your inquiry and a member of our engineering team will be in touch within 1 business day.\n\n";
        $reply_body .= "Your inquiry: " . $inquiry_str . "\n\n";
        $reply_body .= "In the meantime, feel free to explore our products at https://coulombtechnology.com/products/\n\n";
        $reply_body .= "Best regards,\n";
        $reply_body .= "The Coulomb Technology Team\n";
        $reply_body .= "https://coulombtechnology.com\n";
        $reply_headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: Coulomb Technology <noreply@coulombtechnology.com>',
        );
        wp_mail( $email, $reply_subject, $reply_body, $reply_headers );

        wp_send_json_success( array( 'message' => 'Message sent successfully.' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Email delivery failed. Please try again.' ) );
    }
}
add_action( 'wp_ajax_coulomb_contact_form', 'coulomb_handle_contact_form' );
add_action( 'wp_ajax_nopriv_coulomb_contact_form', 'coulomb_handle_contact_form' );

// ─── 7. Secure REST Deploy Endpoint ─────────────────────────────────────────
// Allows Manus to push individual file updates without a full plugin zip upload.
// Authenticated via WordPress application password (admin only).
add_action( 'rest_api_init', function () {
    register_rest_route( 'coulomb/v1', '/deploy', array(
        'methods'             => 'POST',
        'callback'            => 'coulomb_rest_deploy',
        'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        },
    ) );
} );

function coulomb_rest_deploy( WP_REST_Request $request ) {
    $files   = $request->get_param( 'files' );   // array of {path, content}
    $version = $request->get_param( 'version' );  // new version string e.g. "1.3.9"

    if ( empty( $files ) || ! is_array( $files ) ) {
        return new WP_Error( 'no_files', 'No files provided.', array( 'status' => 400 ) );
    }

    $plugin_dir = plugin_dir_path( __FILE__ );
    $updated    = array();
    $errors     = array();

    // Allowed file extensions for security
    $allowed_ext = array( 'html', 'css', 'php', 'js' );

    foreach ( $files as $file ) {
        $rel_path = isset( $file['path'] ) ? ltrim( $file['path'], '/' ) : '';
        $content  = isset( $file['content'] ) ? $file['content'] : null;

        if ( empty( $rel_path ) || $content === null ) {
            $errors[] = 'Invalid file entry: ' . json_encode( $file );
            continue;
        }

        // Security: only allow files within the plugin directory, no traversal
        $ext = strtolower( pathinfo( $rel_path, PATHINFO_EXTENSION ) );
        if ( ! in_array( $ext, $allowed_ext ) ) {
            $errors[] = 'Disallowed file type: ' . $rel_path;
            continue;
        }
        if ( strpos( $rel_path, '..' ) !== false ) {
            $errors[] = 'Path traversal attempt: ' . $rel_path;
            continue;
        }

        $abs_path = $plugin_dir . $rel_path;
        $dir      = dirname( $abs_path );

        // Ensure directory exists
        if ( ! is_dir( $dir ) ) {
            wp_mkdir_p( $dir );
        }

        $bytes = file_put_contents( $abs_path, $content );
        if ( $bytes === false ) {
            $errors[] = 'Failed to write: ' . $rel_path;
        } else {
            $updated[] = $rel_path . ' (' . $bytes . ' bytes)';
        }
    }

    // Optionally update the version in the main plugin file
    if ( ! empty( $version ) && preg_match( '/^\d+\.\d+\.\d+$/', $version ) ) {
        $main_file = $plugin_dir . 'coulomb-pages.php';
        $php       = file_get_contents( $main_file );
        // Update Version header
        $php = preg_replace( '/(\* Version:\s+)[\d.]+/', '${1}' . $version, $php );
        // Update all version strings in wp_enqueue_style calls
        $php = preg_replace( "/('[\d.]+')\s*\)\s*;(\s*\/\/ enqueue)/", "'" . $version . "' );$2", $php );
        file_put_contents( $main_file, $php );
        $updated[] = 'coulomb-pages.php (version bumped to ' . $version . ')';
    }

    // Flush object cache and page caches after deploy
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
    if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
    if ( function_exists( 'wpfc_clear_all_cache' ) ) { wpfc_clear_all_cache(); }
    // GoDaddy Managed WordPress cache purge
    if ( class_exists( 'WPaaS\\Cache' ) ) { do_action( 'wpaas_purge_cache' ); }
    // Flush rewrite rules and transients
    delete_transient( 'coulomb_pages_cache' );
    flush_rewrite_rules( false );

    return array(
        'success' => true,
        'updated' => $updated,
        'errors'  => $errors,
    );
}
