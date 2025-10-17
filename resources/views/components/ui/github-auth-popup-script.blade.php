<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('githubAuthPopup', (defaultRedirect) => ({
            popup: null,
            defaultRedirect,
            messageHandler: null,
            init() {
                this.messageHandler = (event) => {
                    if (event.origin !== window.location.origin) {
                        return;
                    }

                    const data = event.data || {};

                    if (data.source !== 'github-auth-popup') {
                        return;
                    }

                    if (this.popup && !this.popup.closed) {
                        this.popup.close();
                    }

                    if (data.type === 'success') {
                        const destination = data.redirectUrl || this.defaultRedirect;
                        window.location.href = destination;
                        return;
                    }

                    if (data.type === 'error') {
                        const destination = data.redirectUrl || window.location.href;
                        window.location.href = destination;
                    }
                };

                window.addEventListener('message', this.messageHandler);

                window.addEventListener('beforeunload', () => {
                    if (this.popup && !this.popup.closed) {
                        this.popup.close();
                    }
                });
            },
            open(url) {
                const width = 640;
                const height = 760;
                const left = window.screenX + Math.max(0, (window.outerWidth - width) / 2);
                const top = window.screenY + Math.max(0, (window.outerHeight - height) / 2);

                const features = [
                    `width=${width}`,
                    `height=${height}`,
                    `left=${left}`,
                    `top=${top}`,
                    'resizable=yes',
                    'scrollbars=yes',
                ].join(',');

                this.popup = window.open(url, 'githubAuthWindow', features);

                if (this.popup) {
                    this.popup.focus();
                } else {
                    window.location.href = url;
                }
            },
        }));
    });
</script>
