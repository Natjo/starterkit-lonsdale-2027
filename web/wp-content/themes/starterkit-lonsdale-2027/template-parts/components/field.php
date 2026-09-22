<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'type' => 'text',
    'label' => '',
    'name' => '',
    'id' => '',
    'value' => '',
    'required' => false,
    'mandatory_msg' => '',
    'type_mismatch_msg' => '',
    'pattern_mismatch_msg' => '',
    'placeholder' => '',
    'options' => [],
    'hint' => '',
    'rows' => 4,
    'min' => null,
    'max' => null,
    'minlength' => null,
    'pattern' => '',
    'autocomplete' => '',
    'disabled' => false,
    'checked' => false,
    'classes' => '',
    'attributes' => [],
]);
$type = strtolower(component_string($args['type']));
$type = $type === 'radio' ? 'radios' : $type;
$allowed_types = ['text', 'search', 'textarea', 'select', 'checkbox', 'checkboxes', 'radios', 'number', 'email', 'url', 'tel', 'date', 'password'];
$name = component_string($args['name']);

if (!in_array($type, $allowed_types, true) || !preg_match('/^[A-Za-z][A-Za-z0-9_.\-\[\]]*$/', $name)) return;

$options = is_array($args['options']) ? $args['options'] : [];
$is_group = in_array($type, ['checkboxes', 'radios'], true);
if (($is_group || $type === 'select') && $options === []) return;

$id = component_id(component_string($args['id']) ?: str_replace(['[', ']'], '-', $name), 'field-');
$label = component_string($args['label']);
$required = component_bool($args['required']);
$disabled = component_bool($args['disabled']);
$error_id = $id . '-error';
$label_id = $id . '-label';
$hint = component_string($args['hint']);
$hint_id = $id . '-hint';
$describedby = trim($error_id . ($hint !== '' ? ' ' . $hint_id : ''));
$mandatory = component_string($args['mandatory_msg']);
$type_mismatch = component_string($args['type_mismatch_msg']);
$pattern_mismatch = component_string($args['pattern_mismatch_msg']);
$field_classes = ['field'];
if ($type === 'checkbox' || $type === 'checkboxes') $field_classes[] = 'checkbox';
if ($type === 'radios') $field_classes[] = 'radio';
$classes = component_classes($field_classes, $args['classes']);
$attributes = component_attributes($args['attributes'], ['class', 'role', 'aria-labelledby', 'aria-describedby', 'data-required', 'data-mandatory']);
$field_aria = ' aria-describedby="' . esc_attr($describedby) . '"';
$mandatory_attribute = $mandatory !== '' ? ' data-mandatory="' . esc_attr($mandatory) . '"' : '';
$type_mismatch_attribute = $type_mismatch !== '' ? ' data-typemismatch="' . esc_attr($type_mismatch) . '"' : '';
$pattern_mismatch_attribute = $pattern_mismatch !== '' ? ' data-patternmismatch="' . esc_attr($pattern_mismatch) . '"' : '';
$validation_attributes = $mandatory_attribute . $type_mismatch_attribute . $pattern_mismatch_attribute . $field_aria;
$value = component_string($args['value']);
?>

