@extends('layouts.app')

@section('title', '| Project Users Professions')

@section('content')

    <div class="col-lg-10 col-lg-offset-1">

        <h1>
            <i class="fa fa-phone"></i> {{ __('User :name professions', ['name' => $user->name]) }}

            <a href="{{ route('roles.index') }}" class="btn btn-default pull-right">Roles</a>
            <a href="{{ route('permissions.index') }}" class="btn btn-default pull-right">Permissions</a></h1>
        <hr>

        <hr>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">

                <thead>
                <tr>
                    <th>Project</th>
                    <th>Profession</th>
                    <th>Operation</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($project_users as $project_user)
                    <tr>

                        <td>{{ $project_user->project->name }}</td>
                        <td>{{ $project_user->profession->name }}</td>
                        <td>
                            {{--<a href="{{ @route('users.phones.index', ['id' => $user->id]) }}" class="btn btn-outline-info pull-left" style="margin-right: 3px;"><i class="fa fa-phone"></i> {{ __('Phones (:phones)', ['phones' => $user->countPhones()]) }}</a>--}}
                            {{--<a href="{{ @route('users.phones.index', ['id' => $user->id]) }}" class="btn btn-outline-info pull-left" style="margin-right: 3px;"><i class="fa fa-user-tie"></i> {{ __('Professions') }}</a>--}}
                            {{--<a href="{{ @route('users.edit', $user->id) }}" class="btn btn-info pull-left" style="margin-right: 3px;">Edit</a>--}}
                            {{--<a href="{{ @route('users.confirm.destroy', ['id' => $user->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>--}}
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>

        <a href="{{ route('project_user.create') }}" class="btn btn-success">{{ __('Add profession') }}</a>

    </div>

@endsection