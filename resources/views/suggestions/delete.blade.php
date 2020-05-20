@extends('layouts.app')

@section('title', '| Delete Suggestion')

@section('breadcrumbs')
    {{ Breadcrumbs::render('suggestions.show', $suggestion) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-comment-alt'></i> {{ __('Profession delete') }}</h1>
        <hr>
        <h2>{{ __('Deleting Suggestion :desc', ['desc' => Illuminate\Support\Str::limit($suggestion->body, 30)]) }}</h2>
        <h3>...are you sure?</h3>

        {!! Form::open(['method' => 'DELETE', 'route' => ['suggestions.destroy', $suggestion]]) !!}
        @csrf
        {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>

@endsection
