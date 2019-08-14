@extends('layouts.app')

@section('title', '| Intervent Types')

@section('content')

    <div class="col-lg-10 col-lg-offset-1">
        <h1>
            <i class="fa fa-hammer"></i> {{ __('Intervent types') }}
        </h1>
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Operation') }}</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($intervent_types as $type)
                    <tr>

                        <td>{{ $type->name }}</td>
                        <td>
                            <a href="{{ URL::to('task_intervent_types/'.$type->id.'/edit') }}" class="btn btn-info pull-left" style="margin-right: 3px;">{{ __('Edit') }}</a>
                            <a href="{{ @route('task_intervent_types.confirm.destroy', ['id' => $type->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>

        <a href="{{ URL::to('task_intervent_types/create') }}" class="btn btn-success">{{ __('Add type') }}</a>

    </div>

@endsection