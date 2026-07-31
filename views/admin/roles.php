<?php $role = $editing ?: ['id'=>'','name'=>'','slug'=>'','description'=>'','is_system'=>0,'is_super'=>0]; ?>
<header class="admin-page-header">
    <div><p class="text-sm text-muted">Least-privilege access control</p><h1>Roles & permissions</h1></div>
    <div class="flex flex-wrap gap-2"><a class="button button-secondary" href="<?= e(url('/admin/users')) ?>">Administrators</a><a class="button button-primary" href="<?= e(url('/admin/roles?new=1')) ?>">+ Create role</a></div>
</header>

<div class="admin-split">
    <section class="admin-card">
        <div class="admin-card-header"><h2>Roles</h2><span class="text-xs text-muted"><?= count($roles) ?> total</span></div>
        <div class="mt-4 space-y-2">
            <?php foreach ($roles as $item): ?>
                <?php $manageable = can_manage_role((int) $item['id']); ?>
                <?php if ($manageable): ?><a class="block rounded-xl border p-4 <?= (int) ($role['id'] ?: 0) === (int) $item['id'] ? 'border-berry bg-blush/40' : 'border-line bg-white' ?>" href="<?= e(url('/admin/roles?edit=' . $item['id'])) ?>"><?php else: ?><div class="rounded-xl border border-line bg-slate-50 p-4 opacity-75"><?php endif; ?>
                    <div class="flex items-center gap-2"><p class="font-semibold text-wine"><?= e($item['name']) ?></p><?php if ($item['is_system']): ?><span class="status status-draft ml-auto">System</span><?php endif; ?></div>
                    <p class="mt-2 text-xs text-muted"><?= (int) $item['user_count'] ?> user<?= (int) $item['user_count'] === 1 ? '' : 's' ?> · <?= $item['is_super'] ? 'All permissions' : (int) $item['permission_count'] . ' permissions' ?></p>
                <?php if ($manageable): ?></a><?php else: ?></div><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><div><p class="text-xs font-bold uppercase tracking-[.12em] text-muted"><?= $role['id'] ? 'Edit role' : 'Create role' ?></p><h2 class="mt-1"><?= e($role['name'] ?: 'New role') ?></h2></div><?php if ($role['is_system']): ?><span class="status status-draft">System role</span><?php endif; ?></div>
        <form method="post" action="<?= e(url('/admin/roles')) ?>" class="mt-6 space-y-6" data-unsaved-form>
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($role['id']) ?>">
            <div class="form-field"><label>Role name <span>*</span></label><input class="form-control" name="name" value="<?= e($role['name']) ?>" required></div>
            <div class="form-field"><label>Description</label><textarea class="form-control" name="description"><?= e($role['description']) ?></textarea></div>

            <?php if ($role['is_super']): ?>
                <div class="rounded-xl border border-amber/30 bg-[#FFF7EB] p-5"><p class="font-bold text-wine">Unrestricted system role</p><p class="mt-2 text-sm leading-6 text-muted">Super administrators automatically receive every current and future permission. This cannot be reduced from the role editor.</p></div>
            <?php else: ?>
                <fieldset><legend class="form-section-title"><span>01</span> Permissions</legend>
                    <div class="mt-5 space-y-6">
                        <?php foreach ($permissionGroups as $group => $permissions): ?><div><p class="text-xs font-bold uppercase tracking-[.13em] text-muted"><?= e($group) ?></p><div class="mt-3 grid gap-3 sm:grid-cols-2"><?php foreach ($permissions as $permission): ?><label class="choice-card"><input type="checkbox" name="permission_ids[]" value="<?= (int) $permission['id'] ?>" <?= in_array((int) $permission['id'], $editingPermissionIds, true) ? 'checked' : '' ?> <?= $permission['slug'] === 'dashboard.view' ? 'checked disabled' : '' ?>><span><strong class="block text-wine"><?= e($permission['name']) ?></strong><small class="mt-1 block leading-5 text-muted"><?= e($permission['description']) ?></small></span></label><?php endforeach; ?></div></div><?php endforeach; ?>
                    </div>
                </fieldset>
            <?php endif; ?>

            <div class="admin-savebar"><button class="button button-primary">Save role</button><?php if ($role['id'] && !$role['is_system']): ?><button class="button button-danger" name="action" value="delete" data-confirm="Delete this role? It must not be assigned to any administrator.">Delete role</button><?php endif; ?></div>
        </form>
    </section>
</div>
