<?php common('footer'); ?>
<?php wp_footer(); ?>

<?php if (defined('LSD_GTM_ID') && LSD_GTM_ID !== '') : ?>
<script>
window.dataLayer.push({ event: 'dataLayer.ready' });

function lsdLoadGtm() {
    if (window.__lsdGtmLoaded) return;
    window.__lsdGtmLoaded = true;
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',<?= wp_json_encode((string) LSD_GTM_ID) ?>);
}

if (document.readyState === 'complete') {
    lsdLoadGtm();
} else {
    window.addEventListener('load', lsdLoadGtm, { once: true });
}
</script>
<?php endif ?>
</body>
</html>
