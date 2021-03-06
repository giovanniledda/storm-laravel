@extends('layouts.app')

@section('title', '| Delete Permission')

@section('breadcrumbs')
    {{ Breadcrumbs::render('permission', $permission) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-key'></i> {{ __('Permission delete') }}</h1>
        <hr>
        <h2>{{ __('Deleting Permission: :name', ['name' => $permission->name]) }}</h2>
        <h3>...are you sure?</h3>

        {!! Form::open(['method' => 'DELETE', 'route' => ['permissions.destroy', $permission->id] ]) !!}
        @csrf
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection