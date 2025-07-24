<h1>
    <?= isset($entity)
        ? __('entity_edit_title')
        : __('entity_add_title') ?>
</h1>

<form method="post" id="entity-def-form">
    <!-- Entity Basic Info -->
    <div class="mb-3">
        <label class="form-label"><?= __('entity_name') ?> *</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($entity['name'] ?? '') ?>">
    </div>

    <!-- ✨ CHANGE: The slug input field has been removed. -->

    <div class="mb-3">
        <label class="form-label"><?= __('entity_description') ?></label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($entity['description'] ?? '') ?></textarea>
    </div>

    <?php if (isset($entity) && !empty($entity['id'])): ?>
        <hr>
        <h5><?= __('entity_relationships_title') ?></h5>
        <div id="relationship-builder-container">
            <!-- Existing relationships will be rendered here by JavaScript -->
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-relationship-btn">
            <i class="bi bi-plus-circle"></i> <?= __('add_relationship_button') ?>
        </button>
        <hr>
    <?php endif; ?>

    <button type="submit" class="btn btn-success"><?= __('save_button') ?></button>
    <a href="index.php?route=entitydef&action=list" class="btn btn-secondary"><?= __('cancel_button') ?></a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const currentEntity = <?= json_encode($entity ?? null) ?>;
    const allEntities = <?= json_encode($allEntities ?? []) ?>;
    const existingRelationships = <?= json_encode($relationships ?? []) ?>;
    const container = document.getElementById('relationship-builder-container');
    const addBtn = document.getElementById('add-relationship-btn');
    const mainForm = document.getElementById('entity-def-form');

    const otherEntities = allEntities.filter(e => e.id !== currentEntity.id);
    let relationshipIndex = 0;

    const createRow = (rel = null) => {
        const index = relationshipIndex++;
        
        const isCurrentEntityOne = rel ? rel.entity_one_id == currentEntity.id : true;
        const otherEntityId = rel ? (isCurrentEntityOne ? rel.entity_two_id : rel.entity_one_id) : null;
        
        const inversionMap = { 'one_many': 'many_one', 'many_one': 'one_many', 'many_many': 'many_many', 'one_one': 'one_one' };
        const storedRelType = rel ? rel.relationship_type : null;
        const displayRelType = rel ? (isCurrentEntityOne ? storedRelType : inversionMap[storedRelType]) : null;
        const otherEntityName = otherEntityId ? allEntities.find(e => e.id == otherEntityId)?.name : '...';

        const row = document.createElement('div');
        row.className = 'card mb-3 relationship-row';
        row.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#collapse-${index}" role="button" style="cursor: pointer;">
                <span>Relație cu <strong class="other-entity-name-header">${otherEntityName}</strong></span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="collapse ${rel ? '' : 'show'}" id="collapse-${index}">
                <div class="card-body">
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close remove-rel-btn" aria-label="Close"></button></div>
                    <p class="mb-2"><strong>Pasul 1:</strong> Alege entitatea asociată.</p>
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary fs-6 me-2">${currentEntity.name}</span>
                        <span>se leagă de</span>
                        <select name="relationships[${index}][other_entity_id]" class="form-select mx-2" style="width: auto;">
                            <option value="">-- Alege --</option>
                            ${otherEntities.map(e => `<option value="${e.id}" ${otherEntityId == e.id ? 'selected' : ''}>${e.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="step-2" style="display: ${otherEntityId ? 'block' : 'none'};">
                        <p class="mb-2"><strong>Pasul 2:</strong> Cum se leagă între ele?</p>
                        <div class="list-group">
                            <label class="list-group-item"><input class="form-check-input me-2" type="radio" name="relationships[${index}][display_type]" value="one_many" ${displayRelType === 'one_many' ? 'checked' : ''}> Un(o) <strong>${currentEntity.name}</strong> are mai multe/mulți <strong><span class="other-entity-name">${otherEntityName}</span></strong>. (1:N)</label>
                            <label class="list-group-item"><input class="form-check-input me-2" type="radio" name="relationships[${index}][display_type]" value="many_one" ${displayRelType === 'many_one' ? 'checked' : ''}> Mai multe/mulți <strong>${currentEntity.name}</strong> aparțin la un(o) <strong><span class="other-entity-name">${otherEntityName}</span></strong>. (N:1)</label>
                            <label class="list-group-item"><input class="form-check-input me-2" type="radio" name="relationships[${index}][display_type]" value="many_many" ${displayRelType === 'many_many' ? 'checked' : ''}> Mai multe/mulți <strong>${currentEntity.name}</strong> se leagă de mai multe/mulți <strong><span class="other-entity-name">${otherEntityName}</span></strong>. (N:M)</label>
                            <label class="list-group-item"><input class="form-check-input me-2" type="radio" name="relationships[${index}][display_type]" value="one_one" ${displayRelType === 'one_one' ? 'checked' : ''}> Un(o) <strong>${currentEntity.name}</strong> se leagă de un(o) singur(ă) <strong><span class="other-entity-name">${otherEntityName}</span></strong>. (1:1)</label>
                        </div>
                    </div>
                    <input type="hidden" name="relationships[${index}][entity_one_id]">
                    <input type="hidden" name="relationships[${index}][entity_two_id]">
                    <input type="hidden" name="relationships[${index}][type]">
                    <input type="hidden" name="relationships[${index}][entity_one_label]" value="${currentEntity.name}">
                    <input type="hidden" name="relationships[${index}][entity_two_label]">
                </div>
                <div class="card-footer text-end">
                     <button type="button" class="btn btn-danger btn-sm remove-rel-btn">Anulează</button>
                     <button type="button" class="btn btn-success btn-sm save-form-btn">Salvează Entitatea</button>
                </div>
            </div>
        `;
        container.appendChild(row);
        if (rel) {
            const radio = row.querySelector(`input[name$="[display_type]"]:checked`);
            if (radio) radio.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    container.addEventListener('change', e => {
        const row = e.target.closest('.relationship-row');
        if (!row) return;

        if (e.target.matches('select[name$="[other_entity_id]"]')) {
            const step2 = row.querySelector('.step-2');
            const otherEntityNameSpans = row.querySelectorAll('.other-entity-name');
            const headerSpan = row.querySelector('.other-entity-name-header');
            const selectedOption = e.target.options[e.target.selectedIndex];
            
            if (e.target.value) {
                const otherEntityName = selectedOption.text;
                otherEntityNameSpans.forEach(span => span.textContent = otherEntityName);
                headerSpan.textContent = otherEntityName;
                row.querySelector('input[name$="[entity_two_label]"]').value = otherEntityName;
                step2.style.display = 'block';
            } else {
                headerSpan.textContent = '...';
                step2.style.display = 'none';
            }
        }

        if (e.target.matches('input[name$="[display_type]"]')) {
            const displayType = e.target.value;
            const otherEntityId = row.querySelector('select[name$="[other_entity_id]"]').value;
            const entityOneInput = row.querySelector('input[name$="[entity_one_id]"]');
            const entityTwoInput = row.querySelector('input[name$="[entity_two_id]"]');
            const storedTypeInput = row.querySelector('input[name$="[type]"]');

            if (displayType === 'many_one') {
                entityOneInput.value = otherEntityId;
                entityTwoInput.value = currentEntity.id;
                storedTypeInput.value = 'one_many';
            } else {
                entityOneInput.value = currentEntity.id;
                entityTwoInput.value = otherEntityId;
                storedTypeInput.value = displayType;
            }
        }
    });

    container.addEventListener('click', e => {
        if (e.target.classList.contains('remove-rel-btn')) e.target.closest('.relationship-row').remove();
        if (e.target.classList.contains('save-form-btn')) mainForm.submit();
    });

    addBtn.addEventListener('click', () => createRow());
    existingRelationships.forEach(rel => createRow(rel));
});
</script>
