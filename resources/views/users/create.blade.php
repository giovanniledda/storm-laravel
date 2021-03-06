@extends('layouts.app')

@section('title', '| Add User')

@section('breadcrumbs')
    {{ Breadcrumbs::render('users.new') }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1><i class='fa fa-user-plus'></i> Add User</h1>
        <hr>

        {{ Form::open(array('url' => 'users')) }}

        @csrf

        <div class="form-group">
            {{ Form::label('name', 'Name') }}
            {{ Form::text('name', '', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('surname', 'Surname') }}
            {{ Form::text('surname', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('email', 'Email') }}
            {{ Form::email('email', '', array('class' => 'form-control')) }}
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
                {{ Form::checkbox('roles[]',  $role->id ) }}
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

        {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>

@endsection