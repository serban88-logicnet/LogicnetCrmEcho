<?php
// This partial is ONLY for displaying the detailed product list on a Factura view page.
$block = $form_data[0]; // We only expect one block of data here
$existing = $block['existing'];
$other_entity = $block['other_entity'];
?>

<?php if (!empty($existing)): ?>
    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr>
                <th>Produs</th>
                <th class="text-end">Cantitate</th>
                <th class="text-end">Preț Unitar</th>
                <th class="text-end">Total Linie</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($existing as $item): ?>
                <?php
                    $quantity = (float)($item['meta']['cantitate'] ?? 0);
                    $price = (float)($item['price'] ?? 0);
                    $line_total = $quantity * $price;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['summary']) ?></td>
                    <td class="text-end"><?= $quantity ?></td>
                    <td class="text-end"><?= number_format($price, 2) ?> Lei</td>
                    <td class="text-end fw-bold"><?= number_format($line_total, 2) ?> Lei</td>
                    <td class="text-center">
                        <a href="index.php?route=entity&type=<?= $other_entity['slug'] ?>&action=view&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-muted"><?= __('no_related_found') ?></p>
<?php endif; ?>
