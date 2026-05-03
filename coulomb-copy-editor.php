<?php
/**
 * Coulomb Technology — Copy Editor Admin Panel
 *
 * Adds a "Copy Editor" menu item to wp-admin that lets authorised users
 * edit the full HTML body of every Coulomb page and publish changes live
 * with a single click.  No coding required.
 *
 * Access: WordPress users with the manage_options capability (Administrators).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Register admin menu ──────────────────────────────────────────────────────
add_action( 'admin_menu', function () {
    add_menu_page(
        'Coulomb Copy Editor',
        'Copy Editor',
        'manage_options',
        'coulomb-copy-editor',
        'coulomb_copy_editor_page',
        'dashicons-edit-page',
        30
    );
} );

// ── Page definitions — label => html file (relative to plugin dir) ───────────
function coulomb_editor_pages() {
    return array(
        'home'        => array( 'label' => 'Home',                    'file' => 'html/home-body.html' ),
        'seriesb'     => array( 'label' => 'Series-C (279V AC)',       'file' => 'html/seriesb-body.html' ),
        'seriesdc'    => array( 'label' => 'Series-DC (279V DC)',      'file' => 'html/seriesdc-body.html' ),
        'seriesr'     => array( 'label' => 'Series-R (48V)',           'file' => 'html/seriesr-body.html' ),
        'seriesm'     => array( 'label' => 'Series-M (48V Motive)',    'file' => 'html/seriesm-body.html' ),
        'seriess'     => array( 'label' => 'Series-S (12V)',           'file' => 'html/seriess-body.html' ),
        'allproducts' => array( 'label' => 'All Products',             'file' => 'html/allproducts-body.html' ),
        'ci'          => array( 'label' => 'Commercial & Industrial',  'file' => 'html/ci-body.html' ),
        'def'         => array( 'label' => 'Defense & Government',     'file' => 'html/def-body.html' ),
        'mf'          => array( 'label' => 'Motive & Traction',        'file' => 'html/mf-body.html' ),
        'besscore'    => array( 'label' => 'BESS Core Technology',     'file' => 'html/besscore-body.html' ),
        'sodium'      => array( 'label' => 'Sodium-Ion Technology',    'file' => 'html/sodium-body.html' ),
        'smartems'    => array( 'label' => 'Smart EMS',                'file' => 'html/smartems-body.html' ),
        'about'       => array( 'label' => 'About Us',                 'file' => 'html/about-body.html' ),
        'contact'     => array( 'label' => 'Contact',                  'file' => 'html/contact-body.html' ),
    );
}

// ── Enqueue CodeMirror-style editor assets ───────────────────────────────────
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( $hook !== 'toplevel_page_coulomb-copy-editor' ) return;
    // Use WordPress's built-in CodeMirror (available since WP 4.9)
    $cm_settings = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
    wp_enqueue_script( 'wp-theme-plugin-editor' );
    wp_enqueue_style( 'wp-codemirror' );
} );

// ── Handle save (POST) ───────────────────────────────────────────────────────
function coulomb_copy_editor_handle_save() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorised', 403 );
    }
    check_admin_referer( 'coulomb_copy_editor_save' );

    $page_key = sanitize_key( $_POST['page_key'] ?? '' );
    $pages    = coulomb_editor_pages();

    if ( ! isset( $pages[ $page_key ] ) ) {
        return array( 'error' => 'Unknown page key.' );
    }

    // Get raw HTML content — we allow unfiltered HTML for admins
    $new_content = wp_unslash( $_POST['page_html'] ?? '' );

    $plugin_dir = plugin_dir_path( __FILE__ );
    $file_path  = $plugin_dir . $pages[ $page_key ]['file'];

    // Write to disk
    $bytes = file_put_contents( $file_path, $new_content );
    if ( $bytes === false ) {
        return array( 'error' => 'Failed to write file. Check server permissions.' );
    }

    // Flush all caches (same as deploy endpoint)
    if ( function_exists( 'wp_cache_flush' ) )        { wp_cache_flush(); }
    if ( function_exists( 'rocket_clean_domain' ) )   { rocket_clean_domain(); }
    if ( function_exists( 'w3tc_flush_all' ) )        { w3tc_flush_all(); }
    if ( function_exists( 'wpfc_clear_all_cache' ) )  { wpfc_clear_all_cache(); }
    do_action( 'wpaas_purge_cache' );
    if ( function_exists( 'godaddy_mwp_flush_cache' ) ) { godaddy_mwp_flush_cache(); }
    wp_cache_flush();
    set_transient( 'coulomb_last_deploy', time(), 3600 );
    delete_transient( 'coulomb_pages_cache' );
    flush_rewrite_rules( false );

    return array( 'success' => true, 'bytes' => $bytes, 'page' => $pages[ $page_key ]['label'] );
}

// ── Main admin page render ───────────────────────────────────────────────────
function coulomb_copy_editor_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'You do not have permission to access this page.' );
    }

    $pages   = coulomb_editor_pages();
    $notice  = null;
    $plugin_dir = plugin_dir_path( __FILE__ );

    // Handle save
    if ( isset( $_POST['coulomb_copy_editor_save'] ) ) {
        $result = coulomb_copy_editor_handle_save();
        if ( isset( $result['success'] ) ) {
            $notice = array( 'type' => 'success', 'msg' => '&#10003; <strong>' . esc_html( $result['page'] ) . '</strong> published successfully to the live site.' );
        } else {
            $notice = array( 'type' => 'error', 'msg' => '&#9888; Error: ' . esc_html( $result['error'] ) );
        }
    }

    // Active page
    $active_key = sanitize_key( $_GET['edit'] ?? array_key_first( $pages ) );
    if ( ! isset( $pages[ $active_key ] ) ) {
        $active_key = array_key_first( $pages );
    }

    // Read current file content
    $file_path   = $plugin_dir . $pages[ $active_key ]['file'];
    $html_content = file_exists( $file_path ) ? file_get_contents( $file_path ) : '';

    ?>
    <div class="wrap coulomb-editor-wrap">

        <div class="coulomb-editor-header">
            <div class="coulomb-editor-logo">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#00b050" stroke-width="2.2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                <span>Coulomb Technology</span>
            </div>
            <h1>Copy Editor</h1>
            <p class="coulomb-editor-subtitle">Edit the text on any page and click <strong>Publish</strong> to go live instantly.</p>
        </div>

        <?php if ( $notice ) : ?>
        <div class="notice notice-<?php echo $notice['type']; ?> is-dismissible coulomb-notice">
            <p><?php echo $notice['msg']; ?></p>
        </div>
        <?php endif; ?>

        <div class="coulomb-editor-layout">

            <!-- Sidebar: page list -->
            <nav class="coulomb-editor-nav">
                <div class="coulomb-nav-section-label">PRODUCTS</div>
                <?php
                $sections = array(
                    'PRODUCTS'    => array( 'allproducts', 'seriesb', 'seriesdc', 'seriesr', 'seriesm', 'seriess' ),
                    'INDUSTRIES'  => array( 'ci', 'def', 'mf' ),
                    'TECHNOLOGY'  => array( 'besscore', 'sodium', 'smartems' ),
                    'COMPANY'     => array( 'home', 'about', 'contact' ),
                );
                foreach ( $sections as $section_label => $keys ) :
                    ?>
                    <div class="coulomb-nav-section-label"><?php echo esc_html( $section_label ); ?></div>
                    <?php foreach ( $keys as $key ) :
                        if ( ! isset( $pages[ $key ] ) ) continue;
                        $is_active = ( $key === $active_key );
                        $edit_url  = admin_url( 'admin.php?page=coulomb-copy-editor&edit=' . $key );
                        ?>
                        <a href="<?php echo esc_url( $edit_url ); ?>"
                           class="coulomb-nav-item <?php echo $is_active ? 'active' : ''; ?>">
                            <?php echo esc_html( $pages[ $key ]['label'] ); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </nav>

            <!-- Main editor area -->
            <div class="coulomb-editor-main">
                <div class="coulomb-editor-toolbar">
                    <div class="coulomb-editor-page-title">
                        Editing: <strong><?php echo esc_html( $pages[ $active_key ]['label'] ); ?></strong>
                    </div>
                    <div class="coulomb-editor-toolbar-actions">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button coulomb-btn-preview">
                            &#128065; Preview Site
                        </a>
                    </div>
                </div>

                <div class="coulomb-editor-tip">
                    <strong>How to use:</strong> Edit any text below. You can change words, sentences, bullet points, headings, and numbers. 
                    Do <em>not</em> delete the HTML tags (the parts in &lt;angle brackets&gt;) — only change the text between them.
                    When done, click <strong>Publish Changes</strong>.
                </div>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=coulomb-copy-editor&edit=' . $active_key ) ); ?>">
                    <?php wp_nonce_field( 'coulomb_copy_editor_save' ); ?>
                    <input type="hidden" name="coulomb_copy_editor_save" value="1">
                    <input type="hidden" name="page_key" value="<?php echo esc_attr( $active_key ); ?>">

                    <textarea
                        id="coulomb-html-editor"
                        name="page_html"
                        class="coulomb-html-textarea"
                        rows="40"
                        spellcheck="true"
                    ><?php echo esc_textarea( $html_content ); ?></textarea>

                    <div class="coulomb-editor-footer">
                        <button type="submit" class="button button-primary coulomb-btn-publish">
                            &#9654; Publish Changes to Live Site
                        </button>
                        <span class="coulomb-editor-file-hint">File: <?php echo esc_html( $pages[ $active_key ]['file'] ); ?></span>
                    </div>
                </form>
            </div>

        </div><!-- .coulomb-editor-layout -->
    </div><!-- .wrap -->

    <style>
    /* ── Coulomb Copy Editor Styles ── */
    .coulomb-editor-wrap { max-width: 100%; padding: 0; }

    .coulomb-editor-header {
        background: #0d1117;
        color: #fff;
        padding: 20px 28px 18px;
        margin: -10px -20px 24px;
        border-bottom: 3px solid #00b050;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .coulomb-editor-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        font-size: 15px;
        color: #00b050;
        margin-right: 8px;
    }
    .coulomb-editor-header h1 {
        color: #fff !important;
        font-size: 22px !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1.2 !important;
    }
    .coulomb-editor-subtitle {
        color: #aaa;
        font-size: 13px;
        margin: 0;
        flex-basis: 100%;
        padding-left: 44px;
    }

    .coulomb-notice { margin: 0 0 20px; }

    .coulomb-editor-layout {
        display: flex;
        gap: 0;
        min-height: 80vh;
        border: 1px solid #ddd;
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
    }

    /* Sidebar */
    .coulomb-editor-nav {
        width: 200px;
        min-width: 200px;
        background: #1a1a2e;
        padding: 16px 0;
        overflow-y: auto;
    }
    .coulomb-nav-section-label {
        font-size: 10px;
        font-weight: 700;
        color: #666;
        letter-spacing: 1.2px;
        padding: 12px 16px 4px;
        text-transform: uppercase;
    }
    .coulomb-nav-item {
        display: block;
        padding: 8px 16px;
        color: #ccc;
        text-decoration: none;
        font-size: 13px;
        border-left: 3px solid transparent;
        transition: all 0.15s;
    }
    .coulomb-nav-item:hover {
        background: rgba(0,176,80,0.1);
        color: #fff;
        border-left-color: #00b050;
    }
    .coulomb-nav-item.active {
        background: rgba(0,176,80,0.15);
        color: #00b050;
        border-left-color: #00b050;
        font-weight: 600;
    }

    /* Main area */
    .coulomb-editor-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .coulomb-editor-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
    }
    .coulomb-editor-page-title {
        font-size: 14px;
        color: #333;
    }
    .coulomb-btn-preview {
        font-size: 12px !important;
    }

    .coulomb-editor-tip {
        background: #fffbea;
        border-left: 4px solid #f0b429;
        padding: 10px 16px;
        font-size: 13px;
        color: #555;
        margin: 0;
    }

    .coulomb-html-textarea {
        width: 100% !important;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace !important;
        font-size: 13px !important;
        line-height: 1.6 !important;
        border: none !important;
        border-bottom: 1px solid #e0e0e0 !important;
        border-radius: 0 !important;
        resize: vertical !important;
        padding: 16px 20px !important;
        background: #fafafa !important;
        color: #1a1a1a !important;
        box-shadow: none !important;
        outline: none !important;
        min-height: 500px;
    }
    .coulomb-html-textarea:focus {
        background: #fff !important;
        box-shadow: inset 0 0 0 2px #00b050 !important;
    }

    .coulomb-editor-footer {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 20px;
        background: #f8f9fa;
        border-top: 1px solid #e0e0e0;
    }
    .coulomb-btn-publish {
        background: #00b050 !important;
        border-color: #009040 !important;
        color: #fff !important;
        font-size: 14px !important;
        padding: 8px 20px !important;
        height: auto !important;
        font-weight: 600 !important;
    }
    .coulomb-btn-publish:hover {
        background: #009040 !important;
        border-color: #007030 !important;
    }
    .coulomb-editor-file-hint {
        font-size: 11px;
        color: #999;
        font-family: monospace;
    }

    /* CodeMirror overrides */
    .CodeMirror {
        height: auto !important;
        min-height: 500px;
        font-size: 13px !important;
        line-height: 1.6 !important;
        border: none !important;
        border-bottom: 1px solid #e0e0e0 !important;
        border-radius: 0 !important;
    }
    </style>

    <script>
    jQuery(function($) {
        // Initialize CodeMirror on the textarea if available
        if (typeof wp !== 'undefined' && wp.codeEditor) {
            var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
            editorSettings.codemirror = _.extend({}, editorSettings.codemirror, {
                mode: 'htmlmixed',
                lineNumbers: true,
                lineWrapping: true,
                indentUnit: 2,
                tabSize: 2,
                autoCloseTags: true,
                matchBrackets: true,
                theme: 'default',
            });
            var editor = wp.codeEditor.initialize($('#coulomb-html-editor'), editorSettings);

            // Sync CodeMirror back to textarea on form submit
            $('form').on('submit', function() {
                if (editor && editor.codemirror) {
                    editor.codemirror.save();
                }
            });
        }

        // Confirm before publishing
        $('.coulomb-btn-publish').on('click', function(e) {
            var pageName = $(this).closest('form').find('input[name="page_key"]').val();
            if (!confirm('Publish changes to the live site?\n\nThis will immediately update the page for all visitors.')) {
                e.preventDefault();
            }
        });
    });
    </script>
    <?php
}
