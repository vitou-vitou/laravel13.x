@if (app()->environment('local'))
<script>
    (function () {
        if (typeof crypto !== 'undefined' && typeof crypto.randomUUID !== 'function') {
            crypto.randomUUID = function () {
                var bytes = new Uint8Array(16);
                crypto.getRandomValues(bytes);
                bytes[6] = (bytes[6] & 0x0f) | 0x40;
                bytes[8] = (bytes[8] & 0x3f) | 0x80;
                var hex = Array.from(bytes, function (b) { return b.toString(16).padStart(2, '0'); }).join('');
                return hex.slice(0, 8) + '-' + hex.slice(8, 12) + '-' + hex.slice(12, 16) + '-' + hex.slice(16, 20) + '-' + hex.slice(20);
            };
        }

        function reloadImpeccableLive() {
            if (! document.querySelector('script[src*="live.js"]')) {
                return;
            }

            window.__IMPECCABLE_LIVE_INIT__ = false;
            document.getElementById('impeccable-live-global-bar')?.remove();
            document.getElementById('impeccable-live-bar')?.remove();

            var src = 'http://localhost:8400/live.js?t=' + Date.now();
            var s = document.createElement('script');
            s.src = src;
            document.body.appendChild(s);
        }

        document.addEventListener('livewire:initialized', function () {
            setTimeout(reloadImpeccableLive, 0);
        });
        document.addEventListener('livewire:navigated', function () {
            setTimeout(reloadImpeccableLive, 0);
        });
    })();
</script>
@endif
