@extends('layouts.app')

@section('title', '| Edit Intervent type')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-user-tie'></i> {{ __('Edit intervent type :name', ['name' => $intervent_type->name]) }}</h1>
        <hr>

        {{ Form::model($intervent_type, array('route' => array('task_intervent_types.update', $intervent_type), 'method' => 'PUT')) }}

        @csrf

        <div class="form-group">
            {{ Form::label('name', __('Name')) }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        <br>
        {{ Form::submit(__('Save'), array('class' => 'btn btn-primary')) }}
        <a href="{{ @route('task_intervent_types.confirm.destroy', ['id' => $intervent_type->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>

        {{ Form::close() }}
    </div>

@endsection