@extends('layouts.app')

@section('title', '| Add Intervent type')

@section('breadcrumbs')
    {{ Breadcrumbs::render('task_intervent_types.new') }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1><i class='fa fa-hammer'></i> {{ __('Add Intervent type') }}</h1>
        <hr>

        {{ Form::open(array('url' => 'task_intervent_types')) }}

        @csrf

        <div class="form-group">
            {{ Form::label('name', __('Name')) }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        {{ Form::submit(__('Add'), array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>

@endsection