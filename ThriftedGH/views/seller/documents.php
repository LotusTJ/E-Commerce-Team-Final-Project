<?php
require_once __DIR__ . '/../../core/guard.php';
require_once __DIR__ . '/../../controllers/Document.php';

$user = guardRequireLogin();
if ($user['role'] === 'admin') {
    guardDeny(403, 'Administrator accounts do not apply to sell.');
}

$application = application_status_ctr($user['user_id']);
if (!$application) {
    tgh_flash_set('error', 'Submit a seller application first, then add your documents.');
    tgh_redirect(tgh_url('views/seller/apply.php'));
}

$documents = (new DocumentClass())->forApplication($application['application_id']);
$open = $application['status'] === 'pending';
$remaining = DocumentClass::MAX_PER_APPLICATION - count($documents);

tgh_layout_start('Verification documents', 'seller');
tgh_render_flash();
?>
<section class="m3-head">
    <h1>Verification documents</h1>
    <p class="m3-muted">For <strong><?= tgh_e($application['shop_name']) ?></strong>. Only you and the ThriftedGH administrators can open these files.</p>
</section>

<?php if ($open): ?>
    <form class="m3-form" method="post" enctype="multipart/form-data" action="<?= tgh_e(tgh_url('actions/documents/upload.php')) ?>">
        <?= tgh_csrf_field() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= (int)DOCUMENT_MAX_BYTES ?>">

        <label for="doc_type">Document type</label>
        <select id="doc_type" name="doc_type" required>
            <?php foreach (DocumentClass::TYPES as $key => $label): ?>
                <option value="<?= tgh_e($key) ?>"><?= tgh_e($label) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="document">File</label>
        <input type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required <?= $remaining < 1 ? 'disabled' : '' ?>>
        <p class="m3-hint">PDF, JPG or PNG, up to 5 MB each. You can attach <?= (int)DocumentClass::MAX_PER_APPLICATION ?> files and have <?= max(0, $remaining) ?> left.</p>

        <button class="m3-btn" type="submit" <?= $remaining < 1 ? 'disabled' : '' ?>><?= tgh_icon('upload') ?> Upload document</button>
    </form>
<?php else: ?>
    <div class="m3-panel">
        <p>This application was <?= tgh_e($application['status']) ?>, so its documents can no longer be changed.</p>
    </div>
<?php endif; ?>

<section class="m3-section">
    <h2>Uploaded files</h2>
    <?php if (!$documents): ?>
        <div class="m3-panel m3-empty">
            <p>Nothing uploaded yet.</p>
        </div>
    <?php else: ?>
        <ul class="m3-list">
            <?php foreach ($documents as $document): ?>
                <li class="m3-row m3-row-split">
                    <div class="m3-row-body doc-row">
                        <span class="doc-icon"><?= tgh_icon('file') ?></span>
                        <div>
                            <h3><?= tgh_e($document['original_name']) ?></h3>
                            <p class="m3-muted"><?= tgh_e(DocumentClass::TYPES[$document['doc_type']] ?? '') ?> · <?= tgh_e(max(1, (int)round($document['size_bytes'] / 1024))) ?> KB · <?= tgh_e(date('j M Y, g:i A', strtotime($document['uploaded_at']))) ?></p>
                        </div>
                    </div>
                    <div class="m3-row-actions">
                        <a class="m3-btn m3-btn-small m3-btn-ghost" href="<?= tgh_e(tgh_url('actions/documents/download.php')) ?>?id=<?= (int)$document['document_id'] ?>"><?= tgh_icon('download') ?> Download</a>
                        <?php if ($open): ?>
                            <form method="post" action="<?= tgh_e(tgh_url('actions/documents/delete.php')) ?>" class="js-confirm" data-message="Delete this document?">
                                <?= tgh_csrf_field() ?>
                                <input type="hidden" name="document_id" value="<?= (int)$document['document_id'] ?>">
                                <button type="submit" class="m3-btn m3-btn-small m3-btn-danger"><?= tgh_icon('trash') ?> Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<p><a class="m3-btn m3-btn-ghost" href="<?= tgh_e(tgh_url('views/buyer/dashboard.php')) ?>">Back to my account</a></p>
<?php tgh_layout_end(); ?>
