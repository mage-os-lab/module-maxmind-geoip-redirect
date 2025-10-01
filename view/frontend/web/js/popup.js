define([
    'jquery',
    'mage/cookies',
    'mage/url',
    'jquery-ui-modules/widget'
], function ($, cookies, urlBuilder) {
    'use strict';

    $.widget('maxmind.geolitepopup', {
        options: {
            storeCode: ''
        },

        _create: function () {
            let redirectCookie = $.mage.cookies.get('maxmind_redirect');
            if (this.options.storeCode !== redirectCookie) {
                $.ajax({
                    url: window.BASE_URL + 'maxmind/geoip/checkpopup',
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.length >= 2 && response[0] && response[1] !== redirectCookie) {
                            $('#geoip-redirect-popup > .popup-content > .geoip-message').html(function (_, html) {
                                if (typeof response[3] === 'string' && response[3].trim() !== '') {
                                    html = response[3];
                                }

                                return html.replace('{{geolocated_country}}', response[2]);
                            });
                            $('#geoip-redirect-popup > .popup-content .action-primary').html(function (_, html) {
                                if (typeof response[3] === 'string' && response[3].trim() !== '') {
                                    return response[4];
                                }
                            });
                            $('#geoip-redirect-popup > .popup-content .action-secondary').html(function (_, html) {
                                if (typeof response[3] === 'string' && response[3].trim() !== '') {
                                    return response[5];
                                }
                            });
                            $('#geoip-redirect-popup').show();
                        }
                    }
                });
            }
        }
    });

    return $.maxmind.geolitepopup;
});
