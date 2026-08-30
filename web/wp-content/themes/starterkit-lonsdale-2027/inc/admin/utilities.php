<?php

add_action('admin_menu', function () {
    add_management_page(
        'Utilitaires',
        'Utilitaires',
        'manage_options',
        'theme-utilities',
        'theme_utilities_render_page'
    );
});

add_action('admin_init', function () {
    if (!isset($_POST['theme_utilities_action'])) return;
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');

    $action = sanitize_text_field($_POST['theme_utilities_action']);

    if ($action === 'export_db') {
        theme_utilities_export_db();
    } elseif ($action === 'import_db') {
        theme_utilities_import_db();
    } elseif ($action === 'export_media') {
        theme_utilities_export_media();
    } elseif ($action === 'analyze_media') {
        if (!wp_verify_nonce($_POST['_wpnonce_analyze_media'] ?? '', 'theme_utilities_analyze_media')) {
            wp_die('Nonce invalide.');
        }
        delete_transient('theme_utilities_media_analysis');
        wp_safe_redirect(admin_url('tools.php?page=theme-utilities&media_analysis=1&run_analysis=1'));
        exit;
    } elseif ($action === 'delete_media') {
        if (!wp_verify_nonce($_POST['_wpnonce_delete_media'] ?? '', 'theme_utilities_delete_media')) {
            wp_die('Nonce invalide.');
        }
        $att_id = (int) ($_POST['attachment_id'] ?? 0);
        if ($att_id > 0) {
            wp_delete_attachment($att_id, true);
        }
        wp_safe_redirect(admin_url('tools.php?page=theme-utilities&media_analysis=1&deleted=' . $att_id));
        exit;
    } elseif ($action === 'delete_all_unused_media') {
        if (!wp_verify_nonce($_POST['_wpnonce_delete_all_unused'] ?? '', 'theme_utilities_delete_all_unused')) {
            wp_die('Nonce invalide.');
        }
        $analysis = get_transient('theme_utilities_media_analysis');
        $deleted_count = 0;
        if (is_array($analysis)) {
            foreach ($analysis as $att_id => $data) {
                if (empty($data['locations'])) {
                    wp_delete_attachment((int) $att_id, true);
                    unset($analysis[$att_id]);
                    $deleted_count++;
                }
            }
            set_transient('theme_utilities_media_analysis', $analysis, 300);
        }
        wp_safe_redirect(admin_url('tools.php?page=theme-utilities&media_analysis=1&deleted_all=' . $deleted_count));
        exit;
    }
});

function theme_utilities_export_db(): void
{
    if (!wp_verify_nonce($_POST['_wpnonce_export_db'] ?? '', 'theme_utilities_export_db')) {
        wp_die('Nonce invalide.');
    }

    $mysqldump = trim(shell_exec('which mysqldump 2>/dev/null') ?: '');
    if (empty($mysqldump)) {
        wp_die('mysqldump n\'est pas disponible sur ce serveur.');
    }

    $host = escapeshellarg(DB_HOST);
    $user = escapeshellarg(DB_USER);
    $pass = DB_PASSWORD;
    $name = escapeshellarg(DB_NAME);

    $filename = DB_NAME . '_' . date('Y-m-d_H-i-s') . '.sql';

    $cmd = sprintf(
        '%s --host=%s --user=%s --password=%s %s 2>&1',
        escapeshellarg($mysqldump),
        $host,
        $user,
        escapeshellarg($pass),
        $name
    );

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    passthru($cmd);
    exit;
}

