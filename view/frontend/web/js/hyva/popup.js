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
                    if (response.showPopup && response.targetStore !== redirectCookie) {
                        if (typeof response.popupText === 'string' && response.popupText.trim() !== '') {
                            this.message = response.popupText.replace('{{geolocated_country}}', response.countryName);
                        }

                        if (typeof response.acceptLabel === 'string') {
                            this.acceptLabel = response.acceptLabel;
                        }

                        if (typeof response.declineLabel === 'string') {
                            this.declineLabel = response.declineLabel;
                        }

                        this.visible = true;
                        this.$nextTick(() => {
                            this.$refs.acceptBtn && this.$refs.acceptBtn.focus();
                        });
                    }
                });
        },

        getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    };
}
