<?php

/** Contrôles locaux du Styleguide, alimentés par le groupe ACF des options. */
function theme_styleguide_strate_options(string $module, array $payload): void
{
    static $fields = null;
    if ($fields === null) {
        $path = get_template_directory() . '/acf-json/group_strates_options.json';
        $group = is_readable($path) ? json_decode(file_get_contents($path), true) : [];
        $fields = [];
        $flatten = static function (array $items, string $path = '') use (&$flatten, &$fields): void {
            foreach ($items as $field) {
                if (!is_array($field) || !is_string($field['name'] ?? null)) continue;
                $name = $field['name'];
                $key = $path !== '' ? $path . '.' . $name : $name;
                $title = trim((string) ($field['label'] ?? ''));
                if ($field['type'] === 'group') {
                    $flatten($field['sub_fields'] ?? [], $key);
                } elseif (in_array($field['type'] ?? '', ['select', 'true_false'], true)) {
                    $fields[] = [
                        'path' => $key,
                        'label' => (str_starts_with($key, 'background.padding.') ? 'Padding — ' : '') . ($title !== '' ? $title : 'Activer le fond'),
                        'type' => $field['type'],
                        'choices' => $field['choices'] ?? [],
                        'default' => $field['default_value'] ?? '',
                    ];
                }
            }
        };
        $flatten(is_array($group) ? ($group['fields'][0]['sub_fields'] ?? []) : []);
    }
    if ($fields === []) return;

    $options = is_array($payload['options'] ?? null) ? $payload['options'] : [];
    ?>
    <form class="styleguide-strate-options" id="options-<?= esc_attr($module) ?>" data-styleguide-strate-options aria-label="Options de strate" novalidate>
        <?php foreach (['container' => 'Conteneur', 'margin' => 'Marges', 'background' => 'Background'] as $group => $label) : ?>
        <section class="styleguide-strate-options-group" aria-labelledby="options-<?= esc_attr($module . '-' . $group) ?>">
            <h3 id="options-<?= esc_attr($module . '-' . $group) ?>"><?= esc_html($label) ?></h3>
            <div class="styleguide-strate-options-grid">
            <?php foreach ($fields as $field) :
                if (explode('.', $field['path'])[0] !== $group) continue;
                $value = $options;
                foreach (explode('.', $field['path']) as $part) {
                    $value = is_array($value) && array_key_exists($part, $value) ? $value[$part] : null;
                }
                $value ??= $field['default'];
                $string_value = is_scalar($value) ? (string) $value : '';
                $background_field = str_starts_with($field['path'], 'background.') && $field['path'] !== 'background.hasbackground';
            ?>
                <label class="styleguide-strate-control">
                    <span<?= $field['path'] === 'container' ? ' class="sr-only"' : '' ?>><?= esc_html($field['label']) ?></span>
                    <?php if ($field['type'] === 'select') : ?>
                        <select data-strate-option="<?= esc_attr($field['path']) ?>"<?= $background_field ? ' data-strate-background' : '' ?>>
                            <?php foreach ($field['choices'] as $choice => $choice_label) : ?>
                                <option value="<?= esc_attr((string) $choice) ?>"<?= $string_value === (string) $choice ? ' selected' : '' ?>><?= esc_html((string) $choice_label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($field['type'] === 'true_false') : ?>
                        <input type="checkbox" data-strate-option="<?= esc_attr($field['path']) ?>"<?= !empty($value) ? ' checked' : '' ?>>
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
        <div class="styleguide-strate-options-actions">
            <button type="reset">Réinitialiser</button>
            <p data-strate-options-message role="status"></p>
        </div>
    </form>
    <?php
}
