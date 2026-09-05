<?php

/**
 * Liste des IPs considérées comme internes.
 * Alimentée dynamiquement depuis le champ ACF Options :
 * groupe "ips_lonsdale" > répéteur "items" > champ texte "ip".
 * Le tableau statique ci-dessous sert de fallback si ACF n'est pas disponible.
 */
function getInternalIpsFromAcf(): array
{
    if (!function_exists('get_field')) {
        return [];
    }

    $group = get_field('ips_lonsdale', 'option');
    if (empty($group['items']) || !is_array($group['items'])) {
        return [];
    }

    $ips = [];
    foreach ($group['items'] as $row) {
        $ip = trim($row['ip'] ?? '');
        if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
            $ips[] = $ip;
        }
    }

    return $ips;
}

$INTERNAL_IPS = getInternalIpsFromAcf() ?: [
    '127.0.0.1',        // localhost IPv4
    '::1',              // localhost IPv6
    '192.168.1.1',      // exemple réseau local
    '84.14.248.74',     // Paris - Studio
    '84.14.248.75',     // Paris - Agence
    '100.12.72.38',     // NYC
];

/**
 * Récupère l'adresse IP réelle du visiteur en tenant compte
 * des éventuels proxys / load-balancers (X-Forwarded-For, etc.).
 *
 * @return string L'adresse IP du visiteur.
 */
function getVisitorIp(): string
{
    $headers = [
        'HTTP_CF_CONNECTING_IP',   // Cloudflare
        'HTTP_X_REAL_IP',          // Nginx proxy
        'HTTP_X_FORWARDED_FOR',    // Proxy standard (peut contenir plusieurs IPs)
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR',             // IP directe (fallback)
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            // X-Forwarded-For peut contenir une liste "client, proxy1, proxy2"
            $ip = trim(explode(',', $_SERVER[$header])[0]);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '';
}



/**
 * IP typiques du trafic host → conteneur (Docker Desktop / compose).
 */
function isLocalDockerIp(string $ip): bool
{
    if ($ip === '127.0.0.1' || $ip === '::1') {
        return true;
    }

    // 172.16.0.0/12 (bridge Docker)
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $long = ip2long($ip);
        if ($long !== false && $long >= ip2long('172.16.0.0') && $long <= ip2long('172.31.255.255')) {
            return true;
        }
        // 192.168.65.0/24 — gateway Docker Desktop (Mac/Windows)
        if (str_starts_with($ip, '192.168.65.')) {
            return true;
        }
    }

    return false;
}

function getYoutubeId($code)
{
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $code, $match);
    return $match[1] ?? '';
}

function slugify($string)
{
    static $table = [
        'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj',
        'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c',
        'Ć' => 'C', 'ć' => 'c', 'À' => 'A', 'Á' => 'A',
        'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
        'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I',
        'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O',
        'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
        'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U',
        'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
        'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y',
        'þ' => 'b', 'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r',
        '/' => '-', ' ' => '-', '+' => '', '(' => '',
        ')' => '', '\'' => '-', '&' => '', ' et ' => '-',
        "\u{200B}" => '', ',' => '-', '?' => '-',
    ];

    $string = preg_replace(['/\s{2,}/', '/[\t\n]/'], ' ', $string);
    return strtolower(strtr($string, $table));
}
