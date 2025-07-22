<h1><?php echo sprintf(__('list_title'), htmlspecialchars($entity['name'])); ?></h1>

<a href="index.php?route=entity&type=<?php echo htmlspecialchars($entity['slug']); ?>&action=form" class="btn btn-primary mb-3">
  <?php echo sprintf(__('add_new_button'), htmlspecialchars($entity['name'])); ?>
</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th><?php echo __('record_id'); ?></th>
            <?php
            if (!empty($records)) {
                $headers = [];
                foreach ($records[0]['fields'] as $field) {
                    $headers[$field['slug']] = $field['field_name'];
                }
                foreach ($headers as $slug => $fieldName) {
                    echo "<th>" . htmlspecialchars($fieldName) . "</th>";
                }
            }
            ?>
            <th><?php echo __('actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($records as $record): ?>
        <tr>
            <td><?php echo htmlspecialchars($record['id']); ?></td>
            <?php
            $fieldValues = [];
            foreach ($record['fields'] as $field) {
                $fieldValues[$field['slug']] = $field['value'];
            }
            foreach ($headers as $slug => $fieldName) {
                $value = isset($fieldValues[$slug]) ? $fieldValues[$slug] : '';
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            ?>
            <td>
                <a href="index.php?route=entity&type=<?php echo htmlspecialchars($entity['slug']); ?>&action=view&id=<?php echo htmlspecialchars($record['id']); ?>" class="btn btn-info btn-sm"><?php echo __('view_button'); ?></a>
                <a href="index.php?route=entity&type=<?php echo htmlspecialchars($entity['slug']); ?>&action=form&id=<?php echo htmlspecialchars($record['id']); ?>" class="btn btn-warning btn-sm"><?php echo __('edit_button'); ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
