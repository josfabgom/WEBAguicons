<?php
/**
 * Página de ajustes del tema: Ajustes → Aguicons.
 * Todo lo "global" del sitio (portada, contacto, WhatsApp, estadísticas, analytics) se edita acá.
 */

if (!defined('ABSPATH')) {
    exit;
}

function agui_defaults(): array
{
    return [
        'whatsapp' => '5493765009075',
        'email_notify' => 'info@aguicons.com',
        'email_contact' => 'info@aguicons.com',
        'phone' => '+54 9 3765 009075',
        'address' => 'Av. Antartida Argentina 876',
        'map_query' => 'Av. Antártida Argentina 876, Posadas, Misiones, Argentina',
        'hero_image' => 0,
        'hero_baked' => '',
        'hero_logo' => '',
        'hero_before' => 'CONSTRUIMOS',
        'hero_accent' => 'tu',
        'hero_after' => 'FUTURO',
        'hero_button' => '',
        'hero_url' => '/nosotros/',
        'lines_title' => 'NUESTRAS LÍNEAS EDILICIAS',
        'stat1_value' => '+5.000', 'stat1_label' => 'Propietarios',
        'stat2_value' => '+5.000', 'stat2_label' => 'Inversores',
        'stat3_value' => '+500', 'stat3_label' => 'Unidades entregadas',
        'copyright' => 'Copyright © 2026 Aguicons',
        'powered' => '',
        'ga_id' => '',
    ];
}

/** Lee un ajuste del tema (con valor por defecto). get_option ya está en caché de WordPress. */
function agui_opt(string $key, $fallback = '')
{
    $saved = get_option('aguicons_opts', []);
    $opts = array_merge(agui_defaults(), is_array($saved) ? array_filter($saved, fn($v, $k) => in_array($k, ['whatsapp', 'ga_id'], true) || ($v !== '' && $v !== null && $v !== 0), ARRAY_FILTER_USE_BOTH) : []);
    return $opts[$key] ?? $fallback;
}

function agui_option_fields(): array
{
    return [
        'Contacto' => [
            'whatsapp' => ['Número de WhatsApp', 'text', 'Solo números con código de país. Ej: 5493765009075. Vacío = se oculta el botón flotante.'],
            'email_notify' => ['Email que recibe las consultas', 'text', 'A esta dirección llegan los avisos de formularios y descargas de brochure.'],
            'email_contact' => ['Email público', 'text', 'Se muestra en la barra de contacto.'],
            'phone' => ['Teléfono público', 'text', ''],
            'address' => ['Dirección', 'text', ''],
            'map_query' => ['Dirección para el mapa', 'text', 'Se usa en el mapa de Google de la página de contacto.'],
        ],
        'Portada (Inicio)' => [
            'hero_image' => ['Imagen de portada', 'image', 'Foto grande de la cabecera del inicio.'],
            'hero_baked' => ['La imagen ya incluye el título', 'check', 'Tildar si la foto de portada ya trae dibujado el título ("Construimos tu futuro"). Se muestra la foto completa sin repetir el título; el logo y el botón se mantienen.'],
            'hero_logo' => ['Mostrar el nombre AGUICONS sobre la portada', 'check', 'Tildar para mostrar el logo (solo el nombre) arriba de la foto. Por defecto no se muestra.'],
            'hero_before' => ['Título: parte 1', 'text', ''],
            'hero_accent' => ['Título: palabra en cursiva', 'text', ''],
            'hero_after' => ['Título: parte 2', 'text', ''],
            'hero_button' => ['Texto del botón (opcional)', 'text', 'Vacío = la portada no muestra botón.'],
            'hero_url' => ['Enlace del botón', 'text', 'Ej: /nosotros/'],
            'lines_title' => ['Título del carrusel de líneas', 'text', ''],
        ],
        'Estadísticas (Inicio)' => [
            'stat1_icon' => ['Dato 1: ícono (opcional)', 'image', 'Si no se carga, se usa el ícono de la llave.'],
            'stat1_value' => ['Dato 1: valor', 'text', 'Ej: +5.000 (se anima al aparecer en pantalla)'],
            'stat1_label' => ['Dato 1: etiqueta', 'text', ''],
            'stat2_icon' => ['Dato 2: ícono (opcional)', 'image', 'Si no se carga, se usa el ícono del inversor.'],
            'stat2_value' => ['Dato 2: valor', 'text', ''],
            'stat2_label' => ['Dato 2: etiqueta', 'text', ''],
            'stat3_icon' => ['Dato 3: ícono (opcional)', 'image', 'Si no se carga, se usa el ícono del edificio.'],
            'stat3_value' => ['Dato 3: valor', 'text', ''],
            'stat3_label' => ['Dato 3: etiqueta', 'text', ''],
        ],
        'Pie de página' => [
            'copyright' => ['Copyright', 'text', ''],
            'powered' => ['Crédito (opcional)', 'text', 'Texto extra junto al copyright. Vacío = no se muestra.'],
        ],
        'Analytics' => [
            'ga_id' => ['ID de Google Analytics 4', 'text', 'Ej: G-XXXXXXXXXX. Solo se carga si el visitante acepta las cookies. Vacío = sin analytics ni aviso de cookies.'],
        ],
    ];
}

