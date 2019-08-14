@extends('layouts.app')

@section('title', '| Edit Profession')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-user-tie'></i> {{ __('Edit profession :name', ['name' => $profession->name]) }}</h1>
        <hr>

        {{ Form::model($profession, array('route' => array('professions.update', $profession), 'method' => 'PUT')) }}

        <div class="form-group">
            {{ Form::label('name', __('Name')) }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('is_storm', __('Storm')) }}
            {{ Form::checkbox('is_storm', 1, null, array('class' => 'form-control')) }}
        </div>

        <br>
        {{ Form::submit(__('Save'), array('class' => 'btn btn-primary')) }}
        <a href="{{ @route('professions.confirm.destroy', ['id' => $profession->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>

        {{ Form::close() }}
    </div>

@endsection