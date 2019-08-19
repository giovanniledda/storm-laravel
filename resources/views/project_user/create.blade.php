@extends('layouts.app')

@section('title', '| Add Profession for User')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1><i class='fa fa-user-tie'></i> {{ __('Add profession for user :name', ['name' => $user->name]) }}</h1>
        <hr>

        {{ Form::open(array('url' => 'project_user')) }}

        @csrf

        {{ Form::hidden('user_id', $user->id) }}

        @projects() @endprojects

        @stormprofessions() @endstormprofessions

        {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>

@endsection