<?php
$locations = get_nav_menu_locations();
$header_items = !empty($locations['menu-header-tools']) ? wp_get_nav_menu_items($locations['menu-header-tools']) : [];
$header_items = is_array($header_items) ? array_filter($header_items, static fn ($item) => (int) $item->menu_item_parent === 0) : [];
if ($header_items !== []) : ?>
        <div class="header-pictos">
            <?php foreach ($header_items as $item) :
                $payload = ['link' => ['url' => $item->url, 'title' => $item->title, 'target' => $item->target], 'classes' => 'header-picto'];
                // A menu CSS class such as icon-email selects an existing sprite symbol.
                foreach ((array) $item->classes as $class) {
                    if (is_string($class) && preg_match('/^icon-([a-z0-9_-]+)$/i', $class, $match)) {
                        $payload['icon'] = ['name' => $match[1]];
                        break;
                    }
                }
                component('link', $payload);
            endforeach; ?>
        </div>
<?php endif; ?>
