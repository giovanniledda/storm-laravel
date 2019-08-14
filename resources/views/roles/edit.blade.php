@extends('layouts.app')

@section('title', '| Edit Role')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-key'></i> Edit Role: {{$role->name}}</h1>
        <hr>

        {{ Form::model($role, array('route' => array('roles.update', $role->id), 'method' => 'PUT')) }}

        @csrf

        <div class="form-group">
            {{ Form::label('name', 'Role Name') }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        <h5><b>Assign Permissions</b></h5>
        @foreach ($permissions as $permission)
            {{ Form::checkbox('permissions[]',  $permission->id, $role->permissions ) }}
            {{ Form::label($permission->name, ucfirst($permission->name)) }}<br>
        @endforeach
        <br>
        {{ Form::submit(__('Save'), array('class' => 'btn btn-primary')) }}
        <a href="{{ @route('roles.confirm.destroy', ['id' => $role->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>

        {{ Form::close() }}
    </div>

@endsection