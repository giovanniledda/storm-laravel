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

Breadcrumbs::for('users.new', function ($trail) {
    $trail->parent('users');
    $trail->push(__('New User'), route('users.create'));
});

Breadcrumbs::for('user', function ($trail, $user) {
    $trail->parent('users');
    $trail->push($user->name, route('users.edit', $user));
});

// User addresses
Breadcrumbs::for('user.addresses', function ($trail, $site) {
    $trail->parent('user', $site);
    $trail->push(__('Addresses'), route('users.addresses.index', ['id' => $site->id]));
});

Breadcrumbs::for('user.addresses.new', function ($trail, $user) {
    $trail->parent('user.addresses', $user);
    $trail->push(__('New Address'), route('users.addresses.create', ['id' => $user->id]));
});

Breadcrumbs::for('user.address', function ($trail, $user, $address) {
    $trail->parent('user.addresses', $user);
    $trail->push($address->street, route('users.addresses.edit', ['user_id' => $user->id, 'address_id' => $address->id,]));
});

// User phones
Breadcrumbs::for('user.phones', function ($trail, $site) {
    $trail->parent('user', $site);
    $trail->push(__('Phones'), route('users.phones.index', ['id' => $site->id]));
});

Breadcrumbs::for('user.phones.new', function ($trail, $user) {
    $trail->parent('user.phones', $user);
    $trail->push(__('New Phone'), route('users.phones.create', ['id' => $user->id]));
});

Breadcrumbs::for('user.phone', function ($trail, $user, $phone) {
    $trail->parent('user.phones', $user);
    $trail->push($phone->phone_number, route('users.phones.confirm.destroy', ['user_id' => $user->id, 'phone_id' => $phone->id,]));
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


/** Sites **/

Breadcrumbs::for('sites', function ($trail) {
    $trail->parent('dashboard');
    $trail->push(__('Dockyards/Sites'), route('sites.index'));
});

Breadcrumbs::for('sites.new', function ($trail) {
    $trail->parent('sites');
    $trail->push(__('New Dockyard/Site'), route('sites.create'));
});

Breadcrumbs::for('site', function ($trail, $site) {
    $trail->parent('sites');
    $trail->push($site->name, route('sites.edit', $site));
});

// Site addresses
Breadcrumbs::for('site.addresses', function ($trail, $site) {
    $trail->parent('site', $site);
    $trail->push(__('Addresses'), route('sites.addresses.index', ['id' => $site->id]));
});

Breadcrumbs::for('site.addresses.new', function ($trail, $site) {
    $trail->parent('site.addresses', $site);
    $trail->push(__('New Address'), route('sites.addresses.create', ['id' => $site->id]));
});

Breadcrumbs::for('site.address', function ($trail, $site, $address) {
    $trail->parent('site.addresses', $site);
    $trail->push($address->street, route('sites.addresses.edit', ['site_id' => $site->id, 'address_id' => $address->id,]));
});