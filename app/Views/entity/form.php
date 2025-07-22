<h1><?php echo $record_id ? sprintf(__('edit_title'), htmlspecialchars($entity['name'])) : sprintf(__('create_title'), htmlspecialchars($entity['name'])); ?></h1>

<form method="post" action="">
    <?php foreach ($fields as $field): ?>
        <div class="mb-3">
            <label class="form-label" for="field_<?php echo $field['field_id']; ?>">
                <?php echo htmlspecialchars($field['field_name']); ?>
            </label>

            <?php if ($field['field_type'] === 'relation'): ?>
                <?php
                    // Get related entity records dynamically
                    $relatedRecords = entity_model()->getRelationOptions($entity['id'], $field['slug'], $_SESSION['users']['company_id'] ?? 1);
                    $isPrefilled = !empty($field['prefilled']);
                ?>
                <select 
                    id="field_<?php echo $field['field_id']; ?>" 
                    name="fields[<?php echo $field['field_id']; ?>]" 
                    class="form-control" 
                    <?php echo $field['is_required'] ? 'required' : ''; ?>
                    <?php echo $isPrefilled ? 'disabled' : ''; ?>
                >
                    <option value="">-- Alege --</option>
                    <?php foreach ($relatedRecords as $r): ?>
                        <option value="<?php echo $r['id']; ?>"
                            <?php echo (isset($field['value']) && $field['value'] == $r['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if ($isPrefilled): ?>
                    <input type="hidden" name="fields[<?php echo $field['field_id']; ?>]" value="<?php echo htmlspecialchars($field['value']); ?>">
                <?php endif; ?>

            <?php else: ?>
                <input 
                    type="<?php echo htmlspecialchars($field['field_type']); ?>" 
                    id="field_<?php echo $field['field_id']; ?>"
                    name="fields[<?php echo $field['field_id']; ?>]"
                    class="form-control"
                    value="<?php echo htmlspecialchars($field['value'] ?? ''); ?>"
                    <?php echo $field['is_required'] ? 'required' : ''; ?>
                >
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    
    <!-- N:M Data Adding if needed -->
    <?php require_once __DIR__ . '/../partials/nm_relationship_form.php'; ?>

    <button type="submit" class="btn btn-primary">
        <?php echo $record_id ? __('update_button') : __('create_button'); ?>
    </button>
    <a href="index.php?route=entity&type=<?php echo $entity['slug']; ?>&action=list" class="btn btn-secondary"><?php echo __('cancel_button'); ?></a>
</form>
