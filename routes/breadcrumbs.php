<?php

use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

Breadcrumbs::for('dashboard', function ($trail) {
    $trail->push(__('Dashboard'), route('dashboard'));
});


/** Users **/

Breadcrumbs::for('users', function ($trail) {
    $trail->parent('dashboard');
    $trail->push(__('Users'), route('users.index'));
});

Breadcrumbs::for('user.new', function ($trail) {
    $trail->parent('users');
    $trail->push(__('New User'), route('users.create'));
});

Breadcrumbs::for('user', function ($trail, $user) {
    $trail->parent('users');
    $trail->push($user->name, route('users.edit', $user));
});


/** Roles **/

Breadcrumbs::for('roles', function ($trail) {
    $trail->parent('dashboard');
    $trail->push(__('Roles'), route('roles.index'));
});

Breadcrumbs::for('role.new', function ($trail) {
    $trail->parent('roles');
    $trail->push(__('New Role'), route('roles.create'));
});

Breadcrumbs::for('role', function ($trail, $role) {
    $trail->parent('roles');
    $trail->push($role->name, route('roles.edit', $role));
});


/** Permissions **/

Breadcrumbs::for('permissions', function ($trail) {
    $trail->parent('dashboard');
    $trail->push(__('Permissions'), route('permissions.index'));
});

Breadcrumbs::for('permissions.new', function ($trail) {
    $trail->parent('permissions');
    $trail->push(__('New Permission'), route('permissions.create'));
});

Breadcrumbs::for('permission', function ($trail, $permission) {
    $trail->parent('permissions');
    $trail->push($permission->name, route('permissions.edit', $permission));
});


/** Professions **/

Breadcrumbs::for('professions', function ($trail) {
    $trail->parent('dashboard');
    $trail->push(__('Professions'), route('professions.index'));
});

Breadcrumbs::for('professions.new', function ($trail) {
    $trail->parent('professions');
    $trail->push(__('New Profession'), route('professions.create'));
});

Breadcrumbs::for('profession', function ($trail, $profession) {
    $trail->parent('professions');
    $trail->push($profession->name, route('professions.edit', $profession));
});


/** Task Intervent Types **/

Breadcrumbs::for('task_intervent_types', function ($trail) {
    $trail->parent('dashboard');
    $trail->push(__('Task Intervent Types'), route('task_intervent_types.index'));
});

Breadcrumbs::for('task_intervent_types.new', function ($trail) {
    $trail->parent('task_intervent_types');
    $trail->push(__('New Task Intervent Type'), route('task_intervent_types.create'));
});

Breadcrumbs::for('task_intervent_type', function ($trail, $interv_type) {
    $trail->parent('task_intervent_types');
    $trail->push($interv_type->name, route('task_intervent_types.edit', $interv_type));
});