    <a class="nav-logo header-nav-logo" href="<?= esc_url(home_url('/')) ?>" aria-label="<?= esc_attr($site_name) ?>">
        <?php component('icon', ['name' => 'logo', 'width' => 54, 'height' => 55]); ?>
        <span class="sr-only"><?= esc_html($site_name) ?></span>
    </a>
    <button class="btn-nav" type="button" aria-expanded="false" aria-controls="nav">
        <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
        <span class="sr-only"><?= esc_html(__('Menu', 'theme')) ?></span>
    </button>
    <nav id="nav" aria-label="<?= esc_attr(__('Navigation principale', 'theme')) ?>">
