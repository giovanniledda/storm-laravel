@extends('layouts.app')

@section('title', '| Edit User')

@section('breadcrumbs')
    {{ Breadcrumbs::render('user', $user) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1><i class='fa fa-user-plus'></i> {{ __('Edit :name', ['name' => $user->name]) }}</h1>
        <hr>

        {{ Form::model($user, array('route' => array('users.update', $user->id), 'method' => 'PUT', 'files' => true)) }} {{-- Form model binding to automatically populate our fields with user data --}}

        @csrf

        <div class="form-group">
            {{ Form::label('name', 'Name') }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('email', 'Email') }}
            {{ Form::email('email', null, array('class' => 'form-control')) }}
        </div>


        <div class="form-group">
            {{ Form::label('photo', __('Profile photo')) }}
            <input name="photo" type="file">
        </div>

        <div class="form-group">
            {{ Form::label('is_storm', __('Storm')) }}
            {{ Form::checkbox('is_storm', 1, null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('disable_login', __('Login disabled')) }}
            {{ Form::checkbox('disable_login', 1, null, array('class' => 'form-control')) }}
        </div>

        <h5><b>Give Role</b></h5>

        <div class='form-group'>
            @foreach ($roles as $role)
                {{ Form::checkbox('roles[]',  $role->id, $user->roles) }}
                {{ Form::label($role->name, ucfirst($role->name)) }}<br>
            @endforeach
        </div>

        <div class="form-group">
            {{ Form::label('password', 'Password') }}<br>
            {{ Form::password('password', array('class' => 'form-control')) }}

        </div>

        <div class="form-group">
            {{ Form::label('password', 'Confirm Password') }}<br>
            {{ Form::password('password_confirmation', array('class' => 'form-control')) }}
        </div>

        {{ Form::submit(__('Save'), array('class' => 'btn btn-primary')) }}
        <a href="{{ @route('users.confirm.destroy', ['id' => $user->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>

        {{ Form::close() }}

    </div>

@endsection