/* Administración del tema Aguicons: selectores de imagen, galería ordenable e importador de contenido. */
jQuery(function ($) {
  'use strict';

  function frame(opts, cb) {
    var f = wp.media({ title: opts.title || 'Elegir', multiple: !!opts.multiple, library: { type: opts.type || 'image' } });
    f.on('select', function () { cb(f.state().get('selection')); });
    f.open();
  }

  /* Imagen única y galería */
  $(document).on('click', '.agui-media-pick', function () {
    var $f = $(this).closest('.agui-media-field');
    var multiple = $f.data('multiple') === 1 || $f.data('multiple') === '1';
    var $input = $f.find('input[type=hidden]').first();
    frame({ multiple: multiple }, function (sel) {
      if (multiple) {
        var ids = ($input.val() ? $input.val().split(',') : []);
        sel.each(function (a) {
          var id = String(a.get('id'));
          if (ids.indexOf(id) === -1) {
            ids.push(id);
            var url = (a.get('sizes') && a.get('sizes').thumbnail ? a.get('sizes').thumbnail.url : a.get('url'));
            $f.find('.agui-gallery-list').append('<li data-id="' + id + '"><img src="' + url + '"><button type="button" class="agui-gallery-remove" aria-label="Quitar">×</button></li>');
          }
        });
        $input.val(ids.join(','));
      } else {
        var a = sel.first();
        $input.val(a.get('id'));
        var url = (a.get('sizes') && a.get('sizes').medium ? a.get('sizes').medium.url : a.get('url'));
        $f.find('.agui-media-preview').html('<img src="' + url + '" style="max-width:220px;height:auto">');
      }
    });
  });
  $(document).on('click', '.agui-media-clear', function () {
    var $f = $(this).closest('.agui-media-field');
    $f.find('input[type=hidden]').first().val('');
    $f.find('.agui-media-preview').empty();
  });
  $(document).on('click', '.agui-gallery-remove', function () {
    var $f = $(this).closest('.agui-media-field');
    $(this).closest('li').remove();
    syncGallery($f);
  });
  function syncGallery($f) {
    var ids = $f.find('.agui-gallery-list li').map(function () { return $(this).data('id'); }).get();
    $f.find('input[type=hidden]').first().val(ids.join(','));
  }
  $('.agui-gallery-list').sortable({
    update: function () { syncGallery($(this).closest('.agui-media-field')); }
  });

  /* Archivo (PDF) */
  $(document).on('click', '.agui-file-pick', function () {
    var $f = $(this).closest('.agui-file-field');
    frame({ type: 'application/pdf' }, function (sel) {
      var a = sel.first();
      $f.find('input[type=hidden]').val(a.get('id'));
      $f.find('.agui-file-name').text(a.get('filename'));
    });
  });
  $(document).on('click', '.agui-file-clear', function () {
    var $f = $(this).closest('.agui-file-field');
    $f.find('input[type=hidden]').val('');
    $f.find('.agui-file-name').text('Ningún archivo');
  });


  /* Avance de obra: filas con fecha, texto y fotos (se guardan como JSON) */
  var $av = $('#agui-avance');
  if ($av.length) {
    var rowsData = [];
    try { rowsData = JSON.parse($av.attr('data-rows') || '[]'); } catch (e) { rowsData = []; }
    function thumbUrl(id, cb) {
      var att = wp.media.attachment(id);
      att.fetch().then(function () { var sz = att.get('sizes'); cb(sz && sz.thumbnail ? sz.thumbnail.url : att.get('url')); });
    }
    function addRow(r) {
      r = r || { date: '', text: '', ids: [] };
      var $row = $('<div class="agui-av-row"><input type="text" class="av-date" placeholder="Fecha (ej: Marzo 2026)"><input type="text" class="av-text large-text" placeholder="Qué se hizo"><ul class="agui-gallery-list av-imgs"></ul><button type="button" class="button av-pick">Agregar fotos</button> <button type="button" class="button-link av-del">Quitar entrada</button><hr></div>');
      $row.find('.av-date').val(r.date); $row.find('.av-text').val(r.text);
      (r.ids || []).forEach(function (id) { addImg($row, id); });
      $av.append($row);
      $row.find('.av-imgs').sortable({ update: save });
    }
    function addImg($row, id) {
      var $li = $('<li data-id="' + id + '"><img alt=""><button type="button" class="agui-gallery-remove" aria-label="Quitar">×</button></li>');
      $row.find('.av-imgs').append($li);
      thumbUrl(id, function (u) { $li.find('img').attr('src', u); });
    }
    function save() {
      var out = $av.find('.agui-av-row').map(function () {
        var $r = $(this);
        return { date: $r.find('.av-date').val(), text: $r.find('.av-text').val(), ids: $r.find('.av-imgs li').map(function () { return $(this).data('id'); }).get() };
      }).get();
      $('#agui-avance-json').val(JSON.stringify(out));
    }
    rowsData.forEach(addRow);
    $('#agui-avance-add').on('click', function () { addRow(); });
    $av.on('input change', 'input', save);
    $av.on('click', '.av-del', function () { $(this).closest('.agui-av-row').remove(); save(); });
    $av.on('click', '.agui-gallery-remove', function () { $(this).closest('li').remove(); save(); });
    $av.on('click', '.av-pick', function () {
      var $row = $(this).closest('.agui-av-row');
      frame({ multiple: true }, function (sel) { sel.each(function (a) { addImg($row, a.get('id')); }); save(); });
    });
    $('#post').on('submit', save);
  }

  /* Importador de contenido inicial */
  $('#agui-import-start').on('click', function () {
    var $btn = $(this).prop('disabled', true);
    var $log = $('#agui-import-log').empty();
    var cfg = window.AGUI_ADMIN;
    function post(step, index) {
      return $.post(cfg.ajax, { action: 'agui_import_step', nonce: cfg.nonce, step: step, index: index });
    }
    function run(step, index) {
      post(step, index).done(function (res) {
        if (!res || !res.success) { $log.append('<li style="color:#c00">Error: ' + (res && res.data ? res.data : 'respuesta inválida') + '</li>'); $btn.prop('disabled', false); return; }
        var d = res.data;
        $log.append('<li>' + d.label + '</li>');
        $('#agui-import-progress').attr({ value: d.done, max: d.total });
        if (d.next) run(d.next.step, d.next.index);
        else { $log.append('<li><strong>¡Listo! Ya puede ver el sitio.</strong></li>'); $btn.prop('disabled', false); }
      }).fail(function () { $log.append('<li style="color:#c00">Error de conexión. Vuelva a intentar.</li>'); $btn.prop('disabled', false); });
    }
    run('images', 0);
  });
});
