<a href="#"><i class="fas fa-chess-king"></i> Admin menu</a>

<hr />
<h4>Users</h4>
<a href="{{ route('roles.index') }}"><i class="fa fa-btn fa-award"></i> Roles</a>
<a href="{{ route('permissions.index') }}"><i class="fa fa-btn fa-key"></i> Permissions</a>
<a href="{{ route('users.index') }}"><i class="fa fa-btn fa-users"></i> Users</a>

<hr />
<h4>Storm entities</h4>
<a href="{{ route('sites.index') }}"><i class="fa fa-btn fa-anchor"></i> Dockyards/sites</a>
<a href="{{ route('professions.index') }}"><i class="fa fa-btn fa-user-tie"></i> Professions</a>
<a href="{{ route('task_intervent_types.index') }}"><i class="fa fa-btn fa-hammer"></i> Intervent types</a>

@if(Route::has('docs-gen-be-index'))
    <hr />
    <h4>Reports</h4>
    <a href="{{ route('docs-gen-be-index') }}"><i class="fa fa-btn fa-download"></i> Test your report files!</a>
@endif

@if(Route::has('env-measure-be-index'))
    <hr />
    <h4>Environmental Measures</h4>
    <a href="{{ route('env-measure-be-index') }}"><i class="fa fa-btn fa-download"></i> Test it!</a>
@endif
