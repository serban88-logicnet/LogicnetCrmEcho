<h1><?php echo sprintf(__('details_title'), htmlspecialchars($entity['name'])); ?></h1>

<!-- Display the entity's standard fields -->
<table class="table table-striped">
    <tbody>
        <?php foreach ($fields as $field): ?>
            <tr>
                <!-- Field label -->
                <th><?php echo htmlspecialchars($field['field_name']); ?></th>

                <!-- Field value -->
                <td>
                    <!-- ✨ CHANGE: Simplified value display. The complex logic for 'relation' fields is gone. -->
                    <?php echo htmlspecialchars($field['value'] ?? ''); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Action buttons: Edit, Back, Delete -->
<div class="mt-4 mb-3">
    <!-- Edit button -->
    <a href="index.php?route=entity&type=<?php echo $entity['slug']; ?>&action=form&id=<?php echo htmlspecialchars($_GET['id']); ?>" class="btn btn-warning">
        <?php echo __('edit_button'); ?>
    </a>

    <!-- Back to list button -->
    <a href="index.php?route=entity&type=<?php echo $entity['slug']; ?>&action=list" class="btn btn-secondary">
        <?php echo sprintf(__('back_to_all'), htmlspecialchars($entity['name'])); ?>
    </a>

    <!-- Delete button -->
    <a href="index.php?route=entity&type=<?php echo $entity['slug']; ?>&action=delete&id=<?php echo htmlspecialchars($_GET['id']); ?>"
       class="btn btn-danger"
       onclick="return confirm('<?php echo __('delete_confirm'); ?>');">
        <?php echo __('delete_button'); ?>
    </a>
</div>

<!-- This partial now handles displaying ALL related records (parents and children) -->
<?php
    $record_id = $_GET['id'] ?? 0;
    require_once __DIR__ . '/../partials/related_records.php';
?>
