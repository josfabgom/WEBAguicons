<?php
/**
 * Formularios (consulta por proyecto, brochure, contacto): se guardan en "Consultas" y se avisa por email.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_nopriv_agui_lead', 'agui_handle_lead');
add_action('wp_ajax_agui_lead', 'agui_handle_lead');

function agui_handle_lead(): void
{
    $in = wp_unslash($_POST);

    // Anti-spam: campo trampa, tiempo mínimo de llenado y límite por IP.
    if (!empty($in['website'])) {
        wp_send_json_success(['message' => 'ok']);
    }
    $t = (int) ($in['t'] ?? 0);
    if ($t && (time() - $t) < 2) {
        wp_send_json_error(['message' => 'Demasiado rápido'], 429);
    }
    $ip = md5($_SERVER['REMOTE_ADDR'] ?? 'x');
    $key = 'agui_rl_' . $ip;
    $count = (int) get_transient($key);
    if ($count >= 8) {
        wp_send_json_error(['message' => 'Demasiados envíos'], 429);
    }
    set_transient($key, $count + 1, HOUR_IN_SECONDS);

    $mode = in_array($in['mode'] ?? '', ['inquiry', 'brochure', 'contact'], true) ? $in['mode'] : 'inquiry';
    $nombre = sanitize_text_field($in['nombre'] ?? '');
    $email = sanitize_email($in['email'] ?? '');
    $tel = sanitize_text_field($in['telefono'] ?? '');
    $msg = sanitize_textarea_field($in['mensaje'] ?? '');
    $proyecto = sanitize_text_field($in['proyecto'] ?? '');
    $pagina = esc_url_raw($in['pagina'] ?? '');
    $line_id = (int) ($in['line_id'] ?? 0);

    if ($nombre === '' || !is_email($email) || ($mode === 'contact' && $msg === '')) {
        wp_send_json_error(['message' => 'Datos incompletos'], 400);
    }

    $tipos = ['inquiry' => 'Consulta por proyecto', 'brochure' => 'Descarga de brochure', 'contact' => 'Contacto'];
    $tipo = $tipos[$mode];

    $id = wp_insert_post([
        'post_type' => 'consulta',
        'post_status' => 'publish',
        'post_title' => sprintf('%s – %s%s', $tipo, $nombre, $proyecto ? ' (' . $proyecto . ')' : ''),
    ]);
    if ($id && !is_wp_error($id)) {
        foreach (['tipo' => $tipo, 'proyecto' => $proyecto, 'nombre' => $nombre, 'email' => $email, 'telefono' => $tel, 'mensaje' => $msg, 'pagina' => $pagina] as $k => $v) {
            update_post_meta($id, '_agui_' . $k, $v);
        }
    }

    // Aviso por email
    $to = agui_opt('email_notify') ?: get_option('admin_email');
    $body = "Nueva consulta recibida desde el sitio web:\n\n"
        . "Tipo: $tipo\n" . ($proyecto ? "Proyecto: $proyecto\n" : '')
        . "Nombre: $nombre\nEmail: $email\n" . ($tel ? "Teléfono: $tel\n" : '')
        . ($msg ? "\nMensaje:\n$msg\n" : '')
        . ($pagina ? "\nPágina: $pagina\n" : '');
    $headers = ['Reply-To: ' . $nombre . ' <' . $email . '>', 'Content-Type: text/plain; charset=UTF-8'];
    wp_mail($to, '[Aguicons web] ' . $tipo . ($proyecto ? ' - ' . $proyecto : ''), $body, $headers);

    $resp = ['message' => 'ok'];
    if ($mode === 'brochure') {
        $file = $line_id ? (int) get_post_meta($line_id, '_agui_brochure_id', true) : 0;
        if ($file) {
            $resp['download'] = wp_get_attachment_url($file);
        }
    }
    wp_send_json_success($resp);
}

/** Si el sitio no tiene correo configurado, usa el remitente del dominio. */
add_filter('wp_mail_from', function ($from) {
    if (strpos($from, 'wordpress@') === 0) {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        return 'no-reply@' . preg_replace('/^www\./', '', (string) $host);
    }
    return $from;
});
add_filter('wp_mail_from_name', fn($n) => $n === 'WordPress' ? 'Aguicons Web' : $n);
