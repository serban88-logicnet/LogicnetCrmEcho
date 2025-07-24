<h1><?php echo $record_id ? sprintf(__('edit_title'), htmlspecialchars($entity['name'])) : sprintf(__('create_title'), htmlspecialchars($entity['name'])); ?></h1>

<form method="post" action="" id="entity-form">
    <?php foreach ($fields as $field): ?>
        <div class="mb-3">
            <label class="form-label" for="field_<?php echo $field['field_id']; ?>">
                <?php echo htmlspecialchars($field['field_name']); ?>
            </label>
            
            <?php
                $is_readonly = ($entity['slug'] === 'facturi' && $field['slug'] === 'valoare_totala');
            ?>

            <input 
                type="<?php echo htmlspecialchars($field['field_type']); ?>" 
                id="field_<?php echo $field['field_id']; ?>"
                name="fields[<?php echo $field['field_id']; ?>]"
                class="form-control"
                value="<?php echo htmlspecialchars($field['value'] ?? ''); ?>"
                <?php if ($is_readonly) echo 'readonly'; ?>
                <?php if ($field['slug'] === 'valoare_totala') echo ' data-total-field="true"'; ?>
                <?php echo $field['is_required'] ? 'required' : ''; ?>
            >
        </div>
    <?php endforeach; ?>
    
    <?php if (!empty($relationship_form_data)): ?>
        <hr>
        <h5 class="mt-4">Relații</h5>
        <?php foreach ($relationship_form_data as $block): ?>
            <?php
                $rel = $block['relationship'];
                $other_entity = $block['other_entity'];
                $is_invoice_products = ($entity['slug'] === 'facturi' && $other_entity['slug'] === 'produse');
                // ✨ NEW: Special check for the read-only view from the product side
                $is_product_invoices_readonly = ($entity['slug'] === 'produse' && $other_entity['slug'] === 'facturi');
            ?>
            <div class="card mb-3">
                <div class="card-header">
                    <?= htmlspecialchars($other_entity['name']) ?>
                </div>
                <div class="card-body">
                    <?php if ($is_invoice_products): ?>
                        <!-- Use the dedicated invoice partial -->
                        <?php 
                            $form_data = [$block];
                            require __DIR__ . '/../partials/invoice_product_form.php'; 
                        ?>
                    <?php elseif ($is_product_invoices_readonly): ?>
                        <!-- ✨ NEW: Use the new read-only partial for this specific view -->
                        <?php 
                            $form_data = [$block];
                            require __DIR__ . '/../partials/readonly_relationship_list.php'; 
                        ?>
                    <?php elseif ($block['render_type'] === 'multiple'): ?>
                        <!-- Use the generic multi-add partial for other N:M or 1:N relationships -->
                        <?php 
                            $form_data = [$block];
                            require __DIR__ . '/../partials/nm_relationship_form.php'; 
                        ?>
                    <?php else: ?>
                        <!-- Use a simple dropdown for 1:1 and N:1 relationships -->
                        <div class="input-group">
                            <select name="relationships[<?= $rel['id'] ?>][record_id]" class="form-control"
                                <?php if($rel['relationship_type'] === 'one_one'): ?>
                                    data-rel-type="one_one"
                                    data-options='<?= htmlspecialchars(json_encode(array_values($block['all_options_for_js']))) ?>'
                                <?php endif; ?>
                            >
                                <option value="">-- Niciunul --</option>
                                <?php 
                                    if ($block['current_link']) {
                                        echo "<option value='{$block['current_link']['id']}' selected>{$block['current_link']['summary']}</option>";
                                    }
                                    foreach ($block['options'] as $opt) {
                                        echo "<option value='{$opt['id']}'>{$opt['summary']}</option>";
                                    }
                                ?>
                            </select>
                            <a href="index.php?route=entity&type=<?= $other_entity['slug']; ?>&action=form&relationship=<?= $rel['id']; ?>&parent_id=<?= htmlspecialchars($record_id ?? 0); ?>"
                               class="btn btn-outline-primary">
                                <i class="bi bi-magic"></i> Creează Nou
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary mt-3">
        <?php echo $record_id ? __('update_button') : __('create_button'); ?>
    </button>
    <a href="index.php?route=entity&type=<?php echo $entity['slug']; ?>&action=list" class="btn btn-secondary mt-3"><?php echo __('cancel_button'); ?></a>
</form>
