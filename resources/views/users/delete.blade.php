@extends('layouts.app')

@section('title', '| Delete User')

@section('breadcrumbs')
    {{ Breadcrumbs::render('user', $user) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-users'></i> {{ __('User delete') }}</h1>
        <hr>
        <h2>{{ __('Deleting User: :name', ['name' => $user->name]) }}</h2>
        <h3>...are you sure?</h3>

        {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id] ]) !!}
        @csrf
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection