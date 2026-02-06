(function ($) {
    'use strict';

    var IQR = {
        currentSection: 'qr',

        init: function () {
            this.bindNavigation();
            this.loadSection('qr');
        },

        /* ── Navigation ── */
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

        /* ── Section loader ── */
        loadSection: function (section) {
            var self = this;
            var titles = {
                'qr':            'QR Codes',
                'inventory':     'Inventory',
                'export-import': 'Export / Import',
                'defaults':      'Defaults',
                'user':          'Profile'
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
                        '<p class="iqr-text-muted">' + (res.data && res.data.message ? res.data.message : 'Error loading section.') + '</p>'
                    );
                }
            }).fail(function () {
                $('#iqr-content').html('<p class="iqr-text-muted">Connection error. Please try again.</p>');
            });
        },

        /* ── Section-specific handlers ── */
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

        /* ── Inventory ── */
        initInventory: function () {
            var $modal = $('#iqr-item-modal');

            $('#iqr-add-item-btn').on('click', function () {
                $('#iqr-modal-title').text('Add Item');
                $('#iqr-item-form')[0].reset();
                $('#iqr-item-id').val('');
                $modal.fadeIn(200);
            });

            $('#iqr-modal-close, #iqr-modal-cancel, .iqr-modal-overlay').on('click', function () {
                $modal.fadeOut(150);
            });

            $('#iqr-item-form').on('submit', function (e) {
                e.preventDefault();
                IQR.toast('Item saved successfully.', 'success');
                $modal.fadeOut(150);
            });
        },

        /* ── Export / Import ── */
        initExportImport: function () {
            var $dropzone = $('#iqr-import-dropzone');
            var $fileInput = $('#iqr-import-file');

            $dropzone.on('click', function () {
                $fileInput.trigger('click');
            });

            $fileInput.on('change', function () {
                if (this.files.length) {
                    $dropzone.find('p').text(this.files[0].name);
                    $('#iqr-import-btn').prop('disabled', false);
                }
            });

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
                    $('#iqr-import-btn').prop('disabled', false);
                }
            });

            $('#iqr-export-btn').on('click', function () {
                IQR.toast('Export started.', 'success');
            });

            $('#iqr-import-btn').on('click', function () {
                IQR.toast('Import started.', 'success');
            });
        },

        /* ── User / Logout ── */
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

        /* ── Toast notifications ── */
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

    /* ── Frontend Login ── */
    function initLogin() {
        $('#iqr-login-form').on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#iqr-login-submit');
            var $error = $('#iqr-login-error');

            $btn.prop('disabled', true).text('Signing in...');
            $error.hide();

            $.post(iqrData.ajaxUrl, {
                action:   'iqr_login',
                nonce:    iqrData.loginNonce,
                username: $('#iqr-login-user').val(),
                password: $('#iqr-login-pass').val()
            }, function (res) {
                if (res.success) {
                    window.location.reload();
                } else {
                    $error.text(res.data.message).show();
                    $btn.prop('disabled', false).text('Sign In');
                }
            }).fail(function () {
                $error.text('Connection error. Please try again.').show();
                $btn.prop('disabled', false).text('Sign In');
            });
        });
    }

    $(document).ready(function () {
        if ($('#iqr-app').length) {
            IQR.init();
        }
        if ($('#iqr-login-form').length) {
            initLogin();
        }
    });

})(jQuery);
