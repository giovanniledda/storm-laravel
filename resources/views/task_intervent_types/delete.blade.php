@extends('layouts.app')

@section('title', '| Delete Intervent type')

@section('breadcrumbs')
    {{ Breadcrumbs::render('task_intervent_type', $intervent_type) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-hammer'></i> {{ __('Deleting intervent type :name', ['name' => $intervent_type->name]) }}</h1>
        <h3>...are you sure?</h3>
        <hr>
        {!! Form::open(['method' => 'DELETE', 'route' => ['task_intervent_types.destroy', $intervent_type->id] ]) !!}
        @csrf
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection