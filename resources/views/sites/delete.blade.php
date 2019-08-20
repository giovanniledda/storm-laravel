@extends('layouts.app')

@section('title', '| Delete Dockyard/Site')

@section('breadcrumbs')
    {{ Breadcrumbs::render('site', $site) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-key'></i> {{ __('Deleting dockyard/site :name', ['name' => $site->name]) }}</h1>
        <h3>...are you sure?</h3>
        <hr>
        {!! Form::open(['method' => 'DELETE', 'route' => ['sites.destroy', $site->id] ]) !!}
        @csrf
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection