<?php $appointmentType = $editing ?: ['id' => '', 'name' => '', 'description' => '', 'price' => 0, 'currency' => 'NGN', 'sort_order' => count($appointmentTypes) + 1, 'is_active' => 1]; ?>
<header class="admin-page-header">
    <div><p class="text-sm text-muted">Booking catalogue and session fees</p><h1>Appointment types</h1></div>
    <div class="flex flex-wrap gap-2"><a class="button button-secondary" href="<?= e(url('/admin/appointments')) ?>">View appointments</a><a class="button button-primary" href="<?= e(url('/admin/appointment-types?new=1')) ?>">+ New appointment type</a></div>
</header>

<div class="admin-split">
    <section class="space-y-3">
        <?php foreach ($appointmentTypes as $item): ?>
            <a class="admin-list-card <?= (int) ($appointmentType['id'] ?: 0) === (int) $item['id'] ? 'is-active' : '' ?>" href="<?= e(url('/admin/appointment-types?edit=' . $item['id'])) ?>">
                <div class="min-w-0 flex-1"><strong class="block truncate text-wine"><?= e($item['name']) ?></strong><span class="mt-1 block text-xs text-muted"><?= (int) $item['price'] > 0 ? e(format_money((int) $item['price'], $item['currency'])) : 'No fee' ?> · Order <?= (int) $item['sort_order'] ?></span></div>
                <span class="status <?= $item['is_active'] ? 'status-completed' : 'status-draft' ?>"><?= $item['is_active'] ? 'Active' : 'Hidden' ?></span>
            </a>
        <?php endforeach; ?>
        <?php if (!$appointmentTypes): ?><div class="empty-state">No appointment types have been configured.</div><?php endif; ?>
    </section>

    <section class="admin-card self-start">
        <div class="admin-card-header"><div><p class="text-xs font-bold uppercase tracking-[.12em] text-muted"><?= $appointmentType['id'] ? 'Edit appointment type' : 'Create appointment type' ?></p><h2 class="mt-1"><?= e($appointmentType['name'] ?: 'New appointment type') ?></h2></div></div>
        <form method="post" action="<?= e(url('/admin/appointment-types')) ?>" class="mt-6 space-y-6" data-unsaved-form>
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($appointmentType['id']) ?>">
            <div class="form-grid">
                <div class="form-field md:col-span-2"><label>Name <span>*</span></label><input class="form-control" name="name" maxlength="190" value="<?= e($appointmentType['name']) ?>" required></div>
                <div class="form-field md:col-span-2"><label>Description</label><textarea class="form-control min-h-32" name="description" placeholder="Explain who this session is for and what it covers."><?= e($appointmentType['description']) ?></textarea></div>
                <div class="form-field"><label>Price (NGN) <span>*</span></label><input class="form-control" name="price" inputmode="decimal" value="<?= e(number_format((int) $appointmentType['price'] / 100, 2, '.', '')) ?>" placeholder="50000.00" required><p class="field-help">Enter naira, not kobo. Use 0.00 for a free option.</p></div>
                <div class="form-field"><label>Display order</label><input class="form-control" type="number" min="0" max="10000" name="sort_order" value="<?= (int) $appointmentType['sort_order'] ?>"></div>
            </div>
            <label class="consent-row"><input type="checkbox" name="is_active" value="1" <?= $appointmentType['is_active'] ? 'checked' : '' ?>><span>Show this option on the public appointment form</span></label>
            <div class="admin-savebar"><button class="button button-primary" name="action" value="save">Save appointment type</button><?php if ($appointmentType['id']): ?><button class="button button-danger ml-auto" name="action" value="delete" data-confirm="Delete this appointment type? Existing bookings will keep the saved session name and price.">Delete</button><?php endif; ?></div>
        </form>
    </section>
</div>