function theme_utilities_import_db(): void
{
    if (!wp_verify_nonce($_POST['_wpnonce_import_db'] ?? '', 'theme_utilities_import_db')) {
        wp_die('Nonce invalide.');
    }

    if (empty($_FILES['import_sql_file']['tmp_name']) || $_FILES['import_sql_file']['error'] !== UPLOAD_ERR_OK) {
        wp_die('Aucun fichier SQL valide envoyé.');
    }

    $tmp_file = $_FILES['import_sql_file']['tmp_name'];
    $filename = $_FILES['import_sql_file']['name'];

    if (pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
        wp_die('Le fichier doit avoir l\'extension .sql.');
    }

    $mysql = trim(shell_exec('which mysql 2>/dev/null') ?: '');
    if (empty($mysql)) {
        wp_die('mysql n\'est pas disponible sur ce serveur.');
    }

    $host = escapeshellarg(DB_HOST);
    $user = escapeshellarg(DB_USER);
    $pass = escapeshellarg(DB_PASSWORD);
    $name = escapeshellarg(DB_NAME);

    $cmd = sprintf(
        '%s --host=%s --user=%s --password=%s %s < %s 2>&1',
        escapeshellarg($mysql),
        $host,
        $user,
        $pass,
        $name,
        escapeshellarg($tmp_file)
    );

    $output = shell_exec($cmd);
    $success = empty($output) || stripos($output, 'error') === false;

    if ($success) {
        set_transient('theme_utilities_import_db_result', 'success', 30);
    } else {
        set_transient('theme_utilities_import_db_result', 'error:' . $output, 30);
    }

    wp_safe_redirect(admin_url('tools.php?page=theme-utilities&import_db=1'));
    exit;
}

function theme_utilities_export_media(): void
{
    if (!wp_verify_nonce($_POST['_wpnonce_export_media'] ?? '', 'theme_utilities_export_media')) {
        wp_die('Nonce invalide.');
    }

    if (!class_exists('ZipArchive')) {
        wp_die('L\'extension ZipArchive n\'est pas disponible sur ce serveur.');
    }

    $uploads_dir = wp_upload_dir()['basedir'];
    if (!is_dir($uploads_dir)) {
        wp_die('Le dossier uploads est introuvable.');
    }

    $year = sanitize_text_field($_POST['export_media_year'] ?? '');
    $target_dir = $uploads_dir;
    $suffix = '';

    if (!empty($year) && is_dir($uploads_dir . '/' . $year)) {
        $target_dir = $uploads_dir . '/' . $year;
        $suffix = '_' . $year;
    }

    $filename = 'uploads' . $suffix . '_' . date('Y-m-d_H-i-s') . '.zip';
    $tmp_file = tempnam(sys_get_temp_dir(), 'wp_media_export_') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($tmp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        wp_die('Impossible de créer l\'archive ZIP.');
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($target_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $real_path = $file->getRealPath();
            $relative_path = 'uploads/' . substr($real_path, strlen($uploads_dir) + 1);
            $zip->addFile($real_path, $relative_path);
        }
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tmp_file));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    readfile($tmp_file);
    unlink($tmp_file);
    exit;
}

function theme_utilities_analyze_media_batch(int $offset, int $batch_size): array
{
    global $wpdb;

    $attachments = $wpdb->get_results($wpdb->prepare(
        "SELECT ID, guid, post_title FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_status = 'inherit' ORDER BY ID DESC LIMIT %d OFFSET %d",
        $batch_size,
        $offset
    ), OBJECT_K);

    if (empty($attachments)) return [];

    $results = [];

    foreach ($attachments as $att_id => $att) {
        $url = $att->guid;
        $relative_url = preg_replace('#^https?://[^/]+#', '', $url);
        $locations = [];

        $as_thumbnail = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = %s",
            (string) $att_id
        ));
        foreach ($as_thumbnail as $post_id) {
            $locations[] = 'Image à la une de "' . get_the_title($post_id) . '" (ID ' . $post_id . ')';
        }

        $in_content = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_title, post_type FROM {$wpdb->posts}
             WHERE post_type != 'attachment'
             AND post_type != 'revision'
             AND (post_content LIKE %s OR post_content LIKE %s OR post_content LIKE %s)",
            '%wp-image-' . $att_id . '%',
            '%"id":' . $att_id . '%',
            '%' . $wpdb->esc_like($relative_url) . '%'
        ));
        foreach ($in_content as $post) {
            $locations[] = 'Contenu de "' . $post->post_title . '" (' . $post->post_type . ' ID ' . $post->ID . ')';
        }

        $in_meta = $wpdb->get_results($wpdb->prepare(
            "SELECT pm.post_id, pm.meta_key, p.post_title, p.post_type FROM {$wpdb->postmeta} pm
             LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key != '_wp_attachment_metadata'
             AND pm.meta_key != '_wp_attached_file'
             AND pm.meta_key != '_thumbnail_id'
             AND p.post_type != 'revision'
             AND (pm.meta_value = %s OR pm.meta_value LIKE %s OR pm.meta_value LIKE %s OR pm.meta_value LIKE %s)",
            (string) $att_id,
            '%' . $wpdb->esc_like('"' . $att_id . '"') . '%',
            '%' . $wpdb->esc_like($relative_url) . '%',
            '%wp-image-' . $att_id . '%'
        ));
        foreach ($in_meta as $meta) {
            $locations[] = 'Meta "' . $meta->meta_key . '" de "' . $meta->post_title . '" (' . $meta->post_type . ' ID ' . $meta->post_id . ')';
        }

        $in_options = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options}
             WHERE option_name NOT LIKE %s
             AND option_name NOT LIKE %s
             AND option_name NOT LIKE %s
             AND option_name NOT LIKE %s
             AND option_name NOT LIKE %s
             AND option_name NOT LIKE %s
             AND (option_value LIKE %s OR option_value LIKE %s)",
            '_transient%',
            '_site_transient%',
            '_icl_cache%',
            '%_cache%',
            'rewrite_rules',
            'cron',
            '%' . $wpdb->esc_like('"' . $att_id . '"') . '%',
            '%' . $wpdb->esc_like($relative_url) . '%'
        ));
        foreach ($in_options as $opt_name) {
            $locations[] = 'Option "' . $opt_name . '"';
        }

        $results[$att_id] = [
            'title' => $att->post_title,
            'url' => $url,
            'locations' => $locations,
        ];
    }

    return $results;
}

