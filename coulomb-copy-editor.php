<?php
/**
 * Coulomb Technology — Copy Editor Admin Panel v2
 *
 * Page dashboard + no-code field editor.
 * Each page's editable text fields are presented as clean labeled inputs,
 * grouped by section. No HTML visible. Publish goes live instantly.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Page registry ────────────────────────────────────────────────────────────
function coulomb_editor_pages() {
    return array(
        'home'        => array( 'label' => 'Home',                    'file' => 'html/home-body.html',        'icon' => '🏠', 'desc' => 'Main landing page' ),
        'allproducts' => array( 'label' => 'All Products',            'file' => 'html/allproducts-body.html', 'icon' => '📦', 'desc' => 'Products overview page' ),
        'seriesb'     => array( 'label' => 'Series-C (279V AC)',       'file' => 'html/seriesb-body.html',     'icon' => '⚡', 'desc' => 'Commercial BESS product' ),
        'seriesdc'    => array( 'label' => 'Series-DC (279V DC)',      'file' => 'html/seriesdc-body.html',    'icon' => '⚡', 'desc' => 'DC-coupled BESS product' ),
        'seriesr'     => array( 'label' => 'Series-R (48V)',           'file' => 'html/seriesr-body.html',     'icon' => '🔋', 'desc' => 'Rack battery product' ),
        'seriesm'     => array( 'label' => 'Series-M (48V Motive)',    'file' => 'html/seriesm-body.html',     'icon' => '🔋', 'desc' => 'Motive battery product' ),
        'seriess'     => array( 'label' => 'Series-S (12V)',           'file' => 'html/seriess-body.html',     'icon' => '🔋', 'desc' => 'Small battery product' ),
        'ci'          => array( 'label' => 'Commercial & Industrial',  'file' => 'html/ci-body.html',          'icon' => '🏭', 'desc' => 'C&I industry page' ),
        'def'         => array( 'label' => 'Defense & Government',     'file' => 'html/def-body.html',         'icon' => '🛡️', 'desc' => 'Defense industry page' ),
        'mf'          => array( 'label' => 'Motive & Traction',        'file' => 'html/mf-body.html',          'icon' => '🚜', 'desc' => 'Motive industry page' ),
        'besscore'    => array( 'label' => 'BESS Core Technology',     'file' => 'html/besscore-body.html',    'icon' => '🔬', 'desc' => 'Technology page' ),
        'sodium'      => array( 'label' => 'Sodium-Ion Technology',    'file' => 'html/sodium-body.html',      'icon' => '⚗️', 'desc' => 'Chemistry technology page' ),
        'smartems'    => array( 'label' => 'Smart EMS',                'file' => 'html/smartems-body.html',    'icon' => '🖥️', 'desc' => 'Software platform page' ),
        'about'       => array( 'label' => 'About Us',                 'file' => 'html/about-body.html',       'icon' => '👥', 'desc' => 'Company about page' ),
        'contact'     => array( 'label' => 'Contact',                  'file' => 'html/contact-body.html',     'icon' => '✉️', 'desc' => 'Contact page' ),
    );
}

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

// ── Parse HTML file into editable fields using data-ceid markers ─────────────
function coulomb_parse_fields( $html_content ) {
    $fields = array();
    
    // Match all elements with data-ceid attribute
    // Pattern: <tag ... data-ceid="ID" ... data-celabel="LABEL" ... data-cesection="SECTION" ...>TEXT</tag>
    $pattern = '/<([a-z][a-z0-9]*)\s[^>]*data-ceid="([^"]+)"[^>]*data-celabel="([^"]+)"[^>]*data-cesection="([^"]+)"[^>]*>(.*?)<\/\1>/si';
    
    if ( preg_match_all( $pattern, $html_content, $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $m ) {
            $tag     = $m[1];
            $ceid    = $m[2];
            $label   = html_entity_decode( $m[3], ENT_QUOTES );
            $section = html_entity_decode( $m[4], ENT_QUOTES );
            $inner   = $m[5];
            
            // Strip inner HTML tags to get plain text
            $text = wp_strip_all_tags( $inner );
            $text = html_entity_decode( $text, ENT_QUOTES );
            $text = preg_replace( '/\s+/', ' ', $text );
            $text = trim( $text );
            
            if ( empty( $text ) || strlen( $text ) < 2 ) continue;
            
            $fields[ $ceid ] = array(
                'ceid'    => $ceid,
                'label'   => $label,
                'section' => $section,
                'tag'     => $tag,
                'text'    => $text,
                'type'    => ( strlen( $text ) > 100 || $tag === 'p' ) ? 'textarea' : 'text',
            );
        }
    }
    
    return $fields;
}

// ── Group fields by section ──────────────────────────────────────────────────
function coulomb_group_fields( $fields ) {
    $grouped = array();
    foreach ( $fields as $ceid => $field ) {
        $section = $field['section'];
        if ( ! isset( $grouped[ $section ] ) ) {
            $grouped[ $section ] = array();
        }
        $grouped[ $section ][ $ceid ] = $field;
    }
    return $grouped;
}

// ── Save updated fields back to HTML file ───────────────────────────────────
function coulomb_save_fields( $html_content, $updates ) {
    foreach ( $updates as $ceid => $new_text ) {
        $ceid     = sanitize_text_field( $ceid );
        $new_text = sanitize_textarea_field( $new_text );
        $new_text = esc_html( $new_text );
        
        // Replace the inner content of the element with this data-ceid
        // We need to preserve the opening tag (with all attributes) and closing tag
        $pattern = '/(<([a-z][a-z0-9]*)\s[^>]*data-ceid="' . preg_quote( $ceid, '/' ) . '"[^>]*>)(.*?)(<\/\2>)/si';
        $html_content = preg_replace( $pattern, '${1}' . $new_text . '${4}', $html_content, 1 );
    }
    return $html_content;
}

// ── Flush all caches ─────────────────────────────────────────────────────────
function coulomb_flush_caches() {
    if ( function_exists( 'wp_cache_flush' ) )        { wp_cache_flush(); }
    if ( function_exists( 'rocket_clean_domain' ) )   { rocket_clean_domain(); }
    if ( function_exists( 'w3tc_flush_all' ) )        { w3tc_flush_all(); }
    if ( function_exists( 'wpfc_clear_all_cache' ) )  { wpfc_clear_all_cache(); }
    do_action( 'wpaas_purge_cache' );
    if ( function_exists( 'godaddy_mwp_flush_cache' ) ) { godaddy_mwp_flush_cache(); }
    delete_transient( 'coulomb_pages_cache' );
    flush_rewrite_rules( false );
}

// ── Main admin page ──────────────────────────────────────────────────────────
function coulomb_copy_editor_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'You do not have permission to access this page.' );
    }

    $pages      = coulomb_editor_pages();
    $plugin_dir = plugin_dir_path( __FILE__ );
    $notice     = null;
    $active_key = sanitize_key( $_GET['edit'] ?? '' );

    // ── Handle save ──────────────────────────────────────────────────────────
    if ( isset( $_POST['coulomb_ce_save'] ) && $active_key && isset( $pages[ $active_key ] ) ) {
        check_admin_referer( 'coulomb_ce_save_' . $active_key );
        
        $file_path = $plugin_dir . $pages[ $active_key ]['file'];
        $html      = file_get_contents( $file_path );
        
        $updates = array();
        foreach ( $_POST as $key => $val ) {
            if ( strpos( $key, 'cefield_' ) === 0 ) {
                $ceid = substr( $key, 8 ); // strip 'cefield_'
                $updates[ $ceid ] = wp_unslash( $val );
            }
        }
        
        if ( ! empty( $updates ) ) {
            $new_html = coulomb_save_fields( $html, $updates );
            $bytes    = file_put_contents( $file_path, $new_html );
            
            if ( $bytes !== false ) {
                coulomb_flush_caches();
                $notice = array( 'type' => 'success', 'msg' => '&#10003; <strong>' . esc_html( $pages[ $active_key ]['label'] ) . '</strong> updated and published to the live site.' );
            } else {
                $notice = array( 'type' => 'error', 'msg' => '&#9888; Could not write file. Check server permissions.' );
            }
        }
    }

    // ── Render ───────────────────────────────────────────────────────────────
    ?>
    <div class="wrap ce-wrap">

    <?php coulomb_render_header( $active_key ? $pages[ $active_key ]['label'] : null ); ?>

    <?php if ( $notice ) : ?>
    <div class="notice notice-<?php echo $notice['type']; ?> is-dismissible ce-notice">
        <p><?php echo $notice['msg']; ?></p>
    </div>
    <?php endif; ?>

    <?php if ( ! $active_key ) : ?>
        <?php coulomb_render_dashboard( $pages ); ?>
    <?php else : ?>
        <?php
        $page_info = $pages[ $active_key ];
        $file_path = $plugin_dir . $page_info['file'];
        $html      = file_exists( $file_path ) ? file_get_contents( $file_path ) : '';
        $fields    = coulomb_parse_fields( $html );
        $grouped   = coulomb_group_fields( $fields );
        coulomb_render_editor( $active_key, $page_info, $grouped );
        ?>
    <?php endif; ?>

    </div><!-- .ce-wrap -->

    <?php coulomb_render_styles(); ?>
    <?php
}

// ── Render: Page Dashboard ───────────────────────────────────────────────────
function coulomb_render_dashboard( $pages ) {
    $sections = array(
        'Main'       => array( 'home' ),
        'Products'   => array( 'allproducts', 'seriesb', 'seriesdc', 'seriesr', 'seriesm', 'seriess' ),
        'Industries' => array( 'ci', 'def', 'mf' ),
        'Technology' => array( 'besscore', 'sodium', 'smartems' ),
        'Company'    => array( 'about', 'contact' ),
    );
    ?>
    <div class="ce-dashboard">
        <?php foreach ( $sections as $section_name => $keys ) : ?>
        <div class="ce-section">
            <h2 class="ce-section-title"><?php echo esc_html( $section_name ); ?></h2>
            <div class="ce-page-grid">
                <?php foreach ( $keys as $key ) :
                    if ( ! isset( $pages[ $key ] ) ) continue;
                    $p    = $pages[ $key ];
                    $url  = admin_url( 'admin.php?page=coulomb-copy-editor&edit=' . $key );
                    $live = home_url( str_replace( array( 'home', 'seriesb', 'seriesdc', 'seriesr', 'seriesm', 'seriess', 'allproducts', 'ci', 'def', 'mf', 'besscore', 'sodium', 'smartems', 'about', 'contact' ), array( '', '279v-series-c', '279v-series-dc', '48v-series-r', '48v-series-m', '12v-series-s', 'all-products', 'commercial-industrial', 'defense-government', 'motive-traction', 'bess-core-technology', 'sodium-ion-technology', 'smart-ems', 'about-us', 'contact' ), $key ) . '/' );
                    ?>
                    <div class="ce-page-card">
                        <div class="ce-page-icon"><?php echo $p['icon']; ?></div>
                        <div class="ce-page-info">
                            <div class="ce-page-name"><?php echo esc_html( $p['label'] ); ?></div>
                            <div class="ce-page-desc"><?php echo esc_html( $p['desc'] ); ?></div>
                        </div>
                        <div class="ce-page-actions">
                            <a href="<?php echo esc_url( $url ); ?>" class="button button-primary ce-btn-edit">Edit Content</a>
                            <a href="<?php echo esc_url( $live ); ?>" target="_blank" class="button ce-btn-view">View Live ↗</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}

// ── Render: Field Editor ─────────────────────────────────────────────────────
function coulomb_render_editor( $page_key, $page_info, $grouped ) {
    $back_url = admin_url( 'admin.php?page=coulomb-copy-editor' );
    $live_url = home_url( str_replace(
        array( 'home', 'seriesb', 'seriesdc', 'seriesr', 'seriesm', 'seriess', 'allproducts', 'ci', 'def', 'mf', 'besscore', 'sodium', 'smartems', 'about', 'contact' ),
        array( '', '279v-series-c', '279v-series-dc', '48v-series-r', '48v-series-m', '12v-series-s', 'all-products', 'commercial-industrial', 'defense-government', 'motive-traction', 'bess-core-technology', 'sodium-ion-technology', 'smart-ems', 'about-us', 'contact' ),
        $page_key
    ) . '/' );
    $field_count = array_sum( array_map( 'count', $grouped ) );
    ?>
    <div class="ce-editor-wrap">

        <div class="ce-editor-topbar">
            <a href="<?php echo esc_url( $back_url ); ?>" class="ce-back-link">← All Pages</a>
            <div class="ce-editor-page-title">
                <span class="ce-editor-icon"><?php echo $page_info['icon']; ?></span>
                <?php echo esc_html( $page_info['label'] ); ?>
                <span class="ce-field-count"><?php echo $field_count; ?> fields</span>
            </div>
            <a href="<?php echo esc_url( $live_url ); ?>" target="_blank" class="button ce-btn-preview">View Live Page ↗</a>
        </div>

        <div class="ce-tip">
            <strong>How to edit:</strong> Click into any field below and type your changes. When you're done, scroll to the bottom and click <strong>Publish Changes</strong>. Your changes will go live on the website immediately.
        </div>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=coulomb-copy-editor&edit=' . $page_key ) ); ?>" id="ce-form">
            <?php wp_nonce_field( 'coulomb_ce_save_' . $page_key ); ?>
            <input type="hidden" name="coulomb_ce_save" value="1">

            <?php if ( empty( $grouped ) ) : ?>
                <div class="ce-empty">No editable fields found on this page. The HTML file may need to be re-processed.</div>
            <?php else : ?>
                <?php foreach ( $grouped as $section => $fields ) : ?>
                <div class="ce-section-block">
                    <div class="ce-section-header">
                        <span class="ce-section-name"><?php echo esc_html( $section ); ?></span>
                        <span class="ce-section-count"><?php echo count( $fields ); ?> fields</span>
                    </div>
                    <div class="ce-fields-grid">
                        <?php foreach ( $fields as $ceid => $field ) :
                            // Clean up label — remove section prefix if it duplicates
                            $label = $field['label'];
                            $label = preg_replace( '/^' . preg_quote( $section, '/' ) . '\s*[—\-]\s*/i', '', $label );
                            $input_id = 'cefield_' . esc_attr( $ceid );
                            ?>
                            <div class="ce-field <?php echo $field['type'] === 'textarea' ? 'ce-field-wide' : ''; ?>">
                                <label class="ce-field-label" for="<?php echo $input_id; ?>">
                                    <?php echo esc_html( $label ); ?>
                                </label>
                                <?php if ( $field['type'] === 'textarea' ) : ?>
                                    <textarea
                                        id="<?php echo $input_id; ?>"
                                        name="<?php echo $input_id; ?>"
                                        class="ce-textarea"
                                        rows="3"
                                    ><?php echo esc_textarea( $field['text'] ); ?></textarea>
                                <?php else : ?>
                                    <input
                                        type="text"
                                        id="<?php echo $input_id; ?>"
                                        name="<?php echo $input_id; ?>"
                                        class="ce-input"
                                        value="<?php echo esc_attr( $field['text'] ); ?>"
                                    >
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="ce-publish-bar">
                <div class="ce-publish-info">
                    <strong><?php echo esc_html( $page_info['label'] ); ?></strong>
                    <span>— <?php echo $field_count; ?> editable fields</span>
                </div>
                <div class="ce-publish-actions">
                    <a href="<?php echo esc_url( $back_url ); ?>" class="button ce-btn-cancel">Cancel</a>
                    <button type="submit" class="button button-primary ce-btn-publish" id="ce-publish-btn">
                        ✓ Publish Changes to Live Site
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('ce-publish-btn').addEventListener('click', function(e) {
        if (!confirm('Publish all changes to the live site?\n\nThis will immediately update the page for all visitors.')) {
            e.preventDefault();
        }
    });
    // Auto-resize textareas
    document.querySelectorAll('.ce-textarea').forEach(function(ta) {
        ta.style.height = 'auto';
        ta.style.height = (ta.scrollHeight + 4) + 'px';
        ta.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight + 4) + 'px';
        });
    });
    </script>
    <?php
}

