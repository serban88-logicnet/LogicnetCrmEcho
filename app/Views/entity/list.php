<?php
// This view now dynamically builds its table columns based on the new $list_fields variable.
?>

<h1><?= htmlspecialchars($entity['name']) ?></h1>

<a href="index.php?route=entity&type=<?= urlencode($entity['slug']) ?>&action=form" class="btn btn-primary mb-3">
    <?= sprintf(__('add_new'), htmlspecialchars($entity['name'])) ?>
</a>

<?php if (empty($records)): ?>
    <p>No records found for <?= htmlspecialchars($entity['name']) ?>.</p>
<?php else: ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <?php foreach ($list_fields as $field): ?>
                    <th><?= htmlspecialchars($field['field_name']) ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <?php foreach ($list_fields as $field): ?>
                        <td>
                            <?= htmlspecialchars($record['field_values'][$field['id']] ?? '') ?>
                        </td>
                    <?php endforeach; ?>
                    <td>
                        <a href="index.php?route=entity&type=<?= urlencode($entity['slug']) ?>&action=view&id=<?= $record['id'] ?>" class="btn btn-sm btn-info text-white">View</a>
                        <a href="index.php?route=entity&type=<?= urlencode($entity['slug']) ?>&action=form&id=<?= $record['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="index.php?route=entity&type=<?= urlencode($entity['slug']) ?>&action=delete&id=<?= $record['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
