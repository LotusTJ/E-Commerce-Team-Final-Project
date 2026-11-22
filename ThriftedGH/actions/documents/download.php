<?php
require_once __DIR__ . '/../../core/guard.php';
require_once __DIR__ . '/../../controllers/Document.php';

$user = guardRequireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$document = $id ? document_for_download_ctr($user, $id) : null;

if (!$document) {
    guardDeny(404, 'That document does not exist or you cannot open it.');
}

$extension = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'][$document['mime_type']] ?? 'bin';
$base = preg_replace('/\.[A-Za-z0-9]{1,5}$/', '', $document['original_name']);
$safe = trim(preg_replace('/[^A-Za-z0-9._ -]+/', '_', (string)$base), ' ._-');
if ($safe === '') {
    $safe = 'document-' . (int)$document['document_id'];
}

header('Content-Type: ' . $document['mime_type']);
header('Content-Length: ' . filesize($document['path']));
header('Content-Disposition: attachment; filename="' . $safe . '.' . $extension . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($document['path']);
exit;
