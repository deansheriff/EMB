<?php $weekdays = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday']; ?>
<header class="admin-page-header">
    <div><p class="text-sm text-muted">Calendar rules and booking capacity</p><h1>Booking availability</h1></div>
    <a class="button button-secondary" href="<?= e(url('/admin/appointments')) ?>">View appointments</a>
</header>

<section class="grid gap-6 xl:grid-cols-[.8fr_1.2fr]">
    <div class="space-y-6">
        <section class="admin-card">
            <div class="admin-card-header"><h2>Booking limits</h2><span class="text-xs text-muted">Applied before a request is accepted</span></div>
            <form method="post" action="<?= e(url('/admin/availability')) ?>" class="mt-6 space-y-5" data-unsaved-form>
                <?= csrf_field() ?><input type="hidden" name="action" value="save_settings">
                <label class="consent-row"><input type="checkbox" name="appointment_booking_enabled" value="1" <?= $availabilitySettings['enabled'] ? 'checked' : '' ?>><span>Accept new appointment bookings</span></label>
                <div class="form-grid">
                    <div class="form-field"><label>Booking window (days)</label><input class="form-control" type="number" min="1" max="365" name="appointment_booking_window_days" value="<?= (int) $availabilitySettings['window_days'] ?>"><p class="field-help">How far into the future clients can book.</p></div>
                    <div class="form-field"><label>Minimum notice (hours)</label><input class="form-control" type="number" min="0" max="720" name="appointment_min_notice_hours" value="<?= (int) $availabilitySettings['notice_hours'] ?>"></div>
                    <div class="form-field md:col-span-2"><label>Maximum bookings per day</label><input class="form-control" type="number" min="1" max="100" name="appointment_daily_limit" value="<?= (int) $availabilitySettings['daily_limit'] ?>"><p class="field-help">This daily ceiling works alongside the limit on each time slot.</p></div>
                </div>
                <button class="button button-primary">Save booking limits</button>
            </form>
        </section>

        <section class="admin-card">
            <div class="admin-card-header"><h2>Block a date</h2><span class="text-xs text-muted">Holidays, leave, and one-off closures</span></div>
            <form method="post" action="<?= e(url('/admin/availability')) ?>" class="mt-6 space-y-4">
                <?= csrf_field() ?><input type="hidden" name="action" value="block_date">
                <div class="form-field"><label>Date</label><input class="form-control" type="date" name="blocked_date" min="<?= e(date('Y-m-d')) ?>" required></div>
                <div class="form-field"><label>Internal reason</label><input class="form-control" name="reason" placeholder="Public holiday or team leave"></div>
                <button class="button button-secondary">Block date</button>
            </form>
            <div class="mt-7 space-y-3 border-t border-line pt-6">
                <?php foreach ($blockedDates as $blocked): ?>
                    <div class="flex flex-wrap items-center gap-3 rounded-xl bg-ivory p-4">
                        <div><p class="font-semibold text-wine"><?= e(format_date($blocked['blocked_date'], 'D, M j, Y')) ?></p><p class="mt-1 text-xs text-muted"><?= e($blocked['reason'] ?: 'No reason supplied') ?></p></div>
                        <form method="post" action="<?= e(url('/admin/availability')) ?>" class="ml-auto"><?= csrf_field() ?><input type="hidden" name="action" value="unblock_date"><input type="hidden" name="id" value="<?= (int) $blocked['id'] ?>"><button class="text-sm font-bold text-red-700" data-confirm="Reopen this date for bookings?">Unblock</button></form>
                    </div>
                <?php endforeach; ?>
                <?php if (!$blockedDates): ?><p class="text-sm text-muted">No upcoming blocked dates.</p><?php endif; ?>
            </div>
        </section>
    </div>

    <section class="admin-card self-start">
        <div class="admin-card-header"><h2>Weekly time slots</h2><span class="text-xs text-muted"><?= count($slots) ?> configured</span></div>
        <p class="mt-3 text-sm leading-6 text-muted">Each slot repeats weekly. Capacity controls how many clients can request the same date and time.</p>
        <div class="mt-6 space-y-3">
            <?php foreach ($slots as $slot): ?>
                <form method="post" action="<?= e(url('/admin/availability')) ?>" class="rounded-xl border border-line bg-white p-4" data-unsaved-form>
                    <?= csrf_field() ?><input type="hidden" name="action" value="save_slot"><input type="hidden" name="id" value="<?= (int) $slot['id'] ?>">
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[1.2fr_1fr_1fr_1fr_auto] xl:items-end">
                        <div class="form-field"><label>Day</label><select class="form-control" name="weekday"><?php foreach ($weekdays as $number => $label): ?><option value="<?= $number ?>" <?= (int) $slot['weekday'] === $number ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                        <div class="form-field"><label>Start</label><input class="form-control" type="time" name="start_time" value="<?= e(substr($slot['start_time'], 0, 5)) ?>" required></div>
                        <div class="form-field"><label>Minutes</label><input class="form-control" type="number" min="15" max="480" step="15" name="duration_minutes" value="<?= (int) $slot['duration_minutes'] ?>"></div>
                        <div class="form-field"><label>Limit</label><input class="form-control" type="number" min="1" max="50" name="capacity" value="<?= (int) $slot['capacity'] ?>"></div>
                        <button class="button button-secondary">Save</button>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3"><label class="consent-row"><input type="checkbox" name="is_active" value="1" <?= $slot['is_active'] ? 'checked' : '' ?>><span>Available to clients</span></label><button class="text-sm font-bold text-red-700" name="action" value="delete_slot" data-confirm="Remove this recurring time slot?">Delete</button></div>
                </form>
            <?php endforeach; ?>
        </div>
        <form method="post" action="<?= e(url('/admin/availability')) ?>" class="mt-6 rounded-xl border border-dashed border-berry/40 bg-blush/30 p-5" data-unsaved-form>
            <?= csrf_field() ?><input type="hidden" name="action" value="save_slot">
            <h3 class="font-display text-xl text-wine">Add a time slot</h3>
            <div class="mt-4 form-grid">
                <div class="form-field"><label>Weekday</label><select class="form-control" name="weekday"><?php foreach ($weekdays as $number => $label): ?><option value="<?= $number ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                <div class="form-field"><label>Start time</label><input class="form-control" type="time" name="start_time" value="10:00" required></div>
                <div class="form-field"><label>Duration (minutes)</label><input class="form-control" type="number" min="15" max="480" step="15" name="duration_minutes" value="60"></div>
                <div class="form-field"><label>Booking limit</label><input class="form-control" type="number" min="1" max="50" name="capacity" value="1"></div>
            </div>
            <label class="consent-row mt-4"><input type="checkbox" name="is_active" value="1" checked><span>Available to clients immediately</span></label>
            <button class="button button-primary mt-5">Add time slot</button>
        </form>
    </section>
</section>
