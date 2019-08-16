@extends('layouts.app')

@section('title', '| User phones')

@section('content')

    <div class="col-lg-10 col-lg-offset-1">

        <h1>
            <i class="fa fa-phone"></i> {{ __('User :name phones', ['name' => $user->name]) }}

            <a href="{{ route('roles.index') }}" class="btn btn-default pull-right">Roles</a>
            <a href="{{ route('permissions.index') }}" class="btn btn-default pull-right">Permissions</a></h1>
        <hr>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">

                <thead>
                <tr>
                    <th>Phone #</th>
                    <th>Type</th>
                    <th>Number</th>
                    <th>Operations</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($phones as $phone)
                    <tr>

                        <td>{{ $phone->id }}</td>
                        <td>{{ $phone->phone_type }}</td>
                        <td>{{ $phone->phone_number }}</td>
                        <td>
                            <a href="{{ @route('users.phones.confirm.destroy', ['user_id' => $user->id, 'phone_id' => $phone->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>

        <a href="{{ route('users.phones.create', ['id' => $user->id]) }}" class="btn btn-success">{{ __('Add phone') }}</a>

    </div>

@endsection