<?php
// This partial is for GENERIC N:M or 1:N relationships (e.g., Client -> Facturi)
$block = $form_data[0];
$rel = $block['relationship'];
$child_entity = $block['other_entity'];
$rel_id = $rel['id'];
$child_label = htmlspecialchars($child_entity['name'] ?? 'Related Record');
$child_slug = $child_entity['slug'];
$options = $block['options'];
$existing = $block['existing'];
?>

<div id="rel_<?php echo $rel_id; ?>_list">
    <?php foreach ($existing as $i => $item): ?>
        <div class="row mb-2 rel-row" data-record-id="<?= $item['id'] ?>">
            <div class="col-md-10">
                <input type="hidden" name="relationships[<?php echo $rel_id; ?>][<?php echo $i; ?>][record_id]" value="<?php echo $item['id']; ?>">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($item['summary']); ?>" readonly>
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
            data-fields='[]'>
        <i class="bi bi-plus-circle"></i> Adaugă <?php echo $child_label; ?> existent(ă)
    </button>
    <a href="index.php?route=entity&type=<?= $child_slug; ?>&action=form&relationship=<?= $rel_id; ?>&parent_id=<?= htmlspecialchars($record_id ?? 0); ?>"
       class="btn btn-outline-primary btn-sm">
        <i class="bi bi-magic"></i> Creează <?php echo $child_label; ?> nou(ă)
    </a>
</div>
