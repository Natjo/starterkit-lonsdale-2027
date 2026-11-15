<div data-common-footer-slot<?= $footer !== '' ? ' id="common-' . esc_attr($footer) . '"' : '' ?>>
<?php
if ($footer_configurable && $footer === 'footer') $GLOBALS['theme_footer_preview'] = $footer_config;
try { if ($footer !== '') common($footer); }
finally { unset($GLOBALS['theme_footer_preview']); }
?>
</div>
