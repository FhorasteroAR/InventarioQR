(function ($) {
    'use strict';

    var IQR = {
        currentSection: 'qr',

        init: function () {
            this.bindNavigation();
            this.loadSection('qr');
        },

        /* ── Navegación ── */
        bindNavigation: function () {
            var self = this;

            $(document).on('click', '.iqr-nav-item[data-section]', function (e) {
                e.preventDefault();
                var section = $(this).data('section');
                if (section === self.currentSection) return;
                self.setActiveNav($(this));
                self.loadSection(section);
            });
        },

        setActiveNav: function ($el) {
            $('.iqr-nav-item').removeClass('active');
            $el.addClass('active');
        },

        /* ── Cargador de secciones ── */
        loadSection: function (section) {
            var self = this;
            var titles = {
                'qr':            'Códigos QR',
                'inventory':     'Inventario',
                'export-import': 'Exportar / Importar',
                'defaults':      'Configuración',
                'user':          'Perfil'
            };

            self.currentSection = section;
            $('#iqr-page-title').text(titles[section] || section);
            $('#iqr-content').html('<div class="iqr-spinner"></div>');

            $.post(iqrData.ajaxUrl, {
                action:  'iqr_get_section',
                section: section,
                nonce:   iqrData.nonce
            }, function (res) {
                if (res.success) {
                    $('#iqr-content').html(res.data.html);
                    self.initSectionHandlers(section);
                } else {
                    $('#iqr-content').html(
                        '<p class="iqr-text-muted">' + (res.data && res.data.message ? res.data.message : 'Error al cargar la sección.') + '</p>'
                    );
                }
            }).fail(function () {
                $('#iqr-content').html('<p class="iqr-text-muted">Error de conexión. Intentá de nuevo.</p>');
            });
        },

        /* ── Handlers por sección ── */
        initSectionHandlers: function (section) {
            switch (section) {
                case 'inventory':
                    this.initInventory();
                    break;
                case 'export-import':
                    this.initExportImport();
                    break;
                case 'user':
                    this.initUser();
                    break;
            }
        },

        /* ── Inventario ── */
        initInventory: function () {
            var $modal = $('#iqr-item-modal');

            $('#iqr-add-item-btn').on('click', function () {
                $('#iqr-modal-title').text('Agregar bien');
                $('#iqr-item-form')[0].reset();
                $('#iqr-item-id').val('');
                $modal.fadeIn(200);
            });

            $('#iqr-modal-close, #iqr-modal-cancel, .iqr-modal-overlay').on('click', function () {
                $modal.fadeOut(150);
            });

            $('#iqr-item-form').on('submit', function (e) {
                e.preventDefault();
                IQR.toast('Bien guardado correctamente.', 'success');
                $modal.fadeOut(150);
            });
        },

        /* ── Exportar / Importar ── */
        initExportImport: function () {
            var $dropzone = $('#iqr-import-dropzone');
            var $fileInput = $('#iqr-import-file');
            var $importBtn = $('#iqr-import-btn');
            var $status = $('#iqr-import-status');

            // Click en dropzone abre el selector de archivos
            $dropzone.on('click', function () {
                $fileInput.trigger('click');
            });

            // Al seleccionar archivo
            $fileInput.on('change', function () {
                if (this.files.length) {
                    $dropzone.find('p').text(this.files[0].name);
                    $importBtn.prop('disabled', false);
                }
            });

            // Drag & drop
            $dropzone.on('dragover', function (e) {
                e.preventDefault();
                $(this).addClass('dragover');
            }).on('dragleave drop', function (e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            }).on('drop', function (e) {
                var files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    $fileInput[0].files = files;
                    $dropzone.find('p').text(files[0].name);
                    $importBtn.prop('disabled', false);
                }
            });

            // Botón Exportar — descarga real vía AJAX
            $('#iqr-export-btn').on('click', function () {
                var format = $('#iqr-export-format').val();
                var source = $('#iqr-export-source').val();

                IQR.toast('Exportando datos...', 'success');

                $.post(iqrData.ajaxUrl, {
                    action: 'iqr_export_data',
                    format: format,
                    source: source,
                    nonce:  iqrData.nonce
                }, function (res) {
                    if (res.success) {
                        var byteChars = atob(res.data.data);
                        var byteNumbers = new Array(byteChars.length);
                        for (var i = 0; i < byteChars.length; i++) {
                            byteNumbers[i] = byteChars.charCodeAt(i);
                        }
                        var byteArray = new Uint8Array(byteNumbers);
                        var blob = new Blob([byteArray], { type: res.data.mime });

                        var url = URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = res.data.filename;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);

                        IQR.toast('Exportación completada.', 'success');
                    } else {
                        IQR.toast(res.data.message || 'Error al exportar.', 'error');
                    }
                }).fail(function () {
                    IQR.toast('Error de conexión al exportar.', 'error');
                });
            });

            // Botón Importar — abre selector de archivos si no hay archivo, o sube vía AJAX
            $importBtn.on('click', function () {
                var files = $fileInput[0].files;
                if (!files.length) {
                    $fileInput.trigger('click');
                    return;
                }

                var formData = new FormData();
                formData.append('action', 'iqr_import_excel');
                formData.append('nonce', iqrData.nonce);
                formData.append('import_file', files[0]);

                $importBtn.prop('disabled', true).text('Importando...');
                $status.show().text('Subiendo archivo...');

                $.ajax({
                    url: iqrData.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            $status.text(res.data.message);
                            IQR.toast(res.data.message, 'success');
                            // Resetear el dropzone
                            $dropzone.find('p').text('Arrastrá y soltá un archivo aquí, o hacé clic para buscar');
                            $fileInput.val('');
                        } else {
                            $status.text('Error: ' + (res.data.message || 'Error desconocido.'));
                            IQR.toast(res.data.message || 'Error al importar.', 'error');
                        }
                        $importBtn.prop('disabled', true).text('Importar datos');
                    },
                    error: function () {
                        $status.text('Error de conexión.');
                        IQR.toast('Error de conexión al importar.', 'error');
                        $importBtn.prop('disabled', false).text('Importar datos');
                    }
                });
            });
        },

        /* ── Usuario / Logout ── */
        initUser: function () {
            $('#iqr-logout-btn').on('click', function () {
                $.post(iqrData.ajaxUrl, {
                    action: 'iqr_logout',
                    nonce:  iqrData.logoutNonce
                }, function (res) {
                    if (res.success) {
                        window.location.href = res.data.redirect;
                    }
                });
            });
        },

        /* ── Notificaciones toast ── */
        toast: function (message, type) {
            type = type || 'success';
            var $toast = $('<div class="iqr-toast iqr-toast-' + type + '">' + message + '</div>');
            $('body').append($toast);
            setTimeout(function () {
                $toast.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    $(document).ready(function () {
        if ($('#iqr-app').length) {
            IQR.init();
        }
    });

})(jQuery);
