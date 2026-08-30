<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="format-detection" content="telephone=no" />
    <meta name="msapplication-tap-highlight" content="no">

    <link rel="icon" type="image/svg+xml" href="<?= THEME_ASSETS ?>favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= THEME_ASSETS ?>favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="<?= THEME_ASSETS ?>favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= THEME_ASSETS ?>favicon/apple-touch-icon.png">

    <link rel="preload" href="<?= THEME_ASSETS ?>fonts/SequelSansLight.woff2" as="font" type="font/woff2" crossorigin="">
    <link rel="preload" href="<?= THEME_ASSETS ?>fonts/SequelSansBook.woff2" as="font" type="font/woff2" crossorigin="">
    <link rel="preload" href="<?= THEME_ASSETS ?>fonts/SequelSansHeavy.woff2" as="font" type="font/woff2" crossorigin="">

    <?php wp_head(); ?>

    <?php if (defined('LSD_GTM_ID') && LSD_GTM_ID !== '') : ?>
        <script>
            window.dataLayer = window.dataLayer || [];
        </script>
    <?php endif ?>
</head>

<?php $isInternal = isInternalIp(); ?>

<body <?php body_class(); ?> <?= !empty($isInternal) ? 'data-theme="private"' : "" ?>>
    <?php wp_body_open(); ?>