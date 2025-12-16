function geoipRedirectPopup(config) {
    return {
        visible: false,
        message: '',
        acceptLabel: 'Accept',
        declineLabel: 'Decline',
        acceptUrl: config.acceptUrl,
        declineUrl: config.declineUrl,

        init() {
            const redirectCookie = this.getCookie('maxmind_redirect');

            if (config.storeCode === redirectCookie) {
                return;
            }

            fetch(config.checkUrl, { credentials: 'same-origin' })
                .then(r => r.json())
                .then(response => {
                    if (response.length >= 2 && response[0] && response[1] !== redirectCookie) {
                        if (typeof response[3] === 'string' && response[3].trim() !== '') {
                            this.message = response[3].replace('{{geolocated_country}}', response[2]);
                        }

                        if (typeof response[4] === 'string') {
                            this.acceptLabel = response[4];
                        }

                        if (typeof response[5] === 'string') {
                            this.declineLabel = response[5];
                        }

                        this.visible = true;
                    }
                });
        },

        getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    };
}
