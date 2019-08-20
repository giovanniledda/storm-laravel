@extends('layouts.app')

@section('title', '| Edit Permission')

@section('breadcrumbs')
    {{ Breadcrumbs::render('permission', $permission) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1><i class='fa fa-key'></i> Edit {{$permission->name}}</h1>
        <br>
        {{ Form::model($permission, array('route' => array('permissions.update', $permission->id), 'method' => 'PUT')) }}{{-- Form model binding to automatically populate our fields with permission data --}}

        @csrf

        <div class="form-group">
            {{ Form::label('name', 'Permission Name') }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        <br>

        {{ Form::submit(__('Save'), array('class' => 'btn btn-primary')) }}
        <a href="{{ @route('permissions.confirm.destroy', ['id' => $permission->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>

        {{ Form::close() }}

    </div>

@endsection