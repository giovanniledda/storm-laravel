@extends('layouts.app')

@section('title', '| Add Profession')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1><i class='fa fa-user-tie'></i> {{ __('Add Profession') }}</h1>
        <hr>

        {{ Form::open(array('url' => 'professions')) }}

        @csrf

        <div class="form-group">
            {{ Form::label('name', __('Name')) }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('is_storm', __('Storm')) }}
            {{ Form::checkbox('is_storm', 1, null, array('class' => 'form-control')) }}
        </div>

        {{ Form::submit(__('Add'), array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>

@endsection