add_action('admin_menu', function () {
    add_options_page('Aguicons', 'Aguicons', 'manage_options', 'aguicons', 'agui_render_options_page');
});

add_action('admin_init', function () {
    register_setting('aguicons', 'aguicons_opts', [
        'type' => 'array',
        'sanitize_callback' => function ($in) {
            // Se parte de lo ya guardado y solo se actualizan las claves recibidas: así guardar una pantalla
            // (por ejemplo "Estructura del sitio") no borra los ajustes que esa pantalla no muestra.
            $old = get_option('aguicons_opts', []);
            $out = is_array($old) ? $old : [];
            $in = is_array($in) ? $in : [];
            foreach (agui_option_fields() as $fields) {
                foreach ($fields as $key => [$label, $type]) {
                    if (!array_key_exists($key, $in)) {
                        continue;
                    }
                    $v = $in[$key];
                    if ($key === 'whatsapp') {
                        $v = preg_replace('/\D+/', '', (string) $v);
                    } elseif ($type === 'image') {
                        $v = (int) $v;
                    } elseif ($type === 'check') {
                        $v = !empty($v) ? '1' : '';
                    } else {
                        $v = sanitize_text_field((string) $v);
                    }
                    $out[$key] = $v;
                }
            }
            return $out;
        },
    ]);
});

function agui_render_options_page(): void
{
    ?>
    <div class="wrap">
        <h1>Aguicons – Ajustes del sitio</h1>
        <p>Acá se editan los datos que aparecen en todo el sitio. Los textos y fotos de cada línea están en el menú <strong>Líneas edilicias</strong>; los de las demás páginas en <strong>Páginas</strong>.</p>
        <form method="post" action="options.php">
            <?php settings_fields('aguicons'); ?>
            <?php foreach (agui_option_fields() as $section => $fields) : ?>
                <h2><?php echo esc_html($section); ?></h2>
                <table class="form-table" role="presentation">
                    <?php foreach ($fields as $key => [$label, $type, $help]) :
                        $val = agui_opt($key);
                        ?>
                        <tr>
                            <th scope="row"><label for="agui_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                            <td>
                                <?php if ($type === 'image') : ?>
                                    <div class="agui-media-field" data-multiple="0">
                                        <input type="hidden" id="agui_<?php echo esc_attr($key); ?>" name="aguicons_opts[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) $val); ?>">
                                        <div class="agui-media-preview"><?php echo $val ? wp_get_attachment_image((int) $val, 'medium') : ''; ?></div>
                                        <button type="button" class="button agui-media-pick">Elegir imagen</button>
                                        <button type="button" class="button-link agui-media-clear">Quitar</button>
                                    </div>
                                <?php elseif ($type === 'check') : ?>
                                    <input type="hidden" name="aguicons_opts[<?php echo esc_attr($key); ?>]" value="">
                                    <label><input type="checkbox" id="agui_<?php echo esc_attr($key); ?>" name="aguicons_opts[<?php echo esc_attr($key); ?>]" value="1" <?php checked((string) $val, '1'); ?>> Sí</label>
                                <?php else : ?>
                                    <input type="text" id="agui_<?php echo esc_attr($key); ?>" name="aguicons_opts[<?php echo esc_attr($key); ?>]" class="regular-text" value="<?php echo esc_attr((string) $val); ?>">
                                <?php endif; ?>
                                <?php if ($help) : ?><p class="description"><?php echo esc_html($help); ?></p><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/** Scripts del administrador (selector de imágenes, galería). */
add_action('admin_enqueue_scripts', function ($hook) {
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('aguicons-admin', get_theme_file_uri('assets/js/admin.js'), ['jquery', 'jquery-ui-sortable'], AGUI_VERSION, true);
    wp_localize_script('aguicons-admin', 'AGUI_ADMIN', [
        'ajax' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('agui_import'),
    ]);
    wp_enqueue_style('aguicons-admin', get_theme_file_uri('assets/css/admin.css'), [], AGUI_VERSION);
});
