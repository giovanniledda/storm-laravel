@extends('layouts.app')

@section('title', '| Delete Profession Association')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-user-tie'></i> {{ __('Deleting profession association: User :uname, Profession :profname, Project :projname',
        ['uname' => $project_user->user->name, 'profname' => $project_user->profession->name, 'projname' => $project_user->project->name ]) }}</h1>
        <h3>...are you sure?</h3>
        <hr>
        {!! Form::open(['method' => 'DELETE', 'route' => ['project_user.destroy', $project_user->id] ]) !!}
        @csrf
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection