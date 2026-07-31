<?php $flashes = pull_flashes(); ?>
<?php if ($flashes): ?>
    <div class="flash-stack" aria-live="polite">
        <?php foreach ($flashes as $item): ?>
            <div class="flash flash-<?= e($item['type']) ?>" data-flash>
                <span><?= e($item['message']) ?></span>
                <button type="button" data-flash-close aria-label="Dismiss message">×</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

