<?php foreach ($form_data as $block): ?>
    <?php
        $rel = $block['relationship'];
        $rel_id = $rel['id'];
        $child_label = $rel['child_label'] ?? 'Înregistrare';
        $options = $block['options'];
        $existing = $block['existing'];
        $fields = $block['fields']; // Will be empty for 1:N
        $is_many_to_many = $rel['relationship_type'] === 'N:M';
    ?>

    <h5 class="mt-5"><?php echo htmlspecialchars($child_label); ?></h5>

    <div id="rel_<?php echo $rel_id; ?>_list">
        <?php foreach ($existing as $i => $item): ?>
            <div class="row mb-2 rel-row">
                <div class="col-md-<?php echo $is_many_to_many ? '5' : '11'; ?>">
                    <select name="relationships[<?php echo $rel_id; ?>][<?php echo $i; ?>][record_id]" class="form-control">
                        <option value="">-- Alege <?php echo htmlspecialchars($child_label); ?> --</option>
                        <?php foreach ($options as $opt): ?>
                            <option value="<?php echo $opt['id']; ?>" <?php echo ($opt['id'] == $item['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($opt['summary'] ?? $opt['id']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($is_many_to_many): ?>
                    <?php foreach ($fields as $field): ?>
                        <div class="col-md-3">
                            <input
                                type="<?php echo $field['field_type']; ?>"
                                name="relationships[<?php echo $rel_id; ?>][<?php echo $i; ?>][<?php echo $field['meta_key']; ?>]"
                                class="form-control"
                                placeholder="<?php echo htmlspecialchars($field['field_label']); ?>"
                                <?php echo $field['is_required'] ? 'required' : ''; ?>
                                value="<?php echo htmlspecialchars($item['meta'][$field['meta_key']] ?? ''); ?>"
                            >
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-danger btn-sm remove-rel">&times;</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="btn btn-outline-primary btn-sm mt-2 add-rel"
            data-rel="<?php echo $rel_id; ?>"
            data-options='<?php echo json_encode($options); ?>'
            data-fields='<?php echo json_encode($fields); ?>'>
        Adaugă <?php echo htmlspecialchars($child_label); ?>
    </button>

    <hr class="my-4">
<?php endforeach; ?>
