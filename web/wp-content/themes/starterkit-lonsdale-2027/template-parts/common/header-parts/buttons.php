<?php
$locations = get_nav_menu_locations();
$header_items = !empty($locations['menu-header-cta']) ? wp_get_nav_menu_items($locations['menu-header-cta']) : [];
$header_items = is_array($header_items) ? array_filter($header_items, static fn ($item) => (int) $item->menu_item_parent === 0) : [];
if ($header_items !== []) : ?>
        <div class="header-actions">
            <?php foreach ($header_items as $item) :
                $payload = ['link' => ['url' => $item->url, 'title' => $item->title, 'target' => $item->target], 'classes' => 'btn-1'];

                component('btn', $payload);
            endforeach; ?>
        </div>
<?php endif; ?>
