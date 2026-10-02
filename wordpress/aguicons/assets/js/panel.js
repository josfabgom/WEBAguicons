/* Panel de gestión de Aguicons: asistente de líneas y pantalla de estructura. */
jQuery(function ($) {
  'use strict';
  var cfg = window.AGUI_PANEL || {};

  /* ---------- Asistente ---------- */
  var $w = $('#agui-wizard');
  if ($w.length) {
    var step = 1, total = 5;
    var $msg = $('#agui-msg');

    function show(n) {
      step = n;
      $('.agui-step').attr('hidden', true).filter('[data-step="' + n + '"]').removeAttr('hidden');
      $('.agui-steps button').removeClass('is-current').filter('[data-goto="' + n + '"]').addClass('is-current');
      $('#agui-prev').prop('hidden', n === 1);
      $('#agui-next').prop('hidden', n === total);
      window.scrollTo(0, 0);
    }

    function flash(text, bad) {
      $msg.text(text).css('color', bad ? '#b32d2e' : '#1a7f37');
      clearTimeout(flash.t);
      flash.t = setTimeout(function () { $msg.text(''); }, 4000);
    }

    function renderCheck(res) {
      var html = '<p><strong>' + res.score + '% completo.</strong> Estado: ' + (res.status === 'publish' ? '<span class="agui-pill ok">Publicada</span>' : '<span class="agui-pill off">Borrador</span>') + '</p>';
      if (res.missing.length) {
        html += '<p>Todavía falta:</p><ul>' + res.missing.map(function (m) { return '<li>' + m + '</li>'; }).join('') + '</ul><p class="description">Podés publicar igual y completarlo después.</p>';
      } else {
        html += '<p>✔ Todo listo.</p>';
      }
      $('#agui-check').html(html);
      $('#agui-view').attr('href', res.permalink).prop('hidden', false);
    }

    function save(status, keep, quiet) {
      var title = $.trim($w.find('[name="w[title]"]').val());
      if (!title) { show(1); flash('Escribí el nombre de la línea.', true); $w.find('[name="w[title]"]').focus(); return $.Deferred().reject().promise(); }
      $w.find('[name="w[avance]"]').trigger('change');
      var data = $w.serialize() + '&action=agui_wizard_save&nonce=' + encodeURIComponent(cfg.nonce) + '&status=' + status + (keep ? '&keep=1' : '');
      if (!quiet) flash('Guardando…');
      return $.post(cfg.ajax, data).done(function (r) {
        if (!r || !r.success) { flash((r && r.data) || 'No se pudo guardar.', true); return; }
        var res = r.data;
        $w.find('[name="w[id]"]').val(res.id);
        if (window.history && history.replaceState) {
          history.replaceState(null, '', 'admin.php?page=aguicons-wizard&line=' + res.id);
        }
        renderCheck(res);
        flash(status === 'publish' ? '✔ Publicada' : '✔ Guardado');
        $('[data-save="publish"]').text('Guardar cambios');
      }).fail(function () { flash('Error de conexión.', true); });
    }

    $('.agui-steps').on('click', 'button', function () {
      var n = parseInt($(this).data('goto'), 10);
      if (n > 1 && !$.trim($w.find('[name="w[title]"]').val())) { flash('Primero escribí el nombre (paso 1).', true); show(1); return; }
      show(n);
    });
    $('#agui-next').on('click', function () {
      if (!$.trim($w.find('[name="w[title]"]').val())) { flash('Escribí el nombre de la línea.', true); return; }
      save('draft', true, true).always(function () { show(Math.min(total, step + 1)); });
    });
    $('#agui-prev').on('click', function () { show(Math.max(1, step - 1)); });
    $w.on('click', '[data-save]', function () { save($(this).data('save'), $(this).data('save') === 'draft'); });
    $w.on('keydown', 'input[type=text], input[type=number], input[type=url]', function (e) { if (e.key === 'Enter') e.preventDefault(); });
    show(1);
  }

  /* ---------- Estructura del sitio ---------- */
  $('#agui-lines-order, #agui-menu-rows').sortable({ handle: '.handle', axis: 'y' });
  $('#agui-menu-add').on('click', function () {
    var tpl = document.getElementById('agui-menu-tpl');
    $('#agui-menu-rows').append(tpl.content.cloneNode(true));
  });
  $(document).on('click', '.agui-row-del', function () { $(this).closest('li').remove(); });
});
