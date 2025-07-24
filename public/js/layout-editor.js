/**
 * Logic for the integrated entity builder using SortableJS.
 * Manages layout, row reordering, and field properties.
 */
document.addEventListener('DOMContentLoaded', function () {
    // --- DATA AND CONFIG ---
    if (!window.Sortable || !window.layoutEditorData) {
        console.error('CRITICAL: SortableJS library or editor data not found. Aborting.');
        return;
    }

    const { entityId, allFieldsData, layout } = window.layoutEditorData;
    const saveButton = document.getElementById('save-layout-btn');
    const canvas = document.getElementById('layout-canvas');
    const addRowBtn = document.getElementById('add-row-btn');
    const createFieldBtn = document.getElementById('create-field-btn');
    const fieldModalEl = document.getElementById('field-modal');
    const fieldModal = new bootstrap.Modal(fieldModalEl);
    const fieldForm = document.getElementById('field-form');
    let rowCounter = 0;

    // --- FUNCTIONS ---

    /**
     * Renders a field item's HTML.
     */
    function renderFieldItem(fieldId) {
        const fieldData = allFieldsData.get(String(fieldId));
        if (!fieldData) return '';
        return `
            <div class="field-item" data-field-id="${fieldId}">
                <div class="field-item-details">
                    <strong>${fieldData.name}</strong><br>
                    <small>Type: ${fieldData.type}</small>
                </div>
                <div class="field-item-controls">
                    <button class="btn btn-sm btn-outline-secondary edit-field-btn">Edit</button>
                </div>
            </div>
        `;
    }

    /**
     * Creates a new row container.
     */
    function createRow(rowName, layoutType = '1', fieldsByColumn = {}) {
        rowCounter++;
        const rowContainer = document.createElement('div');
        rowContainer.className = 'row-container';
        rowContainer.innerHTML = `
            <div class="row-header">
                <!-- ✅ NEW: Drag handle for reordering rows -->
                <span class="row-drag-handle">⠿</span>
                <input type="text" class="form-control" value="${rowName}" style="font-size: 1.5rem; font-weight: 500; border: none; background: transparent; padding-left: 30px;">
                <div class="row-controls">
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option value="1" ${layoutType == '1' ? 'selected' : ''}>1 Column</option>
                        <option value="2" ${layoutType == '2' ? 'selected' : ''}>2 Columns</option>
                        <option value="3" ${layoutType == '3' ? 'selected' : ''}>3 Columns</option>
                        <option value="4" ${layoutType == '4' ? 'selected' : ''}>4 Columns</option>
                    </select>
                    <button class="delete-row-btn" title="Delete Row">✖</button>
                </div>
            </div>
            <div class="column-layout" data-layout="${layoutType}"></div>`;
        
        canvas.insertBefore(rowContainer, addRowBtn);
        
        const columnLayout = rowContainer.querySelector('.column-layout');
        
        function setupColumns(type, existingFields = {}) {
            columnLayout.innerHTML = '';
            columnLayout.dataset.layout = type;
            for (let i = 0; i < parseInt(type); i++) {
                const colEl = document.createElement('div');
                colEl.className = 'column-dropzone sortable-list';
                colEl.dataset.columnIndex = i;
                if (existingFields[i]) {
                    existingFields[i].forEach(fieldId => colEl.innerHTML += renderFieldItem(fieldId));
                }
                columnLayout.appendChild(colEl);
                new Sortable(colEl, { group: 'shared', animation: 150, ghostClass: 'sortable-ghost' });
            }
        }
        
        setupColumns(layoutType, fieldsByColumn);

        rowContainer.querySelector('select').addEventListener('change', (e) => {
            const newLayoutType = e.target.value;
            const fieldIdsInRow = Array.from(rowContainer.querySelectorAll('.field-item')).map(item => item.dataset.fieldId);
            setupColumns(newLayoutType, { 0: fieldIdsInRow });
        });

        rowContainer.querySelector('.delete-row-btn').addEventListener('click', () => {
            if (confirm(`Are you sure you want to delete this row?`)) {
                rowContainer.querySelectorAll('.field-item').forEach(item => returnFieldToSidebar(item.dataset.fieldId));
                rowContainer.remove();
            }
        });
    }
    
    function returnFieldToSidebar(fieldId) {
        if (document.querySelector(`#available-fields-container .field-item[data-field-id="${fieldId}"]`)) return;
        const container = document.getElementById('available-fields-container');
        container.innerHTML += renderFieldItem(fieldId);
    }

    // --- INITIALIZATION & EVENT BINDING ---

    // ✅ NEW: Initialize the main canvas as a sortable list for rows
    new Sortable(canvas, {
        handle: '.row-drag-handle', // Use the handle to drag rows
        animation: 150,
    });

    new Sortable(document.getElementById('available-fields-container'), {
        group: { name: 'shared', pull: 'clone', put: false },
        sort: false, animation: 150, ghostClass: 'sortable-ghost'
    });
    new Sortable(document.getElementById('trash'), {
        group: 'shared',
        onAdd: function (evt) {
            returnFieldToSidebar(evt.item.dataset.fieldId);
            evt.item.remove();
        }
    });

    const groupedByRow = layout.reduce((acc, field) => {
        const rowName = field.group_name || `Row ${Object.keys(acc).length + 1}`;
        if (!acc[rowName]) {
            acc[rowName] = { layoutType: field.col_span || '1', fieldsByColumn: {} };
        }
        const colIndex = field.col_order || 0;
        if (!acc[rowName].fieldsByColumn[colIndex]) {
            acc[rowName].fieldsByColumn[colIndex] = [];
        }
        acc[rowName].fieldsByColumn[colIndex].push(field.custom_field_id);
        return acc;
    }, {});
    
    Object.entries(groupedByRow).forEach(([rowName, data]) => {
        createRow(rowName, String(data.layoutType), data.fieldsByColumn);
    });
    document.querySelectorAll('#available-fields-container .field-item').forEach(el => {
        el.outerHTML = renderFieldItem(el.dataset.fieldId);
    });

    addRowBtn.addEventListener('click', () => createRow(`Row ${rowCounter + 1}`));

    // --- Field Management Logic ---
    createFieldBtn.addEventListener('click', () => {
        fieldForm.reset();
        document.getElementById('field_id').value = '';
        document.getElementById('fieldModalLabel').textContent = 'Create New Field';
        fieldModal.show();
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-field-btn')) {
            const fieldId = e.target.closest('.field-item').dataset.fieldId;
            const fieldData = allFieldsData.get(fieldId);

            if (fieldData) {
                fieldForm.reset();
                document.getElementById('field_id').value = fieldId;
                document.getElementById('field_name').value = fieldData.name;
                document.getElementById('field_type').value = fieldData.type;
                document.getElementById('is_required').checked = !!parseInt(fieldData.is_required);
                document.getElementById('is_primary_label').checked = !!parseInt(fieldData.is_primary_label);
                document.getElementById('show_on_list').checked = !!parseInt(fieldData.show_on_list); // ✅ POPULATE NEW CHECKBOX
                document.getElementById('fieldModalLabel').textContent = 'Edit Field';
                fieldModal.show();
            }
        }
    });

    fieldForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(fieldForm);
        const fieldId = formData.get('field_id');
        const isCreating = !fieldId;
        
        const data = {
            entity_id: entityId,
            field_id: fieldId,
            field_name: formData.get('field_name'),
            field_type: formData.get('field_type'),
            is_required: formData.get('is_required') === '1',
            is_primary_label: formData.get('is_primary_label') === '1',
            show_on_list: formData.get('show_on_list') === '1', // ✅ SEND NEW PROPERTY
        };

        const url = isCreating ? 'index.php?route=entity-layout&action=createField' : 'index.php?route=entity-layout&action=updateField';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.status === 'success') {
                const newFieldData = isCreating ? result.field : { ...allFieldsData.get(fieldId), ...data };
                // Normalize data structure
                newFieldData.name = newFieldData.field_name;
                newFieldData.type = newFieldData.field_type;
                
                allFieldsData.set(String(newFieldData.field_id), newFieldData);

                if (isCreating) {
                    returnFieldToSidebar(newFieldData.field_id);
                } else {
                    document.querySelectorAll(`.field-item[data-field-id="${fieldId}"]`).forEach(el => {
                        el.outerHTML = renderFieldItem(fieldId);
                    });
                }
                fieldModal.hide();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            alert('An unexpected error occurred.');
        }
    });

    // Save Button click handler
    saveButton.addEventListener('click', function() {
        // ... (This function remains the same)
        saveButton.textContent = 'Saving...';
        saveButton.disabled = true;
        let fullLayoutPayload = [];
        document.querySelectorAll('#layout-canvas .row-container').forEach(rowEl => {
            const rowName = rowEl.querySelector('input[type="text"]').value;
            const layoutType = rowEl.querySelector('select').value;
            rowEl.querySelectorAll('.column-dropzone').forEach((colEl, colIndex) => {
                colEl.querySelectorAll('.field-item').forEach((itemEl, rowIndex) => {
                    fullLayoutPayload.push({
                        custom_field_id: itemEl.dataset.fieldId,
                        group_name: rowName,
                        row_order: rowIndex,
                        col_order: colIndex,
                        col_span: layoutType
                    });
                });
            });
        });
        const postData = { entity_id: entityId, layout: fullLayoutPayload };
        fetch('index.php?route=entity-layout&action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(postData)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.status === 'success' ? 'Layout saved successfully!' : 'Error: ' + data.message);
        })
        .catch(error => console.error('Error:', error))
        .finally(() => {
            saveButton.textContent = 'Save Layout';
            saveButton.disabled = false;
        });
    });
});
