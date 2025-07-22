$(document).ready(function () {
    // Event listener for the "Add" button in the N:M relationship section.
    $('.add-rel').on('click', function () {
        // Get the relationship ID from data attribute.
        const relId = $(this).data('rel');
        // The container where rows are appended for this relationship.
        const $container = $('#rel_' + relId + '_list');
        // Get available options for the dropdown (child records).
        const options = $(this).data('options');
        // Get dynamic meta field definitions for this relationship.
        const fields = $(this).data('fields');
        // Determine the current index by counting existing rows.
        const index = $container.find('.rel-row').length;
 
        // Build the HTML for the select dropdown.
        let selectHtml = '<option value="">-- Alege --</option>';
        $.each(options, function (_, opt) {
            selectHtml += `<option value="${opt.id}">${opt.summary || opt.id}</option>`;
        });
 
        // Begin building the row HTML, starting with the dropdown.
        let rowHtml = `
            <div class="row mb-2 rel-row">
                <div class="col-md-5">
                    <select name="relationships[${relId}][${index}][record_id]" class="form-control">
                        ${selectHtml}
                    </select>
                </div>
        `;
 
        // Loop through each meta field definition and add the corresponding input.
        $.each(fields, function (_, field) {
            rowHtml += `
                <div class="col-md-3">
                    <input type="${field.field_type}"
                           name="relationships[${relId}][${index}][${field.meta_key}]"
                           class="form-control"
                           placeholder="${field.field_label}"
                           ${field.is_required ? 'required' : ''}>
                </div>
            `;
        });
 
        // Add the "remove" button to the row.
        rowHtml += `
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-danger btn-sm remove-rel">&times;</button>
                </div>
            </div>
        `;
 
        // Append the new row to the container.
        $container.append(rowHtml);
    });
 
    // Delegate click event to remove any row when its "remove" button is clicked.
    $(document).on('click', '.remove-rel', function () {
        $(this).closest('.rel-row').remove();
    });
});
