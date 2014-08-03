/**
 * Javascript part of the application.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
(function ($, OC) {

    var App = OC.ContactsToFb = function() {

    };

    App.prototype = {
        init: function() {
            this.initSettings();
        },

        initSettings: function() {
            var that = this;
            var $settings = $('#app-settings');
            var $form = $settings.find('#app-settings-form');

            $form.on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: $form.attr('method'),
                    url: $form.attr('action'),
                    data: $form.serialize()
                }).always(function(data) {
                    if (!data.status || data.status != 'success') {
                        alert(data.msg || 'an error occurred while saving the settings');
                    }
                });
            }).find('input').change(function() {
                $form.submit();
            });

            $settings.find('#app-sync-now').on('click', function() {
                var $elm = $(this);
                var url = $elm.data('url');
                var label = $elm.val();

                $elm.attr('disabled', true).val($elm.data('synchronizing-label'));

                $.ajax(url, {
                    type: 'post',
                    data: {
                        requesttoken: $elm.data('token')
                    }
                }).always(function(data) {
                    $elm.attr('disabled', false).val(label);
                    if (!data.status || data.status != 'success') {
                        alert(data.msg || 'an error occurred while syncing the contacts');
                    }
                });
            });

            $settings.find('#app-settings-header').on('click keydown', function(event) {
                if(that.wrongKey(event)) {
                    return;
                }

                var bodyListener = function(e) {
                    if($settings.find($(e.target)).length === 0) {
                        $settings.switchClass('open', '');
                    }
                };
                if($settings.hasClass('open')) {
                    $settings.switchClass('open', '');
                    $('body').unbind('click', bodyListener);
                } else {
                    $settings.switchClass('', 'open');
                    $('body').bind('click', bodyListener);
                }
            });
        },

        wrongKey: function(event) {
            return ((event.type === 'keydown' || event.type === 'keypress')
                    && (event.keyCode !== 32 && event.keyCode !== 13));
        }
    };

    $(function() {
        var app = new App();
        app.init();
    });
})(jQuery, OC);
