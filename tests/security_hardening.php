<?php
declare(strict_types=1);

// Database-free checks for the HTML sanitizer, CSV round trip, and safe redirects.
require dirname(__DIR__) . '/app/bootstrap.php';

$checks = [];

$dirty = '<h2>Title</h2><p onclick="alert(1)" style="position:fixed">Text <a href="javascript:alert(1)">bad</a> '
    . '<a href="https://example.com" target="_blank">good</a> <a href=" java	script:alert(1)">tab</a></p>'
    . '<script>alert(1)</script><iframe src="https://evil.example"></iframe><meta http-equiv="refresh" content="0;url=https://evil.example">'
    . '<form action="https://evil.example"><input name="x"></form><img src="x" onerror="alert(1)" alt="ok"><custom>kept text</custom>'
    . '<!-- comment --><ul><li>One</li></ul>';
$clean = safe_html($dirty);
$checks['keeps_headings_and_lists'] = str_contains($clean, '<h2>Title</h2>') && str_contains($clean, '<ul><li>One</li></ul>');
$checks['removes_event_handlers'] = !str_contains($clean, 'onclick') && !str_contains($clean, 'onerror');
$checks['removes_inline_styles'] = !str_contains($clean, 'style=');
$checks['removes_javascript_urls'] = !str_contains(strtolower($clean), 'javascript') && !str_contains($clean, 'script:');
$checks['removes_scripts_iframes_meta_forms'] = !preg_match('/<(script|iframe|meta|form|input)\b/i', $clean);
$checks['keeps_safe_links_with_noopener'] = str_contains($clean, 'href="https://example.com"') && str_contains($clean, 'rel="noopener noreferrer"');
$checks['unwraps_unknown_tags'] = str_contains($clean, 'kept text') && !str_contains($clean, '<custom');
$checks['removes_comments'] = !str_contains($clean, 'comment');
$checks['keeps_utf8'] = safe_html('<p>₦500,000 — “quoted”</p>') === '<p>₦500,000 — “quoted”</p>';
$checks['empty_input'] = safe_html(null) === '' && safe_html('   ') === '';

foreach (['+2347000000001', '-negative', '=SUM(A1)', '@handle', 'plain text', "'already quoted"] as $value) {
    $checks['csv_round_trip:' . $value] = csv_import_value(csv_export_value($value)) === $value;
}
$checks['csv_export_guards_formula'] = csv_export_value('=SUM(A1)') === "'=SUM(A1)";

$_SERVER['HTTP_REFERER'] = 'https://evil.example/phish';
$_SERVER['HTTP_HOST'] = parse_url((string) env('APP_URL', 'http://127.0.0.1:8080'), PHP_URL_HOST) ?: 'localhost';
$checks['rejects_external_referer'] = safe_back_url('/') === url('/');
$_SERVER['HTTP_REFERER'] = url('/events');
$checks['accepts_same_site_referer'] = safe_back_url('/') === url('/events');

$checks['hex_normalizes_short_and_lowercase'] = normalize_hex_color('abc') === '#AABBCC' && normalize_hex_color('#6e3345') === '#6E3345';
$checks['hex_rejects_css_injection'] = normalize_hex_color('#123456;}body{display:none') === null && normalize_hex_color('red') === null;
$checks['theme_defaults_cover_palette'] = count(theme_colors()) === 9;
$css = (string) file_get_contents(BASE_PATH . '/public/assets/css/app.css');
$checks['compiled_css_uses_theme_variables'] = str_contains($css, '--color-wine:110 51 69') || str_contains($css, '--color-wine: 110 51 69');
$checks['compiled_css_has_no_hardcoded_wine'] = stripos($css, '110,51,69') === false && stripos($css, '110 51 69 /') === false;
$header = (string) file_get_contents(BASE_PATH . '/views/partials/header.php');
$checks['logo_replaces_site_name'] = (bool) preg_match('/if \(\$logo\):.*?<img.*?else:.*?site_name.*?endif;/s', $header);

$passed = !in_array(false, $checks, true);
echo json_encode(['passed' => $passed, 'checks' => $checks], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
exit($passed ? 0 : 1);
