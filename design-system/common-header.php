<?php
/** The only fragment replaced when a header setting changes. */
if ($configurable) $GLOBALS['theme_header_preview'] = $header_config;
try {
    if (isset($items[$header])) common($header);
} finally {
    unset($GLOBALS['theme_header_preview']);
}
