<?php

// Public, read-only archive. Run: php scripts/archive-legacy.php
declare(strict_types=1);
const ORIGIN = 'https://mdarena.in';
$root = dirname(__DIR__).'/storage/app/legacy/mdarena';
foreach (['pages', 'assets', 'discovery'] as $directory) {
    if (! is_dir("$root/$directory")) {
        mkdir("$root/$directory", 0755, true);
    }
}
function saveJson(string $path, mixed $value): void
{
    file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)."\n");
}
function absoluteUrl(string $value, string $base): ?string
{
    $value = trim(html_entity_decode(trim($value), ENT_QUOTES | ENT_HTML5), " \t\n\r\0\x0B\"'");
    if ($value === '' || preg_match('~^(data:|javascript:|mailto:|tel:|#)~i', $value)) {
        return null;
    }
    if (str_starts_with($value, '//')) {
        $value = 'https:'.$value;
    }
    if (! preg_match('~^https?://~i', $value)) {
        $path = parse_url($base, PHP_URL_PATH) ?: '/';
        $value = ORIGIN.(str_starts_with($value, '/') ? $value : substr($path, 0, strrpos($path, '/') + 1).$value);
    }
    $value = explode('#', $value)[0];

    return $value;
}
function internal(string $url): bool
{
    return in_array(strtolower(parse_url($url, PHP_URL_HOST) ?: ''), ['mdarena.in', 'www.mdarena.in'], true);
}
function fetchUrl(string $url): array
{
    $handle = curl_init($url);
    curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5, CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => 90,
        CURLOPT_USERAGENT => 'MAKDigitalArena-ContentArchive/1.0', CURLOPT_ENCODING => '',
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP]);
    if (PHP_OS_FAMILY === 'Windows' && defined('CURLSSLOPT_NATIVE_CA')) {
        curl_setopt($handle, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
    }
    $body = curl_exec($handle);
    $result = ['status' => curl_getinfo($handle, CURLINFO_HTTP_CODE), 'type' => curl_getinfo($handle, CURLINFO_CONTENT_TYPE) ?: '',
        'effective_url' => curl_getinfo($handle, CURLINFO_EFFECTIVE_URL), 'error' => curl_error($handle), 'body' => $body === false ? '' : $body];
    curl_close($handle);

    return $result;
}
function inlineAssetUrls(string $html): array
{
    $html = html_entity_decode(str_replace('\\/', '/', $html), ENT_QUOTES | ENT_HTML5);
    preg_match_all('~https?://(?:www\.)?mdarena\.in/wp-content/uploads/[^\s<>"\x27\\\\]+?\.(?:jpe?g|png|webp|gif|svg|mp4|webm|pdf)~i', $html, $matches);

    return array_values(array_unique($matches[0]));
}
// Recover images embedded in metadata or slider JSON without recrawling pages.
if (in_array('--inline-media', $argv, true)) {
    $records = json_decode(file_get_contents("$root/assets.json"), true, flags: JSON_THROW_ON_ERROR);
    $report = json_decode(file_get_contents("$root/report.json"), true, flags: JSON_THROW_ON_ERROR);
    $known = array_column($records, null, 'url');
    foreach (glob("$root/pages/*.html") as $htmlFile) {
        foreach (inlineAssetUrls(file_get_contents($htmlFile)) as $url) {
            if (isset($known[$url])) {
                continue;
            }
            $response = fetchUrl($url);
            $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            $file = 'assets/'.sha1($url).'.'.$extension;
            if ($response['status'] === 200) {
                file_put_contents("$root/$file", $response['body']);
            } else {
                $report['failures'][] = ['url' => $url, 'kind' => 'asset', 'status' => $response['status']];
            }
            $known[$url] = ['url' => $url, 'status' => $response['status'], 'file' => $response['status'] === 200 ? $file : null, 'bytes' => strlen($response['body'])];
            echo 'Inline media '.$response['status'].": $url\n";
        }
    }
    saveJson("$root/assets.json", array_values($known));
    $report['assets_saved'] = count(array_filter($known, fn ($a) => $a['status'] === 200));
    $report['inline_media_checked_at'] = gmdate(DATE_ATOM);
    saveJson("$root/report.json", $report);
    exit(0);
}
$pages = [];
$assets = [];
$external = [];
$failures = [];
$queue = [ORIGIN.'/'];
$seen = [];
$started = gmdate(DATE_ATOM);
$robots = fetchUrl(ORIGIN.'/robots.txt');
file_put_contents("$root/discovery/robots.txt", $robots['body']);
$sitemaps = [ORIGIN.'/sitemap_index.xml', ORIGIN.'/wp-sitemap.xml'];
preg_match_all('~^Sitemap:\s*(\S+)~mi', $robots['body'], $matches);
$sitemaps = array_merge($sitemaps, $matches[1]);
$checkedMaps = [];
while ($sitemaps) {
    $url = array_shift($sitemaps);
    if (isset($checkedMaps[$url]) || ! internal($url)) {
        continue;
    }
    $checkedMaps[$url] = true;
    $response = fetchUrl($url);
    file_put_contents("$root/discovery/".sha1($url).'.xml', $response['body']);
    if ($response['status'] !== 200) {
        $failures[] = ['url' => $url, 'kind' => 'sitemap', 'status' => $response['status']];

        continue;
    }
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($response['body']);
    if (! $xml) {
        continue;
    }
    foreach ($xml->xpath('//*[local-name()="loc"]') ?: [] as $loc) {
        $link = (string) $loc;
        if (! internal($link)) {
            continue;
        }
        if (str_contains(parse_url($link, PHP_URL_PATH) ?: '', '.xml')) {
            $sitemaps[] = $link;
        } elseif (! preg_match('~\.(jpg|jpeg|png|webp|gif|svg|mp4|pdf)$~i', parse_url($link, PHP_URL_PATH) ?: '')) {
            $queue[] = $link;
        }
    }
}
$addAsset = function (?string $url) use (&$assets, &$external): void {
    if (! $url) {
        return;
    }
    if (! internal($url)) {
        $external[$url] = true;

        return;
    }
    $assets[$url] ??= ['url' => $url];
};
while ($queue && count($seen) < 500) {
    $url = array_shift($queue);
    if (isset($seen[$url]) || ! internal($url)) {
        continue;
    }
    $seen[$url] = true;
    $response = fetchUrl($url);
    if ($response['status'] !== 200 || ! str_contains($response['type'], 'text/html')) {
        $failures[] = ['url' => $url, 'kind' => 'page', 'status' => $response['status'], 'error' => $response['error']];

        continue;
    }
    $id = trim(preg_replace('~[^a-zA-Z0-9-]+~', '-', parse_url($url, PHP_URL_PATH) ?: ''), '-') ?: 'home';
    $id .= '-'.substr(sha1($url), 0, 8);
    file_put_contents("$root/pages/$id.html", $response['body']);
    $doc = new DOMDocument;
    @$doc->loadHTML('<?xml encoding="UTF-8">'.$response['body']);
    $xpath = new DOMXPath($doc);
    $clean = fn (string $text): string => trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    $page = ['url' => $url, 'effective_url' => $response['effective_url'], 'archived_at' => gmdate(DATE_ATOM),
        'title' => $clean($xpath->evaluate('string(//title)')), 'metadata' => [], 'headings' => [], 'links' => [], 'images' => [], 'structured_data' => [], 'html_file' => "pages/$id.html", 'text_file' => "pages/$id.txt"];
    foreach ($xpath->query('//meta[@name or @property]') as $node) {
        $page['metadata'][$node->getAttribute('name') ?: $node->getAttribute('property')] = $node->getAttribute('content');
    }
    foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $node) {
        $page['headings'][] = ['level' => $node->tagName, 'text' => $clean($node->textContent)];
    }
    foreach ($xpath->query('//a[@href]') as $node) {
        $raw = $node->getAttribute('href');
        $link = absoluteUrl($raw, $url);
        $page['links'][] = ['text' => $clean($node->textContent), 'url' => $link ?: $raw];
        if (! $link) {
            continue;
        }
        if (! internal($link)) {
            $external[$link] = true;

            continue;
        }
        $path = parse_url($link, PHP_URL_PATH) ?: '/';
        if (preg_match('~\.(pdf|jpe?g|png|webp|gif|svg|mp4|webm|docx?|xlsx?|zip)$~i', $path)) {
            $addAsset($link);

            continue;
        }
        if (preg_match('~/(wp-admin|wp-json|feed|wp-login\.php)(/|$)~', $path) || parse_url($link, PHP_URL_QUERY)) {
            continue;
        }
        if (! isset($seen[$link])) {
            $queue[] = $link;
        }
    }
    foreach ($xpath->query('//img|//source|//video|//audio|//script[@src]|//link[@href]') as $node) {
        if ($node->tagName === 'link' && ! in_array($node->getAttribute('rel'), ['stylesheet', 'icon', 'shortcut icon', 'apple-touch-icon', 'preload'])) {
            continue;
        }
        foreach (['src', 'data-src', 'data-lazy-src', 'poster', 'href'] as $attr) {
            if ($node->hasAttribute($attr)) {
                $addAsset(absoluteUrl($node->getAttribute($attr), $url));
            }
        }
        foreach (['srcset', 'data-srcset', 'data-lazy-srcset'] as $attr) {
            foreach (explode(',', $node->getAttribute($attr)) as $candidate) {
                $addAsset(absoluteUrl(preg_split('/\s+/', trim($candidate))[0] ?? '', $url));
            }
        }
        if ($node->tagName === 'img') {
            $page['images'][] = ['url' => absoluteUrl($node->getAttribute('data-src') ?: $node->getAttribute('src'), $url), 'alt' => $node->getAttribute('alt')];
        }
    }
    foreach (inlineAssetUrls($response['body']) as $asset) {
        $addAsset($asset);
    }
    $styleText = '';
    foreach ($xpath->query('//style|//*[@style]') as $styleNode) {
        $styleText .= "\n".($styleNode->tagName === 'style' ? $styleNode->textContent : $styleNode->getAttribute('style'));
    }
    preg_match_all('~url\(\s*[\x22\x27]?([^\)\x22\x27]+)~i', $styleText, $css);
    foreach ($css[1] as $asset) {
        $addAsset(absoluteUrl($asset, $url));
    }
    foreach ($xpath->query('//script[@type="application/ld+json"]') as $node) {
        $page['structured_data'][] = json_decode($node->textContent, true) ?? $node->textContent;
    }
    foreach (iterator_to_array($xpath->query('//script|//style|//noscript|//template')) as $node) {
        $node->parentNode->removeChild($node);
    }
    $lines = [];
    foreach ($xpath->query('//body//text()[normalize-space()]') as $node) {
        $line = $clean($node->textContent);
        if ($line !== '') {
            $lines[] = $line;
        }
    }
    $page['text'] = implode("\n", $lines);
    file_put_contents("$root/pages/$id.txt", $page['text']);
    saveJson("$root/pages/$id.json", $page);
    $pages[] = $page;
    echo 'Page '.count($pages).": $url\n";
    usleep(100000);
}
saveJson("$root/content.json", $pages);
$done = [];
do {
    $pending = array_diff(array_keys($assets), array_keys($done));
    foreach ($pending as $url) {
        $done[$url] = true;
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
        $extension = preg_match('/^[a-z0-9]{1,8}$/', $extension) ? $extension : 'bin';
        $file = 'assets/'.sha1($url).'.'.$extension;
        if (is_file("$root/$file")) {
            $response = ['status' => 200, 'type' => '', 'body' => file_get_contents("$root/$file")];
        } else {
            $response = fetchUrl($url);
            if ($response['status'] === 200) {
                file_put_contents("$root/$file", $response['body']);
            }
        }
        $assets[$url] += ['status' => $response['status'], 'file' => $response['status'] === 200 ? $file : null, 'bytes' => strlen($response['body'])];
        if ($response['status'] !== 200) {
            $failures[] = ['url' => $url, 'kind' => 'asset', 'status' => $response['status']];
        }
        if ($extension === 'css' && $response['status'] === 200) {
            preg_match_all('~url\(\s*[\x22\x27]?([^\)\x22\x27]+)~i', $response['body'], $matches);
            foreach ($matches[1] as $asset) {
                $addAsset(absoluteUrl($asset, $url));
            }
        }
        if (count($done) % 25 === 0) {
            echo 'Assets: '.count($done).' / '.count($assets)."\n";
        }
    }
} while (array_diff(array_keys($assets), array_keys($done)));
saveJson("$root/assets.json", array_values($assets));
saveJson("$root/external-links.json", array_keys($external));
$report = ['source' => ORIGIN, 'started_at' => $started, 'finished_at' => gmdate(DATE_ATOM), 'pages_saved' => count($pages),
    'assets_saved' => count(array_filter($assets, fn ($a) => $a['status'] === 200)), 'sitemaps_checked' => array_keys($checkedMaps),
    'remaining_pages' => array_values(array_diff(array_unique($queue), array_keys($seen))), 'failures' => $failures,
    'scope' => 'Public HTML discovered through sitemaps and internal links, plus same-domain referenced assets. External links are inventoried, not downloaded. No private WordPress database, form submissions, or JavaScript-only API data. Original HTML is an archive, not an offline application.'];
saveJson("$root/report.json", $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
