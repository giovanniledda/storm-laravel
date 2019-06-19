@extends('layouts.app')

@section('title', '| Edit Role')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-key'></i> Deleting Role: {{$role->name}}</h1>
        <h3>...are you sure?</h3>
        <hr>
        {!! Form::open(['method' => 'DELETE', 'route' => ['roles.destroy', $role->id] ]) !!}
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection