<h1><?= __('entity_list_title') ?></h1>

<a href="index.php?route=entitydef&action=form" class="btn btn-primary mb-3">
    <?= __('entity_add_button') ?>
</a>

<?php if (empty($entities)): ?>
    <p><?= __('entity_none_defined') ?></p>
<?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><?= __('entity_name') ?></th>
                <th><?= __('entity_slug') ?></th>
                <th><?= __('entity_description') ?></th>
                <th><?= __('actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entities as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['name']) ?></td>
                    <td><?= htmlspecialchars($e['slug']) ?></td>
                    <td><?= htmlspecialchars($e['description']) ?></td>
                    <td>
                        <a href="index.php?route=entitydef&action=form&id=<?= $e['id'] ?>" class="btn btn-sm btn-warning">
                            <?= __('edit_button') ?>
                        </a>

                        <a href="index.php?route=field&type=<?= urlencode($e['slug']) ?>&action=list" class="btn btn-sm btn-outline-secondary">
                            <?= __('edit_fields_button') ?>
                        </a>

                        <a href="index.php?route=entitydef&action=delete&id=<?= $e['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?= __('confirm_delete_entity') ?>');">
                            <?= __('delete_button') ?>
                        </a>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
