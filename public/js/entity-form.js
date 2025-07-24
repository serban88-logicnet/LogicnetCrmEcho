document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form#entity-form');
    if (!form) return;

    let isAdding = false;

    // ✨ FIX: This function is now robust and calculates totals correctly for both new and existing products.
    const calculateTotal = () => {
        const totalField = form.querySelector('[data-total-field="true"]');
        if (!totalField) return;

        let total = 0;
        const productRows = form.querySelectorAll('.rel-row[data-record-id]');
        
        productRows.forEach(row => {
            const quantityInput = row.querySelector('.quantity-input');
            const quantity = parseFloat(quantityInput.value) || 0;
            
            // The price is now reliably stored on the row's data attribute
            const price = parseFloat(row.dataset.price) || 0;
            const lineTotal = quantity * price;

            const lineTotalEl = row.querySelector('.line-total');
            if (lineTotalEl) {
                lineTotalEl.value = lineTotal.toFixed(2);
            }
            
            total += lineTotal;
        });

        totalField.value = total.toFixed(2);
    };

    form.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-rel')) {
            if (isAdding) return;
            isAdding = true;

            const button = e.target;
            const relId = button.dataset.rel;
            const container = document.getElementById(`rel_${relId}_list`);
            const options = JSON.parse(button.dataset.options);
            const fields = JSON.parse(button.dataset.fields);
            const index = new Date().getTime();

            const existingIds = Array.from(container.querySelectorAll('.rel-row')).map(row => row.dataset.recordId);
            const availableOptions = options.filter(opt => !existingIds.includes(String(opt.id)));

            if (availableOptions.length === 0) {
                alert('Nu mai sunt înregistrări disponibile pentru a fi adăugate.');
                isAdding = false;
                return;
            }

            let selectHtml = '<option value="">-- Alege --</option>';
            availableOptions.forEach(opt => {
                // Embed the price in the option's data attribute
                selectHtml += `<option value="${opt.id}" data-price="${opt.price || 0}">${opt.summary || '#' + opt.id}</option>`;
            });

            let rowHtml = `
                <div class="row mb-2 rel-row align-items-center" data-record-id="" data-price="0">
                    <div class="col-md-4">
                        <select name="relationships[${relId}][${index}][record_id]" class="form-control new-rel-select" required>
                            ${selectHtml}
                        </select>
                    </div>
                    <div class="col-md-2"><input type="text" class="form-control unit-price" readonly placeholder="Preț Unitar"></div>`;

            fields.forEach(field => {
                rowHtml += `
                    <div class="col-md-2">
                        <input type="${field.field_type}"
                               name="relationships[${relId}][${index}][${field.meta_key}]"
                               class="form-control ${field.meta_key === 'cantitate' ? 'quantity-input' : ''}"
                               placeholder="${field.field_label}"
                               value="1">
                    </div>`;
            });

            rowHtml += `
                    <div class="col-md-2"><input type="text" class="form-control line-total" readonly placeholder="Total Linie"></div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-danger btn-sm remove-rel">&times;</button>
                    </div>
                </div>`;
            
            container.insertAdjacentHTML('beforeend', rowHtml);
            setTimeout(() => { isAdding = false; }, 300);
        }

        if (e.target.classList.contains('remove-rel')) {
            e.target.closest('.rel-row').remove();
            calculateTotal();
        }
    });

    form.addEventListener('change', function(e) {
        if (e.target.classList.contains('new-rel-select')) {
            const row = e.target.closest('.rel-row');
            const selectedOption = e.target.options[e.target.selectedIndex];
            const selectedId = e.target.value;
            const price = selectedOption.dataset.price || 0;

            row.dataset.recordId = selectedId;
            row.dataset.price = price;
            row.querySelector('.unit-price').value = parseFloat(price).toFixed(2);
            calculateTotal();
        }

        if (e.target.dataset.relType === 'one_one') {
            const select = e.target;
            const selectedId = select.value;

            if (typeof select.dataset.previousValue === 'undefined') {
                select.dataset.previousValue = document.querySelector(`option[value='${select.value}']`) ? select.value : '';
            }
            
            if (!selectedId) {
                select.dataset.previousValue = '';
                return;
            }

            const options = JSON.parse(select.dataset.options);
            const selectedOption = options.find(opt => opt.id == selectedId);

            if (selectedOption && selectedOption.is_linked) {
                const message = `Înregistrarea "${selectedOption.summary}" este deja asociată în altă parte. Sunteți sigur că doriți să o reasociați? Aceasta va anula legătura existentă.`;
                if (!confirm(message)) {
                    select.value = select.dataset.previousValue;
                    return;
                }
            }
            select.dataset.previousValue = select.value;
        }
    });

    form.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            calculateTotal();
        }
    });

    // Initial calculation on page load
    calculateTotal();
});
