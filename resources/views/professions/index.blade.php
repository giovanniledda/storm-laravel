@extends('layouts.app')

@section('title', '| Professions')

@section('content')

    <div class="col-lg-10 col-lg-offset-1">
        <h1>
            <i class="fa fa-user-tie"></i> {{ __('Professions') }}
        </h1>
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Storm') }}</th>
                    <th>{{ __('Operation') }}</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($professions as $profession)
                    <tr>

                        <td>{{ $profession->name }}</td>
                        <td>{{ $profession->is_storm }}</td>
                        <td>
                            <a href="{{ URL::to('professions/'.$profession->id.'/edit') }}" class="btn btn-info pull-left" style="margin-right: 3px;">{{ __('Edit') }}</a>
                            <a href="{{ @route('professions.confirm.destroy', ['id' => $profession->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>

        <a href="{{ URL::to('professions/create') }}" class="btn btn-success">{{ __('Add profession') }}</a>

    </div>

@endsection