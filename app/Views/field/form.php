<h1>
    <?= isset($field) 
        ? sprintf(__('field_edit_title'), htmlspecialchars($entity['name'])) 
        : sprintf(__('field_add_title'), htmlspecialchars($entity['name'])) ?>
</h1>

<form method="post">
    <div class="mb-3">
        <label class="form-label"><?= __('field_name') ?> *</label>
        <input type="text" name="field_name" class="form-control" required value="<?= htmlspecialchars($field['field_name'] ?? '') ?>">
    </div>

    <!-- ✨ CHANGE: The slug input field has been removed. It's now generated automatically. -->

    <div class="mb-3">
        <label class="form-label"><?= __('field_type') ?> *</label>
        <select name="field_type" class="form-control" required>
            <?php
            $types = [
                'text' => __('field_type_text'),
                'number' => __('field_type_number'),
                'date' => __('field_type_date'),
            ];
            foreach ($types as $key => $label): ?>
                <option value="<?= $key ?>" <?= (isset($field['field_type']) && $field['field_type'] === $key) ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1"
               <?= !empty($field['is_required']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_required">
            <?= __('field_required') ?>
        </label>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_primary_label" id="is_primary_label" value="1"
               <?= !empty($field['is_primary_label']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_primary_label">
            <?= __('field_primary_label') ?>
        </label>
    </div>

    <button type="submit" class="btn btn-success"><?= __('save_button') ?></button>
    <a href="index.php?route=field&type=<?= urlencode($entity['slug']) ?>&action=list" class="btn btn-secondary"><?= __('cancel_button') ?></a>
</form>
