@extends('layouts.app')

@section('title', '| Project-User Relation')

@section('content')

    <div class="col-lg-10 col-lg-offset-1">

        <h1>
            <i class="fa fa-user-tie"></i> {{ __('User :name relations with projects', ['name' => $user->name]) }}

            <a href="{{ route('users.index') }}" class="btn btn-default pull-right">Users</a>
        </h1>
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
                            <a href="{{ @route('project_user.confirm.destroy', ['id' => $project_user->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>

        <a href="{{ route('project_user.create', ['user_id' => $user->id]) }}" class="btn btn-success">{{ __('Add relation') }}</a>

    </div>

@endsection