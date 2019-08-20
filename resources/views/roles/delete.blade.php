@extends('layouts.app')

@section('title', '| Delete Role')

@section('breadcrumbs')
    {{ Breadcrumbs::render('role', $role) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-award'></i> {{ __('Role delete') }}</h1>
        <hr>
        <h2>{{ __('Deleting Role: :name', ['name' => $role->name]) }}</h2>
        <h3>...are you sure?</h3>
        {!! Form::open(['method' => 'DELETE', 'route' => ['roles.destroy', $role->id] ]) !!}
        @csrf
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection