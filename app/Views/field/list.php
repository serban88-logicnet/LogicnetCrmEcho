<h1><?= sprintf(__('field_list_title'), htmlspecialchars($entity['name'])) ?></h1>

<a href="index.php?route=field&type=<?= urlencode($entity['slug']) ?>&action=add" class="btn btn-primary mb-3">
    <?= __('field_add_button') ?>
</a>

<?php if (empty($fields)): ?>
    <p><?= __('field_none_defined') ?></p>
<?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><?= __('field_name') ?></th>
                <th><?= __('field_slug') ?></th>
                <th><?= __('field_type') ?></th>
                <th><?= __('field_required') ?></th>
                <th><?= __('field_primary_label') ?></th>
                <th><?= __('actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fields as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['field_name']) ?></td>
                    <td><?= htmlspecialchars($f['slug']) ?></td>
                    <td><?= htmlspecialchars($f['field_type']) ?></td>
                    <td><?= $f['is_required'] ? '✔️' : '—' ?></td>
                    <td><?= $f['is_primary_label'] ? '✔️' : '—' ?></td>
                    <td>
                        <a href="index.php?route=field&type=<?= urlencode($entity['slug']) ?>&action=edit&id=<?= $f['id'] ?>" class="btn btn-sm btn-warning"><?= __('edit_button') ?></a>
                        <a href="index.php?route=field&type=<?= urlencode($entity['slug']) ?>&action=delete&id=<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?= __('confirm_delete_field') ?>');"><?= __('delete_button') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
