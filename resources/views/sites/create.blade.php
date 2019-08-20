@extends('layouts.app')

@section('title', '| Add Dockyard/Site')

@section('breadcrumbs')
    {{ Breadcrumbs::render('sites.new') }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1><i class='fa fa-anchor'></i> {{ __('Add Dockyard/Site') }}</h1>
        <hr>

        {{ Form::open(array('url' => 'sites')) }}

        @csrf

        <div class="form-group">
            {{ Form::label('name', __('Name')) }}
            {{ Form::text('name', null, array('class' => 'form-control')) }}
        </div>


        <div class="form-group">
            {{ Form::label('location', __('Location')) }}
            {{ Form::text('location', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('lat', __('Latitude')) }}
            {{ Form::text('lat', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('lng', __('Longitude')) }}
            {{ Form::text('lng', null, array('class' => 'form-control')) }}
        </div>

        {{ Form::submit(__('Add'), array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>

@endsection