<?php
/**
 * Peu comu de totes les pagines renderitzades amb render_page().
 *
 * Tanca el document HTML obert a header.php i carrega el JavaScript global.
 */
?>
<footer class="orbit-footer" style="border-top: none; background: none; padding: 1rem 1rem 1.5rem;">
    <div class="orbit-footer__track" style="justify-content: flex-start; padding: 0 clamp(1rem, 3vw, 2rem);">
        <span style="font-size: 0.62rem; letter-spacing: 0.14em; color: rgba(196,160,190,0.35);">CAS3 &middot; 2025–2026</span>
    </div>
</footer>
<script src="<?= h(asset_url('js/app.js')) ?>" defer></script>
</body>
</html>
