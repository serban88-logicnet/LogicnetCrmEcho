<?php
$company_id = current_company_id();
?>

<?php if (!empty($parentRelationships)): ?>
    <hr>
    <h4 class="mt-4">
        <?= __('related_parents') ?>
        <button class="btn btn-sm btn-link toggle-section" data-target="#parent-section"><?= __('toggle_section') ?></button>
    </h4>

    <div id="parent-section">
        <?php foreach ($parentRelationships as $rel): ?>
            <div class="mb-3">
                <h6 class="mb-1"><?= htmlspecialchars($rel['parent_name']) ?></h6>
                <?php
                    $parent = entity_model()->getParentRecordByRelationship(
                        $record_id,
                        $rel['id'],
                        $company_id
                    );
                ?>
                <?php if ($parent): ?>
                    <div class="d-flex justify-content-between align-items-center border p-2 rounded">
                        <?= htmlspecialchars($parent['summary']) ?>
                        <a href="index.php?route=entity&type=<?= $rel['parent_slug'] ?>&action=view&id=<?= $parent['id'] ?>" class="btn btn-sm btn-outline-secondary">
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

    <div id="child-section">
        <?php foreach ($relationships as $rel): ?>
            <div class="mb-4">
                <h5 class="mb-2"><?= htmlspecialchars($rel['child_name']) ?></h5>

                <a href="index.php?route=entity&type=<?= $rel['child_slug']; ?>&action=form&relationship=<?= $rel['id']; ?>&parent_id=<?= htmlspecialchars($record_id); ?>"
                   class="btn btn-sm btn-outline-primary mb-3">
                    <?= sprintf(__('add_related'), htmlspecialchars($rel['child_name'])) ?>
                </a>

                <?php

                $relatedRecords = entity_model()->getLinkedRecordsByRelationshipId(
                    $record_id,
                    $rel['id'],
                    $company_id
                );
                ?>

                <?php if (!empty($relatedRecords)): ?>
                    <ul class="list-group">
                        <?php foreach ($relatedRecords as $rec): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($rec['summary']); ?>
                                <a href="index.php?route=entity&type=<?= $rel['child_slug'] ?>&action=view&id=<?= $rec['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                    <?= __('view_button'); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted"><?= __('no_related_found'); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

