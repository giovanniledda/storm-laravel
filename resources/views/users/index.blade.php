@extends('layouts.app')

@section('title', '| Users')

@section('breadcrumbs')
    {{ Breadcrumbs::render('users') }}
@endsection

@section('content')

    <div class="col-lg-10 col-lg-offset-1">
        <h1><i class="fa fa-users"></i> User Administration <a href="{{ route('roles.index') }}"
                                                               class="btn btn-default pull-right">Roles</a>
            <a href="{{ route('permissions.index') }}" class="btn btn-default pull-right">Permissions</a></h1>
        <hr>
        <div class="table-responsive">
            @if(!count($users))
                @include('includes/no-results')
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Name - Surname</th>
                        <th>Email</th>
                        <th>Photo</th>
                        <th>STORM</th>
                        <th>Can login STORM</th>
                        <th>Date/Time Added</th>
                        <th>User Roles</th>
                        <th>Operations</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name . ' ' .$user->surname }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                            @if($user->hasProfilePhoto())
                                <img alt="Profile photo" style="width:100px; border: 1px gray solid;" src="{{ @route('user-photo', ['id' => $user->id]) }}" />
                            @else
                                {{ __('No photo uploaded!') }}
                            @endif
                            </td>
                            <td>@booltostr($user->is_storm_user)</td>
                            <td>@booltostr($user->can_login)</td>
                            <td>{{ $user->created_at->format('F d, Y h:ia') }}</td>
                            <td>{{ $user->roles()->pluck('name')->implode(' ') }}</td>{{-- Retrieve array of roles associated to a user and convert to string --}}
                            <td>
                                <a href="{{ @route('users.phones.index', ['id' => $user->id]) }}" class="btn btn-outline-info pull-left" style="margin-right: 3px;"><i class="fa fa-phone"></i> {{ __('Phones (:phones)', ['phones' => $user->countPhones()]) }}</a>
{{--                                <a href="{{ @route('project_user.index', ['user_id' => $user->id]) }}" class="btn btn-outline-info pull-left" style="margin-right: 3px;"><i class="fa fa-user-tie"></i> {{ __('Projects (:projs)', ['projs' => $user->countProjects()]) }}</a>--}}
                                <a href="{{ @route('users.addresses.index', ['id' => $user->id]) }}" class="btn btn-outline-info pull-left" style="margin-right: 3px;"><i class="fa fa-map-marked-alt"></i> {{ __('Addresses (:addrs)', ['addrs' => $user->countAddresses()]) }}</a>
                                <a href="{{ @route('users.edit', $user->id) }}" class="btn btn-info pull-left" style="margin-right: 3px;">Edit</a>
                                <a href="{{ @route('users.confirm.destroy', ['id' => $user->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                @include('includes/paginator', ['items' => $users])

            @endif
        </div>

        <a href="{{ route('users.create') }}" class="btn btn-success">Add User</a>

    </div>

@endsection