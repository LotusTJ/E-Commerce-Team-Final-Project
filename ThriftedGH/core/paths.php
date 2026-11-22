<?php
function tgh_base()
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $override = getenv('APP_BASE_PATH');
    if (is_string($override) && $override !== '') {
        return $base = '/' . trim($override, '/') . '/';
    }
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    if (preg_match('#^(.*?)/(?:views|actions|payments|scripts)/#', $script, $m)) {
        $root = $m[1];
    } else {
        $root = rtrim(dirname($script), '/');
    }
    return $base = rtrim($root, '/') . '/';
}

function tgh_url($path = '')
{
    return tgh_base() . ltrim((string)$path, '/');
}

function tgh_root_dir()
{
    return dirname(__DIR__);
}