<div
    class="<?= $classes ?>"
    <?= $is_group ? 'role="group" aria-labelledby="' . esc_attr($label_id) . '" aria-describedby="' . esc_attr($describedby) . '"' : '' ?>
    <?= $is_group && $required ? 'data-required aria-required="true"' : '' ?>
    <?= $is_group ? $mandatory_attribute : '' ?><?= $attributes ?>>
    <?php if ($label !== '' && $type !== 'checkbox') : ?>
        <label<?= $is_group ? ' id="' . esc_attr($label_id) . '"' : ' for="' . esc_attr($id) . '"' ?>><?= esc_html($label) ?></label>
    <?php endif; ?>

    <?php if ($type === 'textarea') : ?>
        <textarea id="<?= esc_attr($id) ?>" name="<?= esc_attr($name) ?>" rows="<?= max(2, (int) $args['rows']) ?>"<?= $required ? ' required' : '' ?><?= $disabled ? ' disabled' : '' ?><?= $validation_attributes ?><?= component_string($args['placeholder']) !== '' ? ' placeholder="' . esc_attr(component_string($args['placeholder'])) . '"' : '' ?>><?= esc_html($value) ?></textarea>

    <?php elseif ($type === 'select') : ?>
        <select id="<?= esc_attr($id) ?>" name="<?= esc_attr($name) ?>"<?= $required ? ' required' : '' ?><?= $disabled ? ' disabled' : '' ?><?= $validation_attributes ?>>
            <?php foreach ($options as $option_key => $option) :
                $option = is_array($option) ? $option : ['value' => $option_key, 'label' => $option];
                $option_value = component_string($option['value'] ?? $option_key);
                $option_label = component_string($option['label'] ?? $option['name'] ?? $option_value);
                if ($option_label === '') continue;
                $selected = component_bool($option['selected'] ?? false) || $option_value === $value;
                $option_disabled = component_bool($option['disabled'] ?? false);
                $option_hidden = component_bool($option['hidden'] ?? false);
            ?>
                <option value="<?= esc_attr($option_value) ?>"<?= $selected ? ' selected' : '' ?><?= $option_disabled ? ' disabled' : '' ?><?= $option_hidden ? ' hidden' : '' ?>><?= esc_html($option_label) ?></option>
            <?php endforeach; ?>
        </select>

    <?php elseif ($is_group) : ?>
        <ul>
            <?php $native_required_pending = $type === 'radios' && $required; ?>
            <?php foreach ($options as $index => $option) :
                $option = is_array($option) ? $option : ['value' => $index, 'label' => $option];
                $option_label = component_string($option['label'] ?? $option['name'] ?? '');
                if ($option_label === '') continue;
                $option_value = component_string($option['value'] ?? $index);
                $option_id = sanitize_html_class(component_string($option['id'] ?? '') ?: $id . '-' . component_string($index));
                $option_name = $type === 'checkboxes' ? $name . '[]' : $name;
                $option_checked = component_bool($option['checked'] ?? false) || component_bool($option['selected'] ?? false);
                $option_disabled = component_bool($option['disabled'] ?? false);
                $option_required = $native_required_pending && !$option_disabled;
                if ($option_required) $native_required_pending = false;
            ?>
                <li>
                    <input id="<?= esc_attr($option_id) ?>" type="<?= $type === 'radios' ? 'radio' : 'checkbox' ?>" name="<?= esc_attr($option_name) ?>" value="<?= esc_attr($option_value) ?>"<?= $option_checked ? ' checked' : '' ?><?= $option_disabled ? ' disabled' : '' ?><?= $option_required ? ' required' : '' ?>>
                    <label for="<?= esc_attr($option_id) ?>"><?= esc_html($option_label) ?></label>
                </li>
            <?php endforeach; ?>
        </ul>

    <?php elseif ($type === 'checkbox') : ?>
        <input id="<?= esc_attr($id) ?>" type="checkbox" name="<?= esc_attr($name) ?>" value="<?= esc_attr($value !== '' ? $value : '1') ?>"<?= component_bool($args['checked']) ? ' checked' : '' ?><?= $required ? ' required' : '' ?><?= $disabled ? ' disabled' : '' ?><?= $validation_attributes ?>>
        <?php if ($label !== '') : ?><label for="<?= esc_attr($id) ?>"><?= esc_html($label) ?></label><?php endif; ?>

    <?php else : ?>
        <input
            id="<?= esc_attr($id) ?>"
            type="<?= esc_attr($type) ?>"
            name="<?= esc_attr($name) ?>"
            value="<?= esc_attr($value) ?>"
            <?= $required ? 'required' : '' ?>
            <?= $disabled ? 'disabled' : '' ?>
            <?= component_string($args['placeholder']) !== '' ? 'placeholder="' . esc_attr(component_string($args['placeholder'])) . '"' : '' ?>
            <?= $args['min'] !== null && $type === 'number' ? 'min="' . esc_attr(component_string($args['min'])) . '"' : '' ?>
            <?= $args['max'] !== null && $type === 'number' ? 'max="' . esc_attr(component_string($args['max'])) . '"' : '' ?>
            <?= $args['minlength'] !== null && $type === 'text' ? 'minlength="' . max(0, (int) $args['minlength']) . '"' : '' ?>
            <?= component_string($args['pattern']) !== '' ? 'pattern="' . esc_attr(component_string($args['pattern'])) . '"' : '' ?>
            <?= component_string($args['autocomplete']) !== '' ? 'autocomplete="' . esc_attr(component_string($args['autocomplete'])) . '"' : '' ?>
            <?= $validation_attributes ?>>
    <?php endif; ?>

    <div id="<?= esc_attr($error_id) ?>" class="invalid-msg" aria-live="polite" hidden></div>
    <?php if ($hint !== '') : ?><small id="<?= esc_attr($hint_id) ?>"><?= esc_html($hint) ?></small><?php endif; ?>
</div>
