<footer>
    <div class="blocks container">
        <div>
            <span class="Random_Play">Random_Play</span>
            <p>We create, you watch</p>
        </div>
        <div>
            <h4>About us</h4>
            <ul>
                <li>Team Random Play</li>
                <li>More than 5 years of experience</li>
                <li>Lots of positive reviews</li>
                <li>Responsive support</li>
            </ul>
        </div>
        <div>
            <h4>Contact us</h4>
            <p>We are always ready to listen your complaints and suggestions.</p>
            <p>+79211704527</p>
            <p>alexfebruar2005@gmail.com</p>
        </div>
    </div>
    <hr>
    <p>Copyright© 2024</p>
</footer>
<script>
    (function () {
        const payload = JSON.stringify({ action: 'pageview', path: window.location.pathname });
        if (navigator.sendBeacon) {
            const blob = new Blob([payload], { type: 'application/json' });
            navigator.sendBeacon('/api/track.php', blob);
            return;
        }
        fetch('/api/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            keepalive: true,
            body: payload,
        }).catch(function () {});
    })();
</script>