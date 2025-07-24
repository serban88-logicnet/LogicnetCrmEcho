<?php
// This view now includes the "Show on list" checkbox and a drag handle for rows.
?>

<div class="layout-editor-container">
    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light border rounded">
        <h1 class="h3 mb-0">Editing Layout for: <strong><?= htmlspecialchars($entity['name']) ?></strong></h1>
        <button id="save-layout-btn" class="btn btn-primary">Save Layout</button>
    </div>

    <div class="layout-editor-main">
        <aside class="fields-sidebar">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Available Fields</h2>
                <button id="create-field-btn" class="btn btn-sm btn-success">+</button>
            </div>
            <div id="available-fields-container" class="sortable-list">
                <?php foreach ($availableFields as $field): ?>
                    <div class="field-item" data-field-id="<?= $field['field_id'] ?>">
                        <!-- Field content will be rendered by JS -->
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h2 style="margin-top: 2rem;">Trash</h2>
            <div id="trash" class="sortable-list"></div>
        </aside>

        <main class="layout-canvas" id="layout-canvas">
            <!-- Rows will be dynamically inserted here by JavaScript -->
            <button id="add-row-btn" class="btn btn-outline-primary">+</button>
        </main>
    </div>
</div>

<!-- Bootstrap Modal for Creating/Editing Fields -->
<div class="modal fade" id="field-modal" tabindex="-1" aria-labelledby="fieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fieldModalLabel">Create New Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="field-form">
                    <input type="hidden" name="field_id" id="field_id">
                    <div class="mb-3">
                        <label for="field_name" class="form-label">Field Name *</label>
                        <input type="text" name="field_name" id="field_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="field_type" class="form-label">Field Type *</label>
                        <select name="field_type" id="field_type" class="form-select" required>
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="textarea">Text Area</option>
                        </select>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1">
                        <label class="form-check-label" for="is_required">Required</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_primary_label" id="is_primary_label" value="1">
                        <label class="form-check-label" for="is_primary_label">Use as Primary Label</label>
                    </div>
                    <!-- ✅ NEW CHECKBOX -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="show_on_list" id="show_on_list" value="1">
                        <label class="form-check-label" for="show_on_list">Show as column in list view</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="field-form" class="btn btn-primary">Save Field</button>
            </div>
        </div>
    </div>
</div>


<!-- This script block passes PHP data to our external JavaScript file -->
<script>
    window.layoutEditorData = {
        entityId: <?= $entity['id'] ?>,
        layout: <?= json_encode($layout) ?>,
        allFieldsData: new Map([
            <?php foreach (array_merge($availableFields, $layout) as $field): ?>
                <?php $field_id = $field['field_id'] ?? $field['custom_field_id']; ?>
                <?php if ($field_id): ?>
                ['<?= $field_id ?>', {
                    ...<?= json_encode($field) ?>, // Pass the full field object
                    name: '<?= htmlspecialchars($field['field_name'], ENT_QUOTES) ?>',
                    type: '<?= htmlspecialchars($field['field_type'], ENT_QUOTES) ?>'
                }],
                <?php endif; ?>
            <?php endforeach; ?>
        ])
    };
</script>