add_action('wp_ajax_theme_utilities_reset_analysis', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('Accès refusé.', 403);
    check_ajax_referer('theme_utilities_analyze_media', '_nonce');
    delete_option('_theme_utilities_media_analysis_progress');
    delete_transient('theme_utilities_media_analysis');
    wp_send_json_success();
});

add_action('wp_ajax_theme_utilities_analyze_batch', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('Accès refusé.', 403);
    check_ajax_referer('theme_utilities_analyze_media', '_nonce');

    global $wpdb;

    $offset = (int) ($_POST['offset'] ?? 0);
    $batch_size = 100;

    $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_status = 'inherit'");
    $batch_results = theme_utilities_analyze_media_batch($offset, $batch_size);

    $existing = get_option('_theme_utilities_media_analysis_progress', []);
    $existing = array_merge($existing, $batch_results);

    $next_offset = $offset + $batch_size;
    $done = $next_offset >= $total;

    if ($done) {
        set_transient('theme_utilities_media_analysis', $existing, 3600);
        delete_option('_theme_utilities_media_analysis_progress');
    } else {
        update_option('_theme_utilities_media_analysis_progress', $existing, false);
    }

    wp_send_json_success([
        'total' => $total,
        'processed' => min($next_offset, $total),
        'done' => $done,
    ]);
});


function theme_utilities_render_page(): void
{
    $mysqldump_available = !empty(trim(shell_exec('which mysqldump 2>/dev/null') ?: ''));
    $zip_available = class_exists('ZipArchive');
?>
    <div class="wrap">
        <h1>Utilitaires</h1>

        <div class="card" style="max-width: 600px; margin-top: 20px; padding: 20px;">
            <h2>Export base de données</h2>
            <p>Télécharger un dump SQL complet de la base de données.</p>
            <?php if ($mysqldump_available): ?>
                <form method="post">
                    <input type="hidden" name="theme_utilities_action" value="export_db">
                    <?php wp_nonce_field('theme_utilities_export_db', '_wpnonce_export_db'); ?>
                    <button type="submit" class="button button-primary">Télécharger la BDD (.sql)</button>
                </form>
            <?php else: ?>
                <p><strong style="color: #d63638;">mysqldump n'est pas disponible sur ce serveur.</strong></p>
            <?php endif; ?>
        </div>

        <div class="card" style="max-width: 600px; margin-top: 20px; padding: 20px;">
            <h2>Import base de données</h2>
            <p>Importer un fichier SQL dans la base de données. <strong style="color: #d63638;">Attention : cela écrasera les données existantes.</strong></p>
            <?php
            $mysql_available = !empty(trim(shell_exec('which mysql 2>/dev/null') ?: ''));
            $import_result = get_transient('theme_utilities_import_db_result');
            if ($import_result) {
                delete_transient('theme_utilities_import_db_result');
                if ($import_result === 'success') {
                    echo '<div class="notice notice-success inline" style="margin-bottom: 15px;"><p>Base de données importée avec succès.</p></div>';
                } else {
                    $error_msg = substr($import_result, 6);
                    echo '<div class="notice notice-error inline" style="margin-bottom: 15px;"><p>Erreur lors de l\'import : <code>' . esc_html($error_msg) . '</code></p></div>';
                }
            }
            ?>
            <?php if ($mysql_available): ?>
                <form method="post" enctype="multipart/form-data" onsubmit="return confirm('Cette action va écraser la base de données actuelle. Continuer ?');">
                    <input type="hidden" name="theme_utilities_action" value="import_db">
                    <?php wp_nonce_field('theme_utilities_import_db', '_wpnonce_import_db'); ?>
                    <p>
                        <input type="file" name="import_sql_file" accept=".sql" required>
                    </p>
                    <button type="submit" class="button button-primary">Importer la BDD (.sql)</button>
                </form>
            <?php else: ?>
                <p><strong style="color: #d63638;">mysql n'est pas disponible sur ce serveur.</strong></p>
            <?php endif; ?>
        </div>

        <div class="card" style="max-width: 600px; margin-top: 20px; padding: 20px;">
            <h2>Export des médias</h2>
            <p>Télécharger une archive ZIP du dossier <code>wp-content/uploads</code>.</p>
            <?php if ($zip_available): ?>
                <?php
                $uploads_base = wp_upload_dir()['basedir'];
                $years = [];
                if (is_dir($uploads_base)) {
                    foreach (scandir($uploads_base) as $entry) {
                        if ($entry === '.' || $entry === '..') continue;
                        if (is_dir($uploads_base . '/' . $entry) && preg_match('/^\d{4}$/', $entry)) {
                            $years[] = $entry;
                        }
                    }
                    sort($years);
                }
                ?>
                <form method="post">
                    <input type="hidden" name="theme_utilities_action" value="export_media">
                    <?php wp_nonce_field('theme_utilities_export_media', '_wpnonce_export_media'); ?>
                    <p>
                        <label for="export_media_year"><strong>Année :</strong></label>
                        <select name="export_media_year" id="export_media_year">
                            <option value="">Toutes (export complet)</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?php echo esc_attr($y); ?>"><?php echo esc_html($y); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <button type="submit" class="button button-primary">Exporter les médias (.zip)</button>
                </form>
            <?php else: ?>
                <p><strong style="color: #d63638;">L'extension ZipArchive n'est pas disponible sur ce serveur.</strong></p>
            <?php endif; ?>
        </div>

        <div class="card" style="max-width: 900px; margin-top: 20px; padding: 20px;">
            <h2>Analyse des médias</h2>
            <p>Analyser tous les médias : affiche où chacun est utilisé (contenu, champs ACF, options, données sérialisées). Les médias inutilisés peuvent être supprimés individuellement (fichier + thumbnails).</p>
            <button type="button" id="btn-analyze-media" class="button button-primary">Analyser les médias</button>
            <div id="analyze-progress" style="display:none; margin-top: 15px;">
                <div style="background: #f0f0f1; border-radius: 4px; overflow: hidden; height: 24px; position: relative;">
                    <div id="analyze-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
                    <span id="analyze-progress-text" style="position: absolute; top: 0; left: 0; right: 0; text-align: center; line-height: 24px; font-size: 12px; color: #1d2327;"></span>
                </div>
            </div>
            <script>
                (function() {
                    var btn = document.getElementById('btn-analyze-media');
                    var progress = document.getElementById('analyze-progress');
                    var bar = document.getElementById('analyze-progress-bar');
                    var text = document.getElementById('analyze-progress-text');
                    var nonce = '<?php echo wp_create_nonce('theme_utilities_analyze_media'); ?>';

                    btn.addEventListener('click', function() {
                        btn.disabled = true;
                        progress.style.display = 'block';
                        bar.style.width = '0%';
                        text.textContent = 'Initialisation...';

                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', ajaxurl);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            deleteProgress();
                            runBatch(0);
                        };
                        xhr.send('action=theme_utilities_reset_analysis&_nonce=' + nonce);
                    });

                    function deleteProgress() {}

                    function runBatch(offset) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', ajaxurl);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            var resp = JSON.parse(xhr.responseText);
                            if (!resp.success) {
                                text.textContent = 'Erreur : ' + (resp.data || 'inconnue');
                                btn.disabled = false;
                                return;
                            }
                            var d = resp.data;
                            var pct = Math.round((d.processed / d.total) * 100);
                            bar.style.width = pct + '%';
                            text.textContent = d.processed + ' / ' + d.total + ' médias analysés (' + pct + '%)';

                            if (d.done) {
                                window.location.href = '<?php echo admin_url('tools.php?page=theme-utilities&media_analysis=1'); ?>';
                            } else {
                                runBatch(d.processed);
                            }
                        };
                        xhr.onerror = function() {
                            text.textContent = 'Erreur réseau. Réessayez.';
                            btn.disabled = false;
                        };
                        xhr.send('action=theme_utilities_analyze_batch&_nonce=' + nonce + '&offset=' + offset);
                    }

                    <?php if (isset($_GET['run_analysis']) && $_GET['run_analysis'] === '1'): ?>
                        btn.click();
                    <?php endif; ?>
                })();
            </script>
            <?php
            if (isset($_GET['deleted']) && (int) $_GET['deleted'] > 0) {
                echo '<div class="notice notice-success inline" style="margin-top: 15px;"><p>Média #' . (int) $_GET['deleted'] . ' supprimé avec succès (fichier + thumbnails).</p></div>';
            }
            if (isset($_GET['deleted_all']) && (int) $_GET['deleted_all'] > 0) {
                echo '<div class="notice notice-success inline" style="margin-top: 15px;"><p>' . (int) $_GET['deleted_all'] . ' média(s) inutilisé(s) supprimé(s) avec succès.</p></div>';
            }

            if (isset($_GET['media_analysis']) && $_GET['media_analysis'] === '1') {
                $analysis = get_transient('theme_utilities_media_analysis');
                if ($analysis === false) {
                    echo '<p style="margin-top: 15px;"><em>Aucun résultat en cache. Relancez l\'analyse.</em></p>';
                } elseif (empty($analysis)) {
                    echo '<p style="margin-top: 15px;"><em>Aucun média trouvé.</em></p>';
                } else {
                    $total = count($analysis);
                    $used = count(array_filter($analysis, fn($d) => !empty($d['locations'])));
                    $unused_items = array_filter($analysis, fn($d) => empty($d['locations']));
                    $unused = count($unused_items);

                    $filter = sanitize_text_field($_GET['media_filter'] ?? 'all');
                    if ($filter === 'unused') {
                        $display_items = $unused_items;
                    } elseif ($filter === 'used') {
                        $display_items = array_filter($analysis, fn($d) => !empty($d['locations']));
                    } else {
                        $display_items = $analysis;
                    }
                    $display_total = count($display_items);

                    $per_page = 50;
                    $current_page = max(1, (int) ($_GET['paged'] ?? 1));
                    $total_pages = (int) ceil($display_total / $per_page);
                    $offset = ($current_page - 1) * $per_page;
                    $paged_items = array_slice($display_items, $offset, $per_page, true);

                    echo '<div class="notice notice-info inline" style="margin-top: 15px;"><p><strong>' . $total . ' médias analysés</strong> — ' . $used . ' utilisé(s), <span style="color: #d63638;">' . $unused . ' inutilisé(s)</span></p></div>';

                    $base_url = admin_url('tools.php?page=theme-utilities&media_analysis=1');
                    echo '<div style="margin-top: 10px;">';
                    echo '<a class="button' . ($filter === 'all' ? ' button-primary' : '') . '" href="' . esc_url($base_url . '&media_filter=all') . '">Tous (' . $total . ')</a> ';
                    echo '<a class="button' . ($filter === 'used' ? ' button-primary' : '') . '" href="' . esc_url($base_url . '&media_filter=used') . '">Utilisés (' . $used . ')</a> ';
                    echo '<a class="button' . ($filter === 'unused' ? ' button-primary' : '') . '" href="' . esc_url($base_url . '&media_filter=unused') . '">Inutilisés (' . $unused . ')</a>';
                    echo '</div>';

                    if ($unused > 0) {
                        echo '<form method="post" style="margin-top: 10px;" onsubmit="return confirm(\'Supprimer définitivement les ' . $unused . ' média(s) inutilisé(s) et leurs thumbnails ?\');">';
                        echo '<input type="hidden" name="theme_utilities_action" value="delete_all_unused_media">';
                        wp_nonce_field('theme_utilities_delete_all_unused', '_wpnonce_delete_all_unused');
                        echo '<button type="submit" class="button button-link-delete" style="font-size: 13px;">Supprimer tous les médias inutilisés (' . $unused . ')</button>';
                        echo '</form>';
                    }

                    if ($display_total > 0) {
                        if ($total_pages > 1) {
                            echo '<div class="tablenav" style="margin-top: 10px;"><div class="tablenav-pages">';
                            echo '<span class="displaying-num">' . $display_total . ' éléments</span>';
                            echo '<span class="pagination-links">';
                            $paged_base = $base_url . '&media_filter=' . $filter;
                            if ($current_page > 1) {
                                echo '<a class="button" href="' . esc_url($paged_base . '&paged=1') . '">&laquo;</a> ';
                                echo '<a class="button" href="' . esc_url($paged_base . '&paged=' . ($current_page - 1)) . '">&lsaquo;</a> ';
                            }
                            echo '<span class="paging-input">' . $current_page . ' / ' . $total_pages . '</span>';
                            if ($current_page < $total_pages) {
                                echo ' <a class="button" href="' . esc_url($paged_base . '&paged=' . ($current_page + 1)) . '">&rsaquo;</a>';
                                echo ' <a class="button" href="' . esc_url($paged_base . '&paged=' . $total_pages) . '">&raquo;</a>';
                            }
                            echo '</span></div></div>';
                        }

                        echo '<table class="widefat striped" style="margin-top: 10px;">';
                        echo '<thead><tr><th style="width:50px">ID</th><th style="width:70px">Aperçu</th><th>Fichier</th><th>Utilisé dans</th><th style="width:100px">Action</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($paged_items as $att_id => $data) {
                            $thumb = wp_get_attachment_image((int) $att_id, [50, 50]);
                            $basename = basename($data['url']);
                            $is_used = !empty($data['locations']);
                            echo '<tr>';
                            echo '<td>' . (int) $att_id . '</td>';
                            echo '<td>' . ($thumb ?: '—') . '</td>';
                            echo '<td><code>' . esc_html($basename) . '</code></td>';
                            echo '<td>';
                            if ($is_used) {
                                echo '<ul style="margin: 0; padding-left: 16px;">';
                                foreach ($data['locations'] as $location) {
                                    echo '<li style="margin-bottom: 2px; font-size: 12px;">' . esc_html($location) . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo '<span style="color: #d63638;">Inutilisé</span>';
                            }
                            echo '</td>';
                            echo '<td>';
                            if (!$is_used) {
                                echo '<form method="post" style="display:inline" onsubmit="return confirm(\'Supprimer définitivement ce média et ses thumbnails ?\');">';
                                echo '<input type="hidden" name="theme_utilities_action" value="delete_media">';
                                echo '<input type="hidden" name="attachment_id" value="' . (int) $att_id . '">';
                                wp_nonce_field('theme_utilities_delete_media', '_wpnonce_delete_media');
                                echo '<button type="submit" class="button button-link-delete">Supprimer</button>';
                                echo '</form>';
                            } else {
                                echo '—';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';

                        if ($total_pages > 1) {
                            echo '<div class="tablenav bottom"><div class="tablenav-pages">';
                            echo '<span class="pagination-links">';
                            if ($current_page > 1) {
                                echo '<a class="button" href="' . esc_url($paged_base . '&paged=1') . '">&laquo;</a> ';
                                echo '<a class="button" href="' . esc_url($paged_base . '&paged=' . ($current_page - 1)) . '">&lsaquo;</a> ';
                            }
                            echo '<span class="paging-input">' . $current_page . ' / ' . $total_pages . '</span>';
                            if ($current_page < $total_pages) {
                                echo ' <a class="button" href="' . esc_url($paged_base . '&paged=' . ($current_page + 1)) . '">&rsaquo;</a>';
                                echo ' <a class="button" href="' . esc_url($paged_base . '&paged=' . $total_pages) . '">&raquo;</a>';
                            }
                            echo '</span></div></div>';
                        }
                    } else {
                        echo '<p style="margin-top: 15px;"><em>Aucun média dans cette catégorie.</em></p>';
                    }
                }
            }
            ?>
        </div>
    </div>
<?php
}
