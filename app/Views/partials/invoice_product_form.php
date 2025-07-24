<?php
// This partial is ONLY for the special N:M relationship between Facturi and Produse
$block = $form_data[0]; // We only expect one block of data here
$rel = $block['relationship'];
$child_entity = $block['other_entity'];
$rel_id = $rel['id'];
$child_label = htmlspecialchars($child_entity['name'] ?? 'Related Record');
$child_slug = $child_entity['slug'];
$options = $block['options'];
$existing = $block['existing'];
$fields = $block['fields'];
?>

<!-- Headers for the product list -->
<div class="row mb-1 text-muted small d-none d-md-flex">
    <div class="col-md-4">Produs</div>
    <div class="col-md-2">Preț Unitar</div>
    <div class="col-md-2">Cantitate</div>
    <div class="col-md-2">Total Linie</div>
</div>

<div id="rel_<?php echo $rel_id; ?>_list">
    <?php foreach ($existing as $i => $item): ?>
        <div class="row mb-2 rel-row align-items-center" data-record-id="<?= $item['id'] ?>" data-price="<?= $item['price'] ?? 0 ?>">
            <div class="col-md-4">
                <input type="hidden" name="relationships[<?php echo $rel_id; ?>][<?php echo $i; ?>][record_id]" value="<?php echo $item['id']; ?>">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($item['summary']); ?>" readonly>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control unit-price" value="<?= number_format($item['price'] ?? 0, 2) ?>" readonly>
            </div>
            <?php foreach ($fields as $field): // This will loop for 'cantitate' ?>
                <div class="col-md-2">
                    <input
                        type="<?php echo $field['field_type']; ?>"
                        name="relationships[<?php echo $rel_id; ?>][<?php echo $i; ?>][<?php echo $field['meta_key']; ?>]"
                        class="form-control quantity-input"
                        placeholder="<?php echo htmlspecialchars($field['field_label']); ?>"
                        value="<?php echo htmlspecialchars($item['meta'][$field['meta_key']] ?? '1'); ?>"
                        required
                    >
                </div>
            <?php endforeach; ?>
            <div class="col-md-2">
                <input type="text" class="form-control line-total" readonly>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-rel">&times;</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="btn-group mt-2">
    <button type="button" class="btn btn-outline-secondary btn-sm add-rel"
            data-rel="<?php echo $rel_id; ?>"
            data-options='<?php echo htmlspecialchars(json_encode(array_values($options))); ?>'
            data-fields='<?php echo htmlspecialchars(json_encode($fields)); ?>'>
        <i class="bi bi-plus-circle"></i> Adaugă <?php echo $child_label; ?>
    </button>
    <a href="index.php?route=entity&type=<?= $child_slug; ?>&action=form&relationship=<?= $rel_id; ?>&parent_id=<?= htmlspecialchars($record_id ?? 0); ?>"
       class="btn btn-outline-primary btn-sm">
        <i class="bi bi-magic"></i> Creează <?php echo $child_label; ?>
    </a>
</div>
