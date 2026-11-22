<?php
require_once __DIR__ . '/core/paths.php';

header('Location: ' . tgh_url('views/public/home.php'), true, 302);
exit;
