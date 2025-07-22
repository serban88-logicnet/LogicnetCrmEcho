<?php
// Build child options dropdown excluding the current entity
$optionsHtml = '';
foreach ($allEntities as $ent) {
    if ($ent['id'] == ($entity['id'] ?? 0)) continue;
    $optionsHtml .= '<option value="' . $ent['id'] . '">' . htmlspecialchars($ent['name']) . '</option>';
}
?>

<h1>
    <?= isset($entity)
        ? __('entity_edit_title')
        : __('entity_add_title') ?>
</h1>

<form method="post">
    <!-- Entity Basic Info -->
    <div class="mb-3">
        <label class="form-label"><?= __('entity_name') ?> *</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($entity['name'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label"><?= __('entity_slug') ?> *</label>
        <input type="text" name="slug" class="form-control" required value="<?= htmlspecialchars($entity['slug'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label"><?= __('entity_description') ?></label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($entity['description'] ?? '') ?></textarea>
    </div>

    <?php if (isset($entity) && !empty($entity['id'])): ?>
        <hr>
        <h5><?= __('entity_relationships_title') ?></h5>

        <div id="relationship-builder">
            <?php foreach ($relationships as $i => $rel): ?>
                <div class="row align-items-end mb-3 rel-row">
                    <!-- Type -->
                    <div class="col-md-2">
                        <label class="form-label"><?= __('relationship_type') ?></label>
                        <select name="relationships[<?= $i ?>][type]" class="form-control" required>
                            <option value="1:N" <?= $rel['relationship_type'] === '1:N' ? 'selected' : '' ?>>1:N</option>
                            <option value="N:M" <?= $rel['relationship_type'] === 'N:M' ? 'selected' : '' ?>>N:M</option>
                        </select>
                    </div>

                    <!-- Parent (read-only) -->
                    <input type="hidden" name="relationships[<?= $i ?>][parent_id]" value="<?= $entity['id'] ?>">
                    <div class="col-md-3">
                        <label class="form-label"><?= __('parent_entity') ?></label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($entity['name']) ?>" disabled>
                        <input type="hidden" name="relationships[<?= $i ?>][parent_label]" value="<?= htmlspecialchars($entity['name']) ?>">
                    </div>

                    <!-- Child entity -->
                    <div class="col-md-3">
                        <label class="form-label"><?= __('child_entity') ?></label>
                        <select name="relationships[<?= $i ?>][child_id]" class="form-control" required>
                            <?php foreach ($allEntities as $ent): ?>
                                <?php if ($ent['id'] == $entity['id']) continue; ?>
                                <option value="<?= $ent['id'] ?>" <?= $ent['id'] == $rel['child_entity_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ent['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="relationships[<?= $i ?>][child_label]" value="">
                    </div>

                    <!-- Remove -->
                    <div class="col-md-2 text-end">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm remove-rel">×</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-relationship">
            <?= __('add_relationship_button') ?>
        </button>
        <hr>
    <?php endif; ?>

    <button type="submit" class="btn btn-success"><?= __('save_button') ?></button>
    <a href="index.php?route=entitydef&action=list" class="btn btn-secondary"><?= __('cancel_button') ?></a>
</form>

<script>
    const currentEntityId = <?= (int)($entity['id'] ?? 0) ?>;
    const currentEntityName = "<?= htmlspecialchars($entity['name'] ?? '') ?>";
    const optionsHtml = `<?= $optionsHtml ?>`;

    document.addEventListener('DOMContentLoaded', function () {
        const builder = document.getElementById('relationship-builder');
        const btnAdd = document.getElementById('add-relationship');

        btnAdd?.addEventListener('click', () => {
            const index = builder.querySelectorAll('.rel-row').length;

            const row = document.createElement('div');
            row.className = 'row align-items-end mb-3 rel-row';
            row.innerHTML = `
                <div class="col-md-2">
                    <label class="form-label"><?= __('relationship_type') ?></label>
                    <select name="relationships[${index}][type]" class="form-control" required>
                        <option value="1:N">1:N</option>
                        <option value="N:M">N:M</option>
                    </select>
                </div>

                <input type="hidden" name="relationships[${index}][parent_id]" value="${currentEntityId}">
                <input type="hidden" name="relationships[${index}][parent_label]" value="${currentEntityName}">

                <div class="col-md-3">
                    <label class="form-label"><?= __('parent_entity') ?></label>
                    <input type="text" class="form-control" value="${currentEntityName}" disabled>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= __('child_entity') ?></label>
                    <select name="relationships[${index}][child_id]" class="form-control" required onchange="this.nextElementSibling.value = this.options[this.selectedIndex].text">
                        ${optionsHtml}
                    </select>
                    <input type="hidden" name="relationships[${index}][child_label]" value="">
                </div>

                <div class="col-md-2 text-end">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm remove-rel">×</button>
                </div>
            `;

            builder.appendChild(row);
        });

        builder?.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-rel')) {
                e.target.closest('.rel-row').remove();
            }
        });
    });
</script>
