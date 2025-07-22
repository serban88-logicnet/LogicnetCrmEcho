<h1><?php echo sprintf(__('details_title'), htmlspecialchars($entity['name'])); ?></h1>

<!-- Display the entity's fields -->
<table class="table table-striped">
    <tbody>
        <?php foreach ($fields as $field): ?>
            <tr>
                <!-- Field label -->
                <th><?php echo htmlspecialchars($field['field_name']); ?></th>

                <!-- Field value -->
                <td>
                    <?php
                    // If it's a relation field and has a value, fetch the related label
                    if ($field['field_type'] === 'relation' && !empty($field['value'])) {
                        $relatedId = $field['value'];
                        $relatedLabel = label_for($relatedId);

                        // Try to figure out the related entity type by querying the record
                        $relatedEntityId = entity_model()->getEntityIdForRecord($relatedId, current_company_id());
                        $relatedEntity = entity_model()->getEntityById($relatedEntityId, current_company_id());

                        if ($relatedEntity) {
                            $relatedSlug = $relatedEntity['slug'];

                            // Make label clickable to related record's view page
                            echo '<a href="index.php?route=entity&type=' . htmlspecialchars($relatedSlug) . '&action=view&id=' . htmlspecialchars($relatedId) . '">';
                            echo htmlspecialchars($relatedLabel);
                            echo '</a>';
                        } else {
                            // fallback if entity not found
                            echo htmlspecialchars($relatedLabel);
                        }

                    } else {
                        // Plain field value
                        echo htmlspecialchars($field['value'] ?? '');
                    }
                    ?>
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

<!-- Include the reusable partial that shows related (child) entities -->
<?php
    $record_id = $_GET['id'] ?? 0;
    require_once __DIR__ . '/../partials/related_records.php';
?>
