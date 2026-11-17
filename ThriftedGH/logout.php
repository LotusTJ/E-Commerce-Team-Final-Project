<?php
require_once __DIR__.'/core/session.php';
session_unset();
session_destroy();
header("Location: /thriftedgh/index.php");
exit();
?>