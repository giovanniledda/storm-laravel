@extends('layouts.app')

@section('title', '| Edit Dockyard/Site')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-anchor'></i> {{ __('Edit dockyard/site :name', ['name' => $site->name]) }}</h1>
        <hr>

        {{ Form::model($site, array('route' => array('sites.update', $site), 'method' => 'PUT')) }}

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

        <h5><b>Assign Address</b></h5>

        <br>
        {{ Form::submit(__('Save'), array('class' => 'btn btn-primary')) }}
        <a href="{{ @route('sites.confirm.destroy', ['id' => $site->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>

        {{ Form::close() }}
    </div>

@endsection