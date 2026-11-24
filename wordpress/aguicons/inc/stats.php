<?php
/**
 * Estadísticas de visitas propias (sin Google, sin cookies, datos anónimos).
 * Tabla {prefix}agui_hits: una fila por visita o evento. No se guarda la IP: solo un código diario
 * irreversible (hash con sal del sitio) que permite contar visitantes únicos dentro del mismo día.
 */

if (!defined('ABSPATH')) {
    exit;
}

const AGUI_STATS_DB = 1;

function agui_stats_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'agui_hits';
}

add_action('init', function () {
    if ((int) get_option('agui_stats_db', 0) === AGUI_STATS_DB) {
        return;
    }
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $t = agui_stats_table();
    dbDelta("CREATE TABLE $t (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        day date NOT NULL,
        visitor char(16) NOT NULL,
        ev varchar(12) NOT NULL,
        path varchar(190) NOT NULL DEFAULT '',
        src varchar(40) NOT NULL DEFAULT '',
        device varchar(12) NOT NULL DEFAULT '',
        PRIMARY KEY  (id),
        KEY day_ev (day, ev),
        KEY path (path)
    ) {$wpdb->get_charset_collate()};");
    update_option('agui_stats_db', AGUI_STATS_DB, false);
});

function agui_is_bot(string $ua): bool
{
    return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|headless|lighthouse|pingdom|uptime|curl|wget|python|monitor|scan/i', $ua);
}

function agui_visitor_code(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return substr(hash('sha256', $ip . '|' . $ua . '|' . gmdate('Y-m-d') . '|' . wp_salt('auth')), 0, 16);
}

function agui_traffic_source(string $ref): string
{
    $host = strtolower((string) wp_parse_url($ref, PHP_URL_HOST));
    if ($host === '') {
        return 'Directo';
    }
    $host = preg_replace('/^(www|m|l|lm)\./', '', $host);
    $mine = preg_replace('/^www\./', '', strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST)));
    if ($host === $mine) {
        return 'Interno';
    }
    $map = ['google.' => 'Google', 'bing.' => 'Bing', 'duckduckgo.' => 'DuckDuckGo', 'instagram.' => 'Instagram', 'facebook.' => 'Facebook', 'fb.' => 'Facebook',
        'wa.me' => 'WhatsApp', 'whatsapp.' => 'WhatsApp', 'youtube.' => 'YouTube', 'linkedin.' => 'LinkedIn', 'tiktok.' => 'TikTok', 't.co' => 'X / Twitter', 'twitter.' => 'X / Twitter'];
    foreach ($map as $k => $label) {
        if (strpos($host, $k) !== false) {
            return $label;
        }
    }
    return mb_substr($host, 0, 40);
}

function agui_stats_record(string $ev, string $path = '', string $ref = ''): void
{
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (agui_is_bot($ua) || current_user_can('edit_posts')) {
        return;
    }
    global $wpdb;
    $path = '/' . ltrim((string) wp_parse_url($path, PHP_URL_PATH), '/');
    $device = preg_match('/Mobi|Android|iPhone|iPad/i', $ua) ? 'Celular' : 'Computadora';
    $wpdb->insert(agui_stats_table(), [
        'day' => current_time('Y-m-d'),
        'visitor' => agui_visitor_code(),
        'ev' => $ev,
        'path' => mb_substr($path, 0, 190),
        'src' => $ev === 'view' ? agui_traffic_source($ref) : '',
        'device' => $device,
    ]);
    if (mt_rand(1, 500) === 1) { // limpieza ocasional: se conservan 2 años
        $wpdb->query($wpdb->prepare('DELETE FROM ' . agui_stats_table() . ' WHERE day < %s', gmdate('Y-m-d', strtotime('-730 days'))));
    }
}

/** Registro desde el navegador (visita, clic en WhatsApp, descarga). */
function agui_ajax_hit(): void
{
    $ev = sanitize_key($_POST['ev'] ?? 'view');
    if (in_array($ev, ['view', 'whatsapp', 'download'], true)) {
        agui_stats_record($ev, (string) wp_unslash($_POST['path'] ?? '/'), (string) wp_unslash($_POST['ref'] ?? ''));
    }
    wp_send_json_success();
}
add_action('wp_ajax_nopriv_agui_hit', 'agui_ajax_hit');
add_action('wp_ajax_agui_hit', 'agui_ajax_hit');

/** Cada consulta enviada se cuenta desde el servidor. */
function agui_stats_lead(): void
{
    agui_stats_record('lead', (string) wp_unslash($_POST['pagina'] ?? '/'));
}

/* ---------------------------------------------------------------------------------------------
 * Pantalla "Visitas" en el panel
 * ------------------------------------------------------------------------------------------- */

/**
 * Rol "Estadísticas": una persona que solo puede ver Visitas (no edita nada ni ve consultas).
 * Se crea desde Usuarios → Añadir nuevo, eligiendo el rol "Estadísticas (solo visitas)".
 */
add_action('init', function () {
    if (get_option('agui_stats_role') === '1') {
        return;
    }
    add_role('agui_estadisticas', 'Estadísticas (solo visitas)', ['read' => true, 'agui_view_stats' => true]);
    get_role('administrator')->add_cap('agui_view_stats');
    update_option('agui_stats_role', '1', false);
});

add_action('admin_menu', function () {
    if (current_user_can('manage_options')) {
        add_submenu_page(AGUI_PANEL, 'Visitas', 'Visitas', 'agui_view_stats', 'aguicons-stats', 'agui_stats_page');
    } elseif (current_user_can('agui_view_stats')) {
        add_menu_page('Visitas', 'Visitas', 'agui_view_stats', 'aguicons-stats', 'agui_stats_page', 'dashicons-chart-bar', 3);
    }
}, 22);

/** Quien solo ve estadísticas entra directo a esa pantalla y no ve el escritorio ni el perfil de otros. */
add_filter('login_redirect', function ($to, $req, $user) {
    if ($user instanceof WP_User && !user_can($user, 'manage_options') && user_can($user, 'agui_view_stats')) {
        return admin_url('admin.php?page=aguicons-stats');
    }
    return $to;
}, 10, 3);

add_action('admin_init', function () {
    if (current_user_can('manage_options') || !current_user_can('agui_view_stats') || wp_doing_ajax()) {
        return;
    }
    global $pagenow;
    if ($pagenow === 'index.php') {
        wp_safe_redirect(admin_url('admin.php?page=aguicons-stats'));
        exit;
    }
});

add_filter('show_admin_bar', function ($show) {
    return current_user_can('manage_options') || !current_user_can('agui_view_stats') ? $show : false;
});

function agui_stats_page(): void
{
    global $wpdb;
    $t = agui_stats_table();
    $days = (int) ($_GET['dias'] ?? 30);
    if (!in_array($days, [7, 30, 90, 365], true)) {
        $days = 30;
    }
    $now = current_time('timestamp');
    $from = gmdate('Y-m-d', $now - ($days - 1) * DAY_IN_SECONDS);

    $tot = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) v, COUNT(DISTINCT CONCAT(day, visitor)) u FROM $t WHERE ev='view' AND day >= %s", $from));
    $count = fn($ev) => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t WHERE ev=%s AND day >= %s", $ev, $from));
    $per_day = $wpdb->get_results($wpdb->prepare("SELECT day, COUNT(*) v FROM $t WHERE ev='view' AND day >= %s GROUP BY day", $from), OBJECT_K);
    $pages = $wpdb->get_results($wpdb->prepare("SELECT path, COUNT(*) v FROM $t WHERE ev='view' AND day >= %s GROUP BY path ORDER BY v DESC LIMIT 15", $from));
    $srcs = $wpdb->get_results($wpdb->prepare("SELECT src label, COUNT(*) v FROM $t WHERE ev='view' AND day >= %s AND src <> 'Interno' GROUP BY src ORDER BY v DESC LIMIT 10", $from));
    $devs = $wpdb->get_results($wpdb->prepare("SELECT device label, COUNT(*) v FROM $t WHERE ev='view' AND day >= %s GROUP BY device ORDER BY v DESC", $from));
    $contact = $wpdb->get_results($wpdb->prepare("SELECT path, COUNT(*) v FROM $t WHERE ev IN ('whatsapp','lead') AND day >= %s GROUP BY path ORDER BY v DESC LIMIT 8", $from));

    $series = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = gmdate('Y-m-d', $now - $i * DAY_IN_SECONDS);
        $series[$d] = isset($per_day[$d]) ? (int) $per_day[$d]->v : 0;
    }
    $max = max(1, max($series));

    $title = function (string $path): string {
        if ($path === '/') {
            return 'Portada';
        }
        $id = url_to_postid(home_url($path));
        return $id ? get_the_title($id) . ' — ' . $path : $path;
    };
    $table = function (array $rows, string $col, callable $fmt, string $head) {
        echo '<table class="widefat striped"><thead><tr><th>' . esc_html($head) . '</th><th style="width:90px">Cantidad</th></tr></thead><tbody>';
        if (!$rows) {
            echo '<tr><td colspan="2">Todavía no hay datos.</td></tr>';
        }
        foreach ($rows as $r) {
            echo '<tr><td>' . esc_html($fmt($r->$col)) . '</td><td>' . (int) $r->v . '</td></tr>';
        }
        echo '</tbody></table>';
    };
    ?>
    <div class="wrap agui-wrap">
        <h1>Visitas del sitio</h1>
        <p>Contador propio de Aguicons: no usa Google, no instala cookies y no guarda la IP de nadie. No cuenta a quienes están logueados en el panel ni a los robots conocidos.</p>
        <p>
            <?php foreach ([7 => '7 días', 30 => '30 días', 90 => '90 días', 365 => '1 año'] as $d => $l) : ?>
                <a class="button <?php echo $d === $days ? 'button-primary' : ''; ?>" href="<?php echo esc_url(add_query_arg(['page' => 'aguicons-stats', 'dias' => $d], admin_url('admin.php'))); ?>"><?php echo esc_html($l); ?></a>
            <?php endforeach; ?>
        </p>
        <style>
            .agui-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin:16px 0}
            .agui-kpi{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px 16px}
            .agui-kpi b{display:block;font-size:28px;line-height:1.1}.agui-kpi span{color:#50575e;font-size:13px}
            .agui-bars{display:flex;align-items:flex-end;gap:2px;height:140px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:12px}
            .agui-bars i{flex:1;background:#b08d3c;min-width:2px;border-radius:2px 2px 0 0}
            .agui-cols{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-top:16px}
        </style>
        <div class="agui-kpis">
            <div class="agui-kpi"><b><?php echo (int) $tot->v; ?></b><span>Visitas a páginas</span></div>
            <div class="agui-kpi"><b><?php echo (int) $tot->u; ?></b><span>Visitantes (por día)</span></div>
            <div class="agui-kpi"><b><?php echo $count('whatsapp'); ?></b><span>Clics en WhatsApp</span></div>
            <div class="agui-kpi"><b><?php echo $count('lead'); ?></b><span>Consultas enviadas</span></div>
            <div class="agui-kpi"><b><?php echo $count('download'); ?></b><span>Descargas</span></div>
        </div>
        <h2>Visitas por día</h2>
        <div class="agui-bars" role="img" aria-label="Visitas por día">
            <?php foreach ($series as $d => $v) : ?>
                <i style="height:<?php echo max(2, round($v / $max * 100)); ?>%" title="<?php echo esc_attr(wp_date('j/n', strtotime($d)) . ': ' . $v); ?>"></i>
            <?php endforeach; ?>
        </div>
        <div class="agui-cols">
            <div><h2>Páginas más vistas</h2><?php $table($pages, 'path', $title, 'Página'); ?></div>
            <div><h2>De dónde llegan</h2><?php $table($srcs, 'label', fn($x) => $x, 'Origen'); ?>
                <h2>Dispositivo</h2><?php $table($devs, 'label', fn($x) => $x, 'Tipo'); ?></div>
            <div><h2>Páginas que generan contacto</h2><p class="description">Clics en WhatsApp y consultas enviadas, por página.</p><?php $table($contact, 'path', $title, 'Página'); ?></div>
        </div>
        <p class="description" style="margin-top:16px">“Visitantes (por día)” cuenta cada persona una vez por día; si vuelve otro día, cuenta de nuevo. Los números pueden diferir de Google Analytics.</p>
    </div>
    <?php
}
