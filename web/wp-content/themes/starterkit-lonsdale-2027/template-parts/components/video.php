<?php

/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$is_safe_url = static function (mixed $value): bool {
    $url = component_string($value);
    if ($url === '' || preg_match('/[\x00-\x1F\x7F\\\\]/', $url)) {
        return false;
    }

    $parts = parse_url($url);
    if ($parts === false) {
        return false;
    }

    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    return $scheme === '' || in_array($scheme, ['http', 'https'], true);
};
$resolve_url = static function (mixed $value) use ($is_safe_url): string {
    $url = component_string($value);
    if ($url !== '' && ctype_digit($url) && function_exists('wp_get_attachment_url')) {
        $resolved = wp_get_attachment_url((int) $url);
        $url = is_string($resolved) ? trim($resolved) : '';
    }

    return $is_safe_url($url) ? $url : '';
};

$mime_types = [
    'mp4' => 'video/mp4',
    'm4v' => 'video/mp4',
    'webm' => 'video/webm',
    'ogv' => 'video/ogg',
    'ogg' => 'video/ogg',
    'mov' => 'video/quicktime',
];
if (array_key_exists('sources', $params)) {
    $raw_sources = component_list($params['sources']);
} else {
    $raw_sources = [$params['url'] ?? ''];
}
$sources = [];

foreach ($raw_sources as $source) {
    $declared_type = '';
    if (is_array($source)) {
        $declared_type = strtolower(component_string($source['type'] ?? ''));
        $source = $source['src'] ?? '';
    }

    $src = $resolve_url($source);
    if ($src === '' || isset($sources[$src])) {
        continue;
    }

    $extension = strtolower((string) pathinfo((string) parse_url($src, PHP_URL_PATH), PATHINFO_EXTENSION));
    $type = in_array($declared_type, $mime_types, true) ? $declared_type : ($mime_types[$extension] ?? '');
    $sources[$src] = ['src' => $src, 'type' => $type];
}

if ($sources === []) {
    return;
}

$sources = array_values($sources);
$url = $sources[0]['src'];
$host = strtolower((string) parse_url($url, PHP_URL_HOST));
$is_host = static fn(string $host, string $domain): bool => $host === $domain || str_ends_with($host, '.' . $domain);
$is_youtube = $host === 'youtu.be' || $is_host($host, 'youtube.com') || $is_host($host, 'youtube-nocookie.com');
$is_vimeo = $is_host($host, 'vimeo.com');
$type = $is_youtube ? 'youtube' : ($is_vimeo ? 'vimeo' : 'video');

$title = component_string($params['title'] ?? '');
$title = $title !== '' ? $title : 'Lecteur vidéo';
$play_label = component_string($params['play_label'] ?? '');
$play_label = $play_label !== '' ? $play_label : 'Lire la vidéo';
$play_label .= ' : ' . $title;
$autoplay = component_bool($params['autoplay'] ?? false);
$loop = component_bool($params['loop'] ?? false);
$poster = $resolve_url($params['poster'] ?? '');
$is_external_player = $type !== 'video';
$has_facade = $poster !== '';

$idle_src = '';
$autoplay_src = '';
if ($is_youtube) {
    $video_id = youtube_id_from_url($url);
    if ($video_id === '') {
        return;
    }

    $query = ['rel' => 0];
    if ($loop) {
        $query['loop'] = 1;
        $query['playlist'] = $video_id;
    }
    $embed = 'https://www.youtube-nocookie.com/embed/' . rawurlencode($video_id);
    $idle_src = $embed . '?' . http_build_query($query);
    $autoplay_src = $embed . '?' . http_build_query($query + ['autoplay' => 1, 'mute' => 1]);
} elseif ($is_vimeo) {
    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    preg_match_all('/(?:^|\/)(\d+)(?:$|\/)/', $path, $matches);
    $video_id = $matches[1] !== [] ? (string) end($matches[1]) : '';
    if ($video_id === '') {
        return;
    }

    $query = $loop ? ['loop' => 1] : [];
    $embed = 'https://player.vimeo.com/video/' . rawurlencode($video_id);
    $idle_src = $embed . ($query !== [] ? '?' . http_build_query($query) : '');
    $autoplay_src = $embed . '?' . http_build_query($query + ['autoplay' => 1, 'muted' => 1]);
}

$lazy_autoplay = array_key_exists('lazy_autoplay', $params)
    ? component_bool($params['lazy_autoplay'])
    : ($autoplay && !$has_facade && $type === 'video');
$controls = array_key_exists('controls', $params)
    ? component_bool($params['controls'], true)
    : !$lazy_autoplay;
$preload = strtolower(component_string($params['preload'] ?? 'metadata'));
$preload = in_array($preload, ['none', 'metadata', 'auto'], true) ? $preload : 'metadata';
if ($has_facade || $lazy_autoplay) {
    $preload = 'none';
}

$needs_module = $has_facade || $lazy_autoplay || $is_external_player;
$wrapper_attributes = html_attributes([
    'class' => component_classes('video', $params['classes'] ?? ''),
    'data-type' => $type,
    'data-autoplay' => $autoplay ? 'true' : 'false',
    'data-autoplay-src' => $autoplay_src !== '' ? $autoplay_src : null,
    'data-module' => $needs_module ? 'components/video' : null,
    'data-context' => $needs_module && !$has_facade ? '@visible true' : null,
]);
$attributes = component_attributes($params['attributes'] ?? [], [
    'class',
    'data-type',
    'data-autoplay',
    'data-autoplay-src',
    'data-module',
    'data-context',
]);

$tracks = [];
$tracks_input = component_list($params['tracks'] ?? null);
foreach ($tracks_input as $track) {
    if (!is_array($track)) {
        continue;
    }

    $src = $resolve_url($track['src'] ?? '');
    $kind = strtolower(component_string($track['kind'] ?? 'subtitles'));
    if ($src === '' || !in_array($kind, ['subtitles', 'captions', 'descriptions', 'chapters', 'metadata'], true)) {
        continue;
    }

    $tracks[] = [
        'src' => $src,
        'kind' => $kind,
        'srclang' => preg_replace('/[^A-Za-z0-9-]/', '', component_string($track['srclang'] ?? '')) ?? '',
        'label' => component_string($track['label'] ?? ''),
        'default' => component_bool($track['default'] ?? false),
    ];
}
?>
<div <?= $wrapper_attributes ?><?= $attributes ?>>
    <?php if ($type !== 'video') : ?>
        <iframe
            data-src="<?= esc_attr($idle_src) ?>"
            title="<?= esc_attr($title) ?>"
            loading="lazy"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            referrerpolicy="strict-origin-when-cross-origin"
            allowfullscreen<?= $has_facade ? ' tabindex="-1"' : '' ?>></iframe>
    <?php else : ?>
        <video
            title="<?= esc_attr($title) ?>"
            preload="<?= esc_attr($preload) ?>"
            <?= $controls ? 'controls' : '' ?>
            <?= $autoplay && !$lazy_autoplay && !$has_facade ? 'autoplay muted playsinline' : '' ?>
            <?= $lazy_autoplay ? 'muted playsinline data-lazy-autoplay' : '' ?>
            <?= $loop ? 'loop' : '' ?><?= $has_facade ? ' tabindex="-1"' : '' ?>>
            <?php foreach ($sources as $source) : ?>
                <source src="<?= esc_url($source['src']) ?>" <?= $source['type'] !== '' ? ' type="' . esc_attr($source['type']) . '"' : '' ?>>
            <?php endforeach ?>
            <?php foreach ($tracks as $track) : ?>
                <track src="<?= esc_url($track['src']) ?>" kind="<?= esc_attr($track['kind']) ?>" <?= $track['srclang'] !== '' ? ' srclang="' . esc_attr($track['srclang']) . '"' : '' ?><?= $track['label'] !== '' ? ' label="' . esc_attr($track['label']) . '"' : '' ?><?= $track['default'] ? ' default' : '' ?>>
            <?php endforeach ?>
        </video>
    <?php endif ?>

    <?php if ($has_facade) : ?>
        <button type="button" class="poster" aria-label="<?= esc_attr($play_label) ?>">
            <?php if ($poster !== '') : ?>
                <?php component('image', [
                    'image' => $poster,
                    'alt' => '',
                    'lazy' => true,
                    'decoding' => 'async',
                ]); ?>
            <?php endif ?>
            
            <?php component('btn', [
                'element' => "span",
                'name' => 'lire la video',
                'classes' => 'play btn-play',
                'icon' => [
                    'name' => 'play',
                    'width' => 30,
                    'height' => 35,
                ],
            ]); ?>
        </button>
    <?php endif ?>
</div>