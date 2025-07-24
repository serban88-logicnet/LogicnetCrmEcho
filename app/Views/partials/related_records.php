<?php
$company_id = current_company_id();
?>

<?php if (!empty($parentRelationships)): ?>
    <hr>
    <h4 class="mt-4">
        <?= __('related_parents') ?>
        <button class="btn btn-sm btn-link toggle-section" data-target="#parent-section"><?= __('toggle_section') ?></button>
    </h4>

    <div id="parent-section" class="collapse show">
        <?php foreach ($parentRelationships as $rel): ?>
            <?php
                $parent_name = ($rel['entity_one_id'] == $entity['id']) ? $rel['entity_two_name'] : $rel['entity_one_name'];
                $parent_slug = ($rel['entity_one_id'] == $entity['id']) ? $rel['entity_two_slug'] : $rel['entity_one_slug'];
                $parent_record = entity_model()->getParentRecordByRelationship($record_id, $rel['id'], $company_id);
            ?>
            <div class="mb-3">
                <h6 class="mb-1"><?= htmlspecialchars($parent_name) ?></h6>
                <?php if ($parent_record): ?>
                    <div class="d-flex justify-content-between align-items-center border p-2 rounded">
                        <span><?= htmlspecialchars($parent_record['summary']) ?></span>
                        <a href="index.php?route=entity&type=<?= $parent_slug ?>&action=view&id=<?= $parent_record['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <?= __('view_button') ?>
                        </a>
                    </div>
                <?php else: ?>
                    <p class="text-muted"><?= __('no_related_found') ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<?php if (!empty($relationships)): ?>
    <hr>
    <h4 class="mt-4">
        <?= __('related_entities') ?>
        <button class="btn btn-sm btn-link toggle-section" data-target="#child-section"><?= __('toggle_section') ?></button>
    </h4>

    <div id="child-section" class="collapse show">
        <?php foreach ($relationships as $rel): ?>
            <?php
                $other_entity_id = ($rel['entity_one_id'] == $entity['id']) ? $rel['entity_two_id'] : $rel['entity_one_id'];
                $other_entity_name = ($rel['entity_one_id'] == $entity['id']) ? $rel['entity_two_name'] : $rel['entity_one_name'];
                $other_entity_slug = ($rel['entity_one_id'] == $entity['id']) ? $rel['entity_two_slug'] : $rel['entity_one_slug'];
                
                // ✨ FIX: Use the enriched data if available, otherwise fetch it
                $relatedRecords = $rel['linked_records'] ?? entity_model()->getLinkedRecordsByRelationshipId($record_id, $rel['id'], $company_id);
                
                $is_invoice_products_view = ($entity['slug'] === 'facturi' && $other_entity_slug === 'produse');
            ?>
            <div class="mb-4">
                <h5 class="mb-2"><?= htmlspecialchars($other_entity_name) ?></h5>

                <a href="index.php?route=entity&type=<?= $entity['slug'] ?>&action=form&id=<?= $record_id ?>" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="bi bi-pencil-square"></i> Gestionează Asocierile
                </a>

                <?php if ($is_invoice_products_view): ?>
                    <?php 
                        $form_data = [[
                            'existing' => $relatedRecords,
                            'other_entity' => ['slug' => $other_entity_slug]
                        ]];
                        require __DIR__ . '/../partials/invoice_product_view.php'; 
                    ?>
                <?php else: ?>
                    <?php if (!empty($relatedRecords)): ?>
                        <ul class="list-group">
                            <?php foreach ($relatedRecords as $rec): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($rec['summary']); ?></span>
                                    <a href="index.php?route=entity&type=<?= $other_entity_slug ?>&action=view&id=<?= $rec['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                        <?= __('view_button'); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted"><?= __('no_related_found'); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
