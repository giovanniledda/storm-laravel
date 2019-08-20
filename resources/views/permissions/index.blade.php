@extends('layouts.app')

@section('title', '| Permissions')

@section('breadcrumbs')
    {{ Breadcrumbs::render('permissions') }}
@endsection

@section('content')

    <div class="col-lg-10 col-lg-offset-1">
        <h1><i class="fa fa-key"></i> Available Permissions
            <a href="{{ route('users.index') }}" class="btn btn-default pull-right">Users</a>
            <a href="{{ route('roles.index') }}" class="btn btn-default pull-right">Roles</a></h1>
        <hr>
        <div class="table-responsive">
            @if(!count($permissions))
                @include('includes/no-results')
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Permissions</th>
                        <th>Operation</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td>
                                <a href="{{ URL::to('permissions/'.$permission->id.'/edit') }}"
                                   class="btn btn-info pull-left" style="margin-right: 3px;">Edit</a>
                                <a href="{{ @route('permissions.confirm.destroy', ['id' => $permission->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <a href="{{ URL::to('permissions/create') }}" class="btn btn-success">Add Permission</a>

    </div>

@endsection