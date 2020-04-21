@extends('layouts.app')

@section('title', '| Suggestions')

@section('breadcrumbs')
    {{ Breadcrumbs::render('suggestions') }}
@endsection

@section('content')

    <div class="col-lg-10 col-lg-offset-1">
        <h1>
            <i class="fa fa-comment-alt"></i> {{ __('Suggestions') }}
        </h1>
        <hr>
        <div class="table-responsive">
            @if(!count($suggestions))
                @include('includes/no-results')
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>{{ __('Text') }}</th>
                        <th>{{ __('Application context') }}</th>
                        <th>{{ __('Counter of use') }}</th>
                        <th>{{ __('Operation') }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($suggestions as $suggestion)
                        <tr>

                            <td>{{ Illuminate\Support\Str::limit($suggestion->body, 30) }}</td>
                            <td>{{ $suggestion->context }}</td>
                            <td>{{ $suggestion->use_counter }}</td>
                            <td>
                                <a href="{{ route('suggestions.edit', ['suggestion' => $suggestion]) }}" class="btn btn-info pull-left" style="margin-right: 3px;">{{ __('Edit') }}</a>
                                <a href="{{ @route('suggestions.confirm.destroy', ['suggestion' => $suggestion]) }}" class="btn btn-danger">{{ __('Delete') }}</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                @include('includes/paginator', ['items' => $suggestions])

            @endif
        </div>

        <a href="{{ route('suggestions.create') }}" class="btn btn-success">{{ __('Add suggestion') }}</a>

    </div>

@endsection
