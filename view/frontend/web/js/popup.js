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
                        if (response.showPopup && response.targetStore !== redirectCookie) {
                            $('#geoip-redirect-popup > .popup-content > .geoip-message').html(function (_, html) {
                                if (typeof response.popupText === 'string' && response.popupText.trim() !== '') {
                                    html = response.popupText;
                                }

                                return html.replace('{{geolocated_country}}', response.countryName);
                            });
                            $('#geoip-redirect-popup > .popup-content .action-primary').html(function (_, html) {
                                if (typeof response.acceptLabel === 'string') {
                                    return response.acceptLabel;
                                }
                            });
                            $('#geoip-redirect-popup > .popup-content .action-secondary').html(function (_, html) {
                                if (typeof response.declineLabel === 'string') {
                                    return response.declineLabel;
                                }
                            });
                            var $popup = $('#geoip-redirect-popup');
                            $popup.show();
                            $popup.find('.action-primary').focus();

                            $(document).on('keydown.geoip', function (e) {
                                if (e.key === 'Escape') {
                                    window.location.href = $popup.find('.action-secondary').attr('href');
                                }
                            });
                        }
                    }
                });
            }
        }
    });

    return $.maxmind.geolitepopup;
});
