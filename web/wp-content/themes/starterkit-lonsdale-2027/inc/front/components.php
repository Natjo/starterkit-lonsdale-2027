<?php

/**
 * Normalise le payload associatif d'un composant.
 *
 * Les tableaux positionnels et les valeurs non-tableau ne font pas partie de
 * l'API publique des composants et retournent `null`.
 *
 * @param array<string, mixed> $defaults
 * @return array<string, mixed>|null
 */
function component_args(mixed $params, array $defaults = []): ?array
{
    if (!is_array($params) || array_is_list($params)) {
        return null;
    }

    return array_replace($defaults, $params);
}

function theme_string(mixed $value, string $default = ''): string
{
    return is_scalar($value) || $value instanceof Stringable
        ? trim((string) $value)
        : $default;
}

function component_bool(mixed $value, bool $default = false): bool
{
    return filter_var(
        $value,
        FILTER_VALIDATE_BOOL,
        FILTER_NULL_ON_FAILURE
    ) ?? $default;
}

/**
 * Normalise une valeur en liste stricte.
 *
 * @return list<mixed>
 */
function component_list(mixed $value): array
{
    return is_array($value) && array_is_list($value) ? $value : [];
}

/**
 * Normalise les options communes aux composants de sélection.
 *
 * @return list<array{value: string, label: string, alt: string, selected: bool, disabled: bool}>
 */
function component_options(mixed $options): array
{
    if (!is_array($options)) {
        return [];
    }

    $normalized = [];

    foreach ($options as $option_key => $option) {
        if (is_array($option)) {
            $value = theme_string($option['value'] ?? '');
            $label = theme_string($option['label'] ?? '');
        } else {
            $value = theme_string($option_key);
            $label = theme_string($option);
            $option = [];
        }

        if ($value === '' || $label === '') {
            continue;
        }

        $normalized[] = [
            'value' => $value,
            'label' => $label,
            'alt' => theme_string($option['alt'] ?? ''),
            'selected' => component_bool($option['selected'] ?? false),
            'disabled' => component_bool($option['disabled'] ?? false),
        ];
    }

    return $normalized;
}

function component_id(mixed $requested, string $prefix): string
{
    $id = sanitize_html_class(theme_string($requested));
    if ($id !== '') {
        return $id;
    }

    return function_exists('wp_unique_id')
        ? wp_unique_id($prefix)
        : uniqid($prefix, false);
}

function component_classes(mixed ...$classes): string
{
    $normalized = [];
    $append = static function (mixed $value) use (&$append, &$normalized): void {
        if (is_array($value)) {
            foreach ($value as $item) {
                $append($item);
            }
            return;
        }

        if ((is_scalar($value) && !is_bool($value)) || $value instanceof Stringable) {
            $value = trim((string) $value);
            if ($value !== '') {
                $normalized[] = $value;
            }
        }
    };

    $append($classes);

    return esc_attr(sanitize_class_list(implode(' ', $normalized)));
}

/**
 * @param list<string> $reserved
 */
function component_attributes(mixed $attributes, array $reserved = []): string
{
    $attributes = html_attributes($attributes, $reserved);

    return $attributes !== '' ? ' ' . $attributes : '';
}
