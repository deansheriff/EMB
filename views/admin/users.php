<?php
$account = $editing ?: ['id'=>'','name'=>'','email'=>'','is_active'=>1,'last_login_at'=>null];
$isSelf = (int) ($account['id'] ?: 0) === (int) auth_user()['id'];
?>
<header class="admin-page-header">
    <div><p class="text-sm text-muted">Role-based administration</p><h1>Administrators</h1></div>
    <div class="flex flex-wrap gap-2"><a class="button button-secondary" href="<?= e(url('/admin/roles')) ?>">Manage roles</a><a class="button button-primary" href="<?= e(url('/admin/users?new=1')) ?>">+ Add administrator</a></div>
</header>

<div class="admin-split">
    <section class="admin-card">
        <div class="admin-card-header"><h2>Accounts</h2><span class="text-xs text-muted"><?= count($users) ?> total</span></div>
        <div class="mt-4 space-y-2">
            <?php foreach ($users as $item): ?>
                <?php $manageable = can_manage_admin((int) $item['id']); ?>
                <?php if ($manageable): ?><a class="block rounded-xl border p-4 <?= (int) ($account['id'] ?: 0) === (int) $item['id'] ? 'border-berry bg-blush/40' : 'border-line bg-white' ?>" href="<?= e(url('/admin/users?edit=' . $item['id'])) ?>"><?php else: ?><div class="block rounded-xl border border-line bg-slate-50 p-4 opacity-75"><?php endif; ?>
                    <div class="flex items-center gap-3"><p class="font-semibold text-wine"><?= e($item['name']) ?></p><span class="status status-<?= $item['is_active'] ? 'completed' : 'cancelled' ?> ml-auto"><?= $item['is_active'] ? 'Active' : 'Inactive' ?></span></div>
                    <p class="mt-1 text-xs text-muted"><?= e($item['email']) ?></p>
                    <p class="mt-2 text-xs font-semibold text-sage"><?= e($item['role_names'] ?: 'No assigned role') ?></p>
                <?php if ($manageable): ?></a><?php else: ?></div><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header">
            <div><p class="text-xs font-bold uppercase tracking-[.12em] text-muted"><?= $account['id'] ? 'Edit administrator' : 'Create administrator' ?></p><h2 class="mt-1"><?= e($account['name'] ?: 'New account') ?></h2></div>
            <?php if ($account['id']): ?><span class="status status-<?= $account['is_active'] ? 'completed' : 'cancelled' ?>"><?= $account['is_active'] ? 'Active' : 'Inactive' ?></span><?php endif; ?>
        </div>
        <form method="post" action="<?= e(url('/admin/users')) ?>" class="mt-6 space-y-6" data-unsaved-form>
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($account['id']) ?>">
            <div class="form-grid">
                <div class="form-field"><label>Full name <span>*</span></label><input class="form-control" name="name" value="<?= e($account['name']) ?>" required autocomplete="name"></div>
                <div class="form-field"><label>Email address <span>*</span></label><input class="form-control" type="email" name="email" value="<?= e($account['email']) ?>" required autocomplete="email"></div>
                <div class="form-field md:col-span-2"><label><?= $account['id'] ? 'New password' : 'Temporary password' ?><?= $account['id'] ? '' : ' *' ?></label><input class="form-control" type="password" name="password" minlength="12" <?= $account['id'] ? '' : 'required' ?> autocomplete="new-password"><p class="field-help"><?= $account['id'] ? 'Leave blank to keep the current password.' : 'Use at least 12 characters and share it through a secure channel.' ?></p></div>
            </div>

            <fieldset>
                <legend class="form-section-title"><span>01</span> Assigned roles</legend>
                <?php if ($isSelf): ?><div class="mt-4 rounded-xl border border-amber/30 bg-[#FFF7EB] p-4 text-sm text-muted">Your own roles and active status are protected here to prevent accidental lockout. Another super administrator can change them.</div><?php endif; ?>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <?php foreach ($roles as $role): ?><label class="choice-card"><input type="checkbox" name="role_ids[]" value="<?= (int) $role['id'] ?>" <?= in_array((int) $role['id'], $editingRoleIds, true) ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?>><span><strong class="block text-wine"><?= e($role['name']) ?></strong><small class="mt-1 block leading-5 text-muted"><?= e($role['description']) ?></small></span></label><?php endforeach; ?>
                </div>
                <?php if ($isSelf): ?><?php foreach ($editingRoleIds as $roleId): ?><input type="hidden" name="role_ids[]" value="<?= (int) $roleId ?>"><?php endforeach; ?><?php endif; ?>
            </fieldset>

            <label class="consent-row"><input type="checkbox" name="is_active" value="1" <?= $account['is_active'] ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?>><span>Account is active and may sign in</span></label>
            <?php if ($isSelf): ?><input type="hidden" name="is_active" value="1"><?php endif; ?>
            <?php if ($account['last_login_at']): ?><p class="text-xs text-muted">Last signed in <?= e(format_date($account['last_login_at'], 'M j, Y · g:i A')) ?></p><?php endif; ?>
            <div class="admin-savebar"><button class="button button-primary"><?= $account['id'] ? 'Save administrator' : 'Create administrator' ?></button></div>
        </form>
    </section>
</div>
