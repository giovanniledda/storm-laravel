@extends('layouts.app')

@section('title', '| Delete User')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-users'></i> Deleting User: {{ $user->name }}</h1>
        <h3>...are you sure?</h3>
        <hr>
        {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id] ]) !!}
        @csrf
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection