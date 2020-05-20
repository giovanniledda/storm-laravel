@extends('layouts.app')

@section('title', '| Add Suggestion')

@section('breadcrumbs')
    {{ Breadcrumbs::render('suggestions.new') }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1><i class='fa fa-comment-alt'></i> {{ __('Add Suggestion') }}</h1>
        <hr>

        {{ Form::open(array('url' => 'suggestions')) }}

        @csrf

        <div class="form-group">
            {{ Form::label('body', __('Text')) }}
            {{ Form::textarea('body', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
{{--            {{ Form::label('context', __('Application context')) }}--}}
{{--            {{ Form::text('context', null, array('id' => 'app-context', 'class' => 'form-control')) }}--}}
            <autocomplete></autocomplete>
        </div>

        {{ Form::submit(__('Add'), array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}

    </div>

@endsection

{{--<script type="application/javascript">--}}
{{--    $(document).ready(function () {--}}
{{--        $('#app-context').val('Buh!');--}}
{{--    });--}}
{{--</script>--}}
