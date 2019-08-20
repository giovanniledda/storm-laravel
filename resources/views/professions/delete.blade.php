@extends('layouts.app')

@section('title', '| Delete Profession')

@section('breadcrumbs')
    {{ Breadcrumbs::render('profession', $profession) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-user-tie'></i> {{ __('Profession delete') }}</h1>
        <hr>
        <h2>{{ __('Deleting Profession :name', ['name' => $profession->name]) }}</h2>
        <h3>...are you sure?</h3>

        {!! Form::open(['method' => 'DELETE', 'route' => ['professions.destroy', $profession->id] ]) !!}
        @csrf
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection