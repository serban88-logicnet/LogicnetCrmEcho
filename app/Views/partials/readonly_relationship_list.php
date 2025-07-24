<?php
// This partial is for displaying a simple, read-only list of related records.
// It's used for views like seeing which invoices a product belongs to.
$block = $form_data[0];
$existing = $block['existing'];
$other_entity = $block['other_entity'];
?>

<?php if (!empty($existing)): ?>
    <ul class="list-group">
        <?php foreach ($existing as $item): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= htmlspecialchars($item['summary']) ?></span>
                <a href="index.php?route=entity&type=<?= $other_entity['slug'] ?>&action=view&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-eye"></i> Vizualizează
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="text-muted"><?= __('no_related_found') ?></p>
<?php endif; ?>
