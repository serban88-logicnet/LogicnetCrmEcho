<?php
// This view now renders fields with labels stacked on top of values.
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= htmlspecialchars($entity['name']) ?> Details</h1>
    <div>
        <a href="index.php?route=entity&type=<?= urlencode($entity['slug']) ?>&action=form&id=<?= $_GET['id'] ?>" class="btn btn-warning">
            <?php echo __('edit_button'); ?>
        </a>
        <a href="index.php?route=entity&type=<?= urlencode($entity['slug']) ?>&action=list" class="btn btn-secondary">
            <?php echo sprintf(__('back_to_all'), htmlspecialchars($entity['name'])); ?>
        </a>
        <a href="index.php?route=entity&type=<?php echo $entity['slug']; ?>&action=delete&id=<?php echo htmlspecialchars($_GET['id']); ?>"
           class="btn btn-danger"
           onclick="return confirm('<?php echo __('delete_confirm'); ?>');">
            <?php echo __('delete_button'); ?>
        </a>
    </div>
</div>

<!-- DYNAMIC LAYOUT RENDERING START -->
<?php if (empty($layout_rows)): ?>
    <div class="alert alert-warning">
        No fields have been configured for this entity's layout.
        <a href="index.php?route=entity-layout&action=editor&entity_id=<?= $entity['id'] ?>">Go to Layout Editor</a>.
    </div>
<?php else: ?>
    <?php foreach ($layout_rows as $rowName => $row): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= htmlspecialchars($rowName) ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($row['columns'] as $colIndex => $column): ?>
                        <?php
                            // Determine column width based on the layout type
                            $col_class = 'col-md-12';
                            if ($row['layoutType'] == '2') $col_class = 'col-md-6';
                            if ($row['layoutType'] == '3') $col_class = 'col-md-4';
                            if ($row['layoutType'] == '4') $col_class = 'col-md-3';
                        ?>
                        <div class="<?= $col_class ?>">
                            <?php foreach ($column as $field): ?>
                                <!-- ✅ FIX: Stacked label/value display -->
                                <div class="mb-3">
                                    <label class="form-label text-muted" style="font-size: 0.9rem;"><?= htmlspecialchars($field['field_name']) ?></label>
                                    <div class="bg-light p-2 border rounded">
                                        <?php
                                            $value = htmlspecialchars($field['value'] ?? '');
                                            if (empty(trim($value))) {
                                                echo '<span class="text-muted">N/A</span>';
                                            } else {
                                                echo $value;
                                            }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<!-- DYNAMIC LAYOUT RENDERING END -->


<!-- EXISTING RELATED RECORDS LOGIC START -->
<?php
    $record_id = $_GET['id'] ?? 0;
    // This partial now handles displaying ALL related records (parents and children)
    require_once __DIR__ . '/../partials/related_records.php';
?>
<!-- EXISTING RELATED RECORDS LOGIC END -->
