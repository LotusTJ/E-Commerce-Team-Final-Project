<?php
require_once __DIR__ . '/paths.php';

function tgh_icon($name, $class = '')
{
    static $cache = [];
    if (!is_string($name) || !preg_match('/^[a-z0-9-]{1,40}$/D', $name)) {
        return '';
    }
    if (!isset($cache[$name])) {
        $file = tgh_root_dir() . '/assets/icons/' . $name . '.svg';
        $cache[$name] = is_file($file) ? trim((string)file_get_contents($file)) : '';
    }
    if ($cache[$name] === '') {
        return '';
    }
    $classes = trim('tg-icon ' . $class);
    return str_replace('<svg ', '<svg class="' . htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true" focusable="false" ', $cache[$name]);
}
