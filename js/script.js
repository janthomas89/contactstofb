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
            this.initListView();
        },

        initSettings: function() {
            var that = this;
            var $navigation = $('#app-navigation');
            var $form = $navigation.find('#app-settings-form');
            var $submit = $form.find('input[type=submit]');
            var submitLabel = $submit.val();

            $form.on('submit', function(e) {
                e.preventDefault();
                $submit.attr('disabled', true).val($submit.data('saving-label'));
                $.ajax({
                    type: $form.attr('method'),
                    url: $form.attr('action'),
                    data: $form.serialize()
                }).always(function(data) {
                    $submit.attr('disabled', false).val(submitLabel);
                    if (!data.status || data.status != 'success') {
                        alert(data.msg || 'an error occurred while saving the settings');
                    }
                });
            });

            $navigation.find('#app-sync-now').on('click', function() {
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
                    } else {
                        location.reload();
                    }
                });
            });
        },

        initListView: function() {
            $('#app-content table').stickyTableHeaders({
                scrollableArea: $('#app-content')
            });

            /*
            var $table = $('#app-content table');

            $table.find('thead th').each(function(i) {
                $table.find('tbody tr td:eq(' + i + ')').width($(this).width());
            });

            $table.find('tbody').width('100%');
            */
        }
    };

    $(function() {
        var app = new App();
        app.init();
    });
})(jQuery, OC);
