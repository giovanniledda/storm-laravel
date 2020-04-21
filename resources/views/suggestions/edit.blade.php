@extends('layouts.app')

@section('title', '| Edit Suggestion')

@section('breadcrumbs')
    {{ Breadcrumbs::render('suggestions.show', $suggestion) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-comment-alt'></i> {{ __('Edit profession :desc', ['desc' => Illuminate\Support\Str::limit($suggestion->body, 30)]) }}</h1>
        <hr>

        {{ Form::model($suggestion, array('route' => array('suggestions.update', $suggestion), 'method' => 'PUT')) }}

        @csrf

        <div class="form-group">
            {{ Form::label('body', __('Text')) }}
            {{ Form::textarea('body', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('context', __('Application context')) }}
            {{ Form::text('context', null, array('id' => 'app-context', 'class' => 'form-control')) }}
        </div>

        <br>
        {{ Form::submit(__('Save'), array('class' => 'btn btn-primary')) }}
{{--        <a href="{{ @route('suggestions.confirm.destroy', ['id' => $suggestion->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>--}}

        {{ Form::close() }}
    </div>

@endsection
