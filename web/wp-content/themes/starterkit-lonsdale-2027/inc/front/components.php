<?php

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
