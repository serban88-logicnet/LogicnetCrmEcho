<?php
// This view now renders the dynamic row/column layout for fields,
// followed by the existing relationship forms.
?>

<h1><?= isset($_GET['id']) ? sprintf(__('edit_title'), htmlspecialchars($entity['name'])) : sprintf(__('create_title'), htmlspecialchars($entity['name'])); ?></h1>

<form method="post" action="" id="entity-form">
    
    <!-- DYNAMIC LAYOUT RENDERING START -->
    <?php if (empty($layout_rows)): ?>
        <div class="alert alert-warning">
            No fields have been configured for this entity's layout.
            <a href="index.php?route=entity-layout&action=editor&entity_id=<?= $entity['id'] ?>">Go to Layout Editor</a>.
        </div>
    <?php else: ?>
        <?php foreach ($layout_rows as $rowName => $row): ?>
            <fieldset class="mb-4">
                <legend class="h5 border-bottom pb-2 mb-3"><?= htmlspecialchars($rowName) ?></legend>
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
                                <div class="mb-3">
                                    <label for="field_<?= $field['custom_field_id'] ?>" class="form-label">
                                        <?= htmlspecialchars($field['field_name']) ?>
                                        <?= !empty($field['is_required']) ? ' <span class="text-danger">*</span>' : '' ?>
                                    </label>
                                    <?php
                                    $fieldName = "fields[{$field['custom_field_id']}]";
                                    $fieldId = "field_{$field['custom_field_id']}";
                                    $required = !empty($field['is_required']) ? 'required' : '';
                                    $value = htmlspecialchars($field['value'] ?? '');
                                    $is_readonly = ($entity['slug'] === 'facturi' && $field['slug'] === 'valoare_totala');

                                    switch ($field['field_type']) {
                                        case 'textarea':
                                            echo "<textarea name='{$fieldName}' id='{$fieldId}' class='form-control' {$required}>{$value}</textarea>";
                                            break;
                                        case 'number':
                                            echo "<input type='number' name='{$fieldName}' id='{$fieldId}' class='form-control' value='{$value}' " . ($is_readonly ? 'readonly' : '') . " {$required}>";
                                            break;
                                        case 'date':
                                            echo "<input type='date' name='{$fieldName}' id='{$fieldId}' class='form-control' value='{$value}' {$required}>";
                                            break;
                                        default: // 'text' and any other type
                                            echo "<input type='text' name='{$fieldName}' id='{$fieldId}' class='form-control' value='{$value}' " . ($is_readonly ? 'readonly' : '') . " {$required}>";
                                            break;
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>
    <!-- DYNAMIC LAYOUT RENDERING END -->

    <!-- EXISTING RELATIONSHIP LOGIC START -->
    <?php if (!empty($relationship_form_data)): ?>
        <hr>
        <h5 class="mt-4">Relații</h5>
        <?php foreach ($relationship_form_data as $block): ?>
            <?php
                $rel = $block['relationship'];
                $other_entity = $block['other_entity'];
                $is_invoice_products = ($entity['slug'] === 'facturi' && $other_entity['slug'] === 'produse');
                $is_product_invoices_readonly = ($entity['slug'] === 'produse' && $other_entity['slug'] === 'facturi');
            ?>
            <div class="card mb-3">
                <div class="card-header">
                    <?= htmlspecialchars($other_entity['name']) ?>
                </div>
                <div class="card-body">
                    <?php if ($is_invoice_products): ?>
                        <?php $form_data = [$block]; require __DIR__ . '/../partials/invoice_product_form.php'; ?>
                    <?php elseif ($is_product_invoices_readonly): ?>
                        <?php $form_data = [$block]; require __DIR__ . '/../partials/readonly_relationship_list.php'; ?>
                    <?php elseif ($block['render_type'] === 'multiple'): ?>
                        <?php $form_data = [$block]; require __DIR__ . '/../partials/nm_relationship_form.php'; ?>
                    <?php else: ?>
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
    <!-- EXISTING RELATIONSHIP LOGIC END -->

    <button type="submit" class="btn btn-primary mt-3">
        <?php echo isset($_GET['id']) ? __('update_button') : __('create_button'); ?>
    </button>
    <a href="index.php?route=entity&type=<?php echo $entity['slug']; ?>&action=list" class="btn btn-secondary mt-3"><?php echo __('cancel_button'); ?></a>
</form>
