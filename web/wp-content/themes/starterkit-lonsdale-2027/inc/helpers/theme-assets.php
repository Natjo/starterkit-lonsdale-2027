<?php

/**
 * Registre les CSS on-demand (split, once et heroes critical).
 *
 * Chemin module = chemin co-localisé sous assets/, ex.:
 *   strates/strate-quizz/strate-quizz → assets/css/strates/strate-quizz/strate-quizz.css
 *   pages/search → assets/css/once/pages/search.css si l'import est marqué once
 *   heros/hero-homepage/hero-homepage → concaténé dans <style id="critical-css"> si hero()
 */
final class ThemeAssets
{
    /** @var array<string, true> */
    private static array $splitModules = [];

    /** @var array<string, true> */
    private static array $renderedSplits = [];

    /** @var array<string, true> */
    private static array $onceModules = [];

    /** @var array<string, true> */
    private static array $renderedOnceModules = [];

    /** @var array<string, true> */
    private static array $heroCriticalQueue = [];

    /** @var array<string, array{href: string, media: string, type: string, imagesrcset: string, imagesizes: string}> */
    private static array $preloadImages = [];

    public static function queueSplit(string $modulePath): void
    {
        $modulePath = self::normalizeModulePath($modulePath);

        if ($modulePath === null) {
            return;
        }

        self::$splitModules[$modulePath] = true;
    }

    public static function queueModule(string $modulePath): void
    {
        $modulePath = self::normalizeModulePath($modulePath);

        if ($modulePath === null) {
            return;
        }

        if (self::getOnceCssPath($modulePath) !== null) {
            self::$onceModules[$modulePath] = true;
            return;
        }

        self::$splitModules[$modulePath] = true;
    }

    public static function queueHeroCritical(string $name): void
    {
        $name = trim($name);

        if ($name === '' || !preg_match('#^[A-Za-z0-9_-]+$#', $name)) {
            return;
        }

        self::$heroCriticalQueue["heros/hero-{$name}/hero-{$name}"] = true;
    }

    /**
     * @return list<string>
     */
    public static function getSplitModules(): array
    {
        return array_keys(self::$splitModules);
    }

    /**
     * @return list<string>
     */
    public static function getOnceModules(): array
    {
        return array_keys(self::$onceModules);
    }

    public static function getModuleCssPath(string $modulePath): ?string
    {
        $modulePath = self::normalizeModulePath($modulePath);

        if ($modulePath === null) {
            return null;
        }

        $file = THEME_DIR . 'assets/css/' . $modulePath . '.css';

        if (!is_readable($file) || filesize($file) === 0) {
            return null;
        }

        return $file;
    }

    public static function getOnceCssPath(string $modulePath): ?string
    {
        $modulePath = self::normalizeModulePath($modulePath);

        if ($modulePath === null) {
            return null;
        }

        $file = THEME_DIR . 'assets/css/once/' . $modulePath . '.css';

        if (!is_readable($file) || filesize($file) === 0) {
            return null;
        }

        return $file;
    }

    public static function renderOnceStyles(): void
    {
        foreach (array_keys(self::$onceModules) as $modulePath) {
            if (isset(self::$renderedOnceModules[$modulePath])) {
                continue;
            }

            $file = self::getOnceCssPath($modulePath);

            if ($file === null) {
                continue;
            }

            $css = file_get_contents($file);

            if ($css === false || trim($css) === '') {
                continue;
            }

            $handle = 'once-' . str_replace('/', '-', $modulePath);
            $css = str_ireplace('</style', '<\/style', $css);

            printf('<style id="%s">%s</style>' . "\n", esc_attr($handle), $css);
            self::$renderedOnceModules[$modulePath] = true;
        }
    }

    public static function renderSplitLinks(): void
    {
        echo self::buildSplitLinksHtml();
    }

    private static function buildSplitLinksHtml(): string
    {
        $html = '';

        foreach (self::getSplitModules() as $modulePath) {
            if (isset(self::$renderedSplits[$modulePath])) {
                continue;
            }

            $file = self::getModuleCssPath($modulePath);

            if ($file === null) {
                continue;
            }

            $handle = 'split-' . str_replace('/', '-', $modulePath);
            $url = THEME_ASSETS . 'css/' . $modulePath . '.css';

            $html .= sprintf(
                '<link rel="stylesheet" id="%s" href="%s?ver=%s" media="all">' . "\n",
                esc_attr($handle),
                esc_url($url),
                esc_attr((string) filemtime($file))
            );

            self::$renderedSplits[$modulePath] = true;
        }

        return $html;
    }

    /**
     * @param array{href?: mixed, media?: mixed, type?: mixed, imagesrcset?: mixed, imagesizes?: mixed} $args
     */
    public static function queuePreloadImage(array $args): void
    {
        $href = isset($args['href']) ? esc_url_raw((string) $args['href']) : '';

        if ($href === '') {
            return;
        }

        $media = isset($args['media']) ? trim((string) $args['media']) : '';
        $type = isset($args['type']) ? trim((string) $args['type']) : '';
        $imagesrcset = isset($args['imagesrcset']) ? trim((string) $args['imagesrcset']) : '';
        $imagesizes = isset($args['imagesizes']) ? trim((string) $args['imagesizes']) : '';
        $key = $href . '|' . $media . '|' . $imagesrcset . '|' . $imagesizes;

        self::$preloadImages[$key] = [
            'href' => $href,
            'media' => $media,
            'type' => $type,
            'imagesrcset' => $imagesrcset,
            'imagesizes' => $imagesizes,
        ];
    }

    /** @var array<string, true> */
    private static array $renderedHeroCriticalModules = [];

    public static function getHeroCriticalCssForHead(): string
    {
        return self::buildHeroCriticalCss();
    }

    /**
     * Output buffer : CSS critique et preloads d’images.
     */
    public static function processBufferedHtml(string $html): string
    {
        $html = self::injectHeroCriticalCss($html);
        return self::injectPreloadImages($html);
    }

    private static function injectHeroCriticalCss(string $html): string
    {
        $heroCss = self::buildHeroCriticalCss();

        if ($heroCss === '') {
            return $html;
        }

        if (preg_match('#<style id="critical-css">.*?</style>#s', $html)) {
            return preg_replace(
                '#(<style id="critical-css">)(.*?)(</style>)#s',
                '$1$2' . $heroCss . '$3',
                $html,
                1
            );
        }

        return str_replace(
            '</head>',
            '<style id="critical-css">' . $heroCss . '</style>' . "\n</head>",
            $html
        );
    }

    private static function injectPreloadImages(string $html): string
    {
        $links = self::buildPreloadImageLinksHtml();

        if ($links === '') {
            return $html;
        }

        if (preg_match('#<style id="critical-css">.*?</style>#s', $html)) {
            return preg_replace(
                '#(<style id="critical-css">.*?</style>)#s',
                '$1' . "\n" . $links,
                $html,
                1
            );
        }

        return str_replace('</head>', $links . '</head>', $html);
    }

    private static function buildPreloadImageLinksHtml(): string
    {
        $html = '';

        foreach (self::$preloadImages as $item) {
            $attrs = sprintf(
                ' rel="preload" as="image" href="%s" fetchpriority="high"',
                esc_url($item['href'])
            );

            if ($item['type'] !== '') {
                $attrs .= sprintf(' type="%s"', esc_attr($item['type']));
            }

            if ($item['media'] !== '') {
                $attrs .= sprintf(' media="%s"', esc_attr($item['media']));
            }

            if ($item['imagesrcset'] !== '') {
                $attrs .= sprintf(' imagesrcset="%s"', esc_attr($item['imagesrcset']));
            }

            if ($item['imagesizes'] !== '') {
                $attrs .= sprintf(' imagesizes="%s"', esc_attr($item['imagesizes']));
            }

            $html .= '<link' . $attrs . '>' . "\n";
        }

        return $html;
    }

    private static function buildHeroCriticalCss(): string
    {
        $chunks = [];

        foreach (array_keys(self::$heroCriticalQueue) as $modulePath) {
            if (isset(self::$renderedHeroCriticalModules[$modulePath])) {
                continue;
            }

            $file = self::getModuleCssPath($modulePath);

            if ($file === null) {
                continue;
            }

            $css = file_get_contents($file);

            if ($css === false || trim($css) === '') {
                continue;
            }

            $chunks[] = $css;
            self::$renderedHeroCriticalModules[$modulePath] = true;
        }

        return implode('', $chunks);
    }

    /**
     * Pré-enregistre explicitement les strates ACF fournies.
     *
     * @param list<array<string, mixed>> $rows
     */
    public static function prescanAcfStrates(array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $layout = isset($row['acf_fc_layout']) ? trim((string) $row['acf_fc_layout']) : '';

            if ($layout === '') {
                continue;
            }

            self::queueModule("strates/strate-{$layout}/strate-{$layout}");
        }
    }

    private static function normalizeModulePath(string $modulePath): ?string
    {
        $modulePath = trim(str_replace('\\', '/', $modulePath), '/');

        if ($modulePath === '' || str_contains($modulePath, '..')) {
            return null;
        }

        if (!preg_match('#^[A-Za-z0-9_/-]+$#', $modulePath)) {
            return null;
        }

        return $modulePath;
    }
}

function theme_queue_module_assets(string $modulePath): void
{
    ThemeAssets::queueModule($modulePath);
    ThemeAssets::renderOnceStyles();
    ThemeAssets::renderSplitLinks();
}

/**
 * Enregistre automatiquement le CSS split associé au template WordPress.
 *
 * Exemple : search.php → assets/css/pages/search.css.
 */
function theme_queue_template_assets(string $template): void
{
    $name = pathinfo($template, PATHINFO_FILENAME);

    if ($name === '' || !preg_match('#^[A-Za-z0-9_-]+$#', $name)) {
        return;
    }

    ThemeAssets::queueModule("pages/{$name}");
}

function theme_queue_hero_critical(string $name): void
{
    ThemeAssets::queueHeroCritical($name);
}

/**
 * @param array<string, mixed> $fields
 */
function theme_prescan_page_assets(array $fields = []): void
{
    theme_prescan_hero_assets($fields);
}

/**
 * @param array{href?: mixed, media?: mixed, type?: mixed, imagesrcset?: mixed, imagesizes?: mixed} $args
 */
function starter_enqueue_preload_image(array $args): void
{
    ThemeAssets::queuePreloadImage($args);
}
