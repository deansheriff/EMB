<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$css = file_get_contents(PUBLIC_PATH . '/assets/css/input.css');
$javascript = file_get_contents(PUBLIC_PATH . '/assets/js/app.js');
$header = file_get_contents(BASE_PATH . '/views/partials/header.php');
$footer = file_get_contents(BASE_PATH . '/views/partials/footer.php');

if ($css === false || $javascript === false || $header === false || $footer === false) {
    throw new RuntimeException('Unable to read the scroll contract source files.');
}

$contracts = [
    'hidden_elements_are_removed' => str_contains($css, '[hidden] { display: none !important; }'),
    'closed_chat_ignores_pointer_input' => str_contains($css, '.support-chat-panel { @apply pointer-events-none'),
    'open_chat_accepts_pointer_input' => str_contains($css, '.support-chat-panel.is-open { @apply pointer-events-auto'),
    'closed_chat_is_inert_and_inline_hidden' => str_contains($footer, 'hidden inert style="display:none"')
        && str_contains($javascript, 'panel.inert = false')
        && str_contains($javascript, 'panel.inert = true'),
    'page_horizontal_overflow_is_clipped' => str_contains($css, 'overflow-x: clip'),
    'hero_uses_stable_grid_flow' => str_contains($css, '.hero-shell { @apply relative grid')
        && str_contains($css, 'relative col-start-1 row-start-1 translate-x-8'),
    'inactive_carousel_items_are_inert' => str_contains($javascript, 'slide.inert = i !== index')
        && str_contains($javascript, 'item.inert = i !== current')
        && str_contains($css, '.hero-slide:not(.is-active), .hero-slide:not(.is-active) *')
        && str_contains($css, '.testimonial:not(.is-active), .testimonial:not(.is-active) *'),
    'testimonials_use_stable_grid_flow' => str_contains($css, '.testimonial-stage { @apply relative grid'),
    'menus_do_not_lock_the_body' => !str_contains($javascript, 'classList.toggle("menu-open"')
        && !str_contains($javascript, 'classList.add("menu-open"'),
    'mobile_header_constrains_logo' => str_contains($header, 'max-w-[150px]')
        && str_contains($header, 'hidden min-w-0 sm:block'),
    'assets_are_cache_busted' => preg_match('/[?&]v=\d+$/', asset('js/app.js')) === 1,
];

$failed = array_keys(array_filter($contracts, static fn (bool $passed): bool => !$passed));
if ($failed) {
    throw new RuntimeException('Failed scroll contracts: ' . implode(', ', $failed));
}

echo json_encode(['passed' => true, 'contracts' => $contracts], JSON_PRETTY_PRINT) . PHP_EOL;