// ── Render: Header ───────────────────────────────────────────────────────────
function coulomb_render_header( $page_label = null ) {
    ?>
    <div class="ce-header">
        <div class="ce-header-brand">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00b050" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            <span>Coulomb Technology</span>
        </div>
        <div class="ce-header-title">
            <h1>Copy Editor<?php if ( $page_label ) echo ' <span class="ce-header-page">/ ' . esc_html( $page_label ) . '</span>'; ?></h1>
        </div>
        <div class="ce-header-sub">Edit website content and publish changes live — no coding required.</div>
    </div>
    <?php
}

// ── Styles ───────────────────────────────────────────────────────────────────
function coulomb_render_styles() {
    ?>
    <style>
    /* ── Reset & Base ── */
    .ce-wrap { max-width: 100%; padding: 0 !important; }
    .ce-wrap * { box-sizing: border-box; }

    /* ── Header ── */
    .ce-header {
        background: #0d1117;
        padding: 18px 28px 14px;
        margin: -10px -20px 28px;
        border-bottom: 3px solid #00b050;
    }
    .ce-header-brand {
        display: flex; align-items: center; gap: 7px;
        font-size: 12px; font-weight: 700; color: #00b050;
        letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px;
    }
    .ce-header h1 {
        color: #fff !important; font-size: 24px !important;
        margin: 0 0 4px !important; padding: 0 !important; line-height: 1.2 !important;
    }
    .ce-header-page { color: #888; font-weight: 400; }
    .ce-header-sub { color: #666; font-size: 13px; }

    /* ── Notice ── */
    .ce-notice { margin: 0 0 20px !important; }

    /* ── Dashboard ── */
    .ce-dashboard { padding: 0 4px; }
    .ce-section { margin-bottom: 32px; }
    .ce-section-title {
        font-size: 11px; font-weight: 700; color: #888;
        letter-spacing: 1.5px; text-transform: uppercase;
        margin: 0 0 12px; padding: 0; border: none;
    }
    .ce-page-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 12px;
    }
    .ce-page-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: box-shadow 0.15s, border-color 0.15s;
    }
    .ce-page-card:hover {
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border-color: #00b050;
    }
    .ce-page-icon { font-size: 26px; flex-shrink: 0; width: 40px; text-align: center; }
    .ce-page-info { flex: 1; min-width: 0; }
    .ce-page-name { font-weight: 600; font-size: 14px; color: #111; margin-bottom: 2px; }
    .ce-page-desc { font-size: 12px; color: #888; }
    .ce-page-actions { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }
    .ce-btn-edit {
        background: #00b050 !important; border-color: #009040 !important;
        color: #fff !important; font-size: 12px !important; white-space: nowrap;
        padding: 5px 12px !important; height: auto !important;
    }
    .ce-btn-edit:hover { background: #009040 !important; }
    .ce-btn-view { font-size: 12px !important; padding: 5px 10px !important; height: auto !important; white-space: nowrap; }

    /* ── Editor ── */
    .ce-editor-wrap { }
    .ce-editor-topbar {
        display: flex; align-items: center; gap: 16px;
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 8px; padding: 12px 18px; margin-bottom: 16px;
    }
    .ce-back-link { font-size: 13px; color: #555; text-decoration: none; white-space: nowrap; }
    .ce-back-link:hover { color: #00b050; }
    .ce-editor-page-title {
        flex: 1; font-size: 16px; font-weight: 600; color: #111;
        display: flex; align-items: center; gap: 8px;
    }
    .ce-editor-icon { font-size: 20px; }
    .ce-field-count {
        font-size: 12px; font-weight: 400; color: #888;
        background: #f3f4f6; padding: 2px 8px; border-radius: 20px;
    }
    .ce-btn-preview { font-size: 12px !important; padding: 6px 12px !important; height: auto !important; white-space: nowrap; }

    .ce-tip {
        background: #f0fdf4; border-left: 4px solid #00b050;
        padding: 11px 16px; font-size: 13px; color: #444;
        border-radius: 0 6px 6px 0; margin-bottom: 20px;
    }

    /* ── Section blocks ── */
    .ce-section-block {
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 10px; margin-bottom: 16px; overflow: hidden;
    }
    .ce-section-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 18px; background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .ce-section-name { font-size: 13px; font-weight: 700; color: #374151; }
    .ce-section-count { font-size: 11px; color: #9ca3af; }

    /* ── Fields grid ── */
    .ce-fields-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 0;
        padding: 4px 0;
    }
    .ce-field {
        padding: 12px 18px;
        border-bottom: 1px solid #f3f4f6;
        border-right: 1px solid #f3f4f6;
    }
    .ce-field:last-child { border-bottom: none; }
    .ce-field-wide {
        grid-column: 1 / -1;
    }
    .ce-field-label {
        display: block; font-size: 11px; font-weight: 600;
        color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .ce-input {
        width: 100% !important; padding: 7px 10px !important;
        border: 1px solid #d1d5db !important; border-radius: 6px !important;
        font-size: 14px !important; color: #111 !important;
        background: #fff !important; transition: border-color 0.15s !important;
        box-shadow: none !important;
    }
    .ce-input:focus {
        border-color: #00b050 !important;
        box-shadow: 0 0 0 3px rgba(0,176,80,0.12) !important;
        outline: none !important;
    }
    .ce-textarea {
        width: 100% !important; padding: 7px 10px !important;
        border: 1px solid #d1d5db !important; border-radius: 6px !important;
        font-size: 14px !important; color: #111 !important;
        background: #fff !important; transition: border-color 0.15s !important;
        box-shadow: none !important; resize: vertical !important;
        min-height: 60px; line-height: 1.5 !important;
        font-family: inherit !important;
    }
    .ce-textarea:focus {
        border-color: #00b050 !important;
        box-shadow: 0 0 0 3px rgba(0,176,80,0.12) !important;
        outline: none !important;
    }

    /* ── Publish bar ── */
    .ce-publish-bar {
        position: sticky; bottom: 0;
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 10px; padding: 14px 20px;
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 20px;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
        z-index: 100;
    }
    .ce-publish-info { font-size: 14px; color: #555; }
    .ce-publish-info strong { color: #111; }
    .ce-publish-actions { display: flex; gap: 10px; align-items: center; }
    .ce-btn-cancel { font-size: 13px !important; padding: 8px 16px !important; height: auto !important; }
    .ce-btn-publish {
        background: #00b050 !important; border-color: #009040 !important;
        color: #fff !important; font-size: 15px !important;
        padding: 10px 24px !important; height: auto !important;
        font-weight: 700 !important; letter-spacing: 0.2px !important;
    }
    .ce-btn-publish:hover { background: #009040 !important; }

    .ce-empty { padding: 40px; text-align: center; color: #888; font-size: 14px; }

    /* ── Responsive ── */
    @media (max-width: 782px) {
        .ce-header { margin: -10px -10px 20px; padding: 14px 16px 12px; }
        .ce-page-grid { grid-template-columns: 1fr; }
        .ce-page-card { flex-wrap: wrap; }
        .ce-page-actions { flex-direction: row; width: 100%; }
        .ce-editor-topbar { flex-wrap: wrap; }
        .ce-fields-grid { grid-template-columns: 1fr; }
        .ce-publish-bar { flex-direction: column; gap: 12px; text-align: center; }
        .ce-publish-actions { width: 100%; justify-content: center; }
    }
    </style>
    <?php
}
