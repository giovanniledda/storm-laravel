@extends('layouts.app')

@section('title', '| Dockyards/Sites')

@section('breadcrumbs')
    {{ Breadcrumbs::render('sites') }}
@endsection

@section('content')

    <div class="col-lg-10 col-lg-offset-1">
        <h1>
            <i class="fa fa-anchor"></i> {{ __('Dockyards/Sites') }}
        </h1>
        <hr>
        <div class="table-responsive">
            @if(!count($sites))
                @include('includes/no-results')
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Lat/Lng') }}</th>
                        <th>{{ __('Operation') }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($sites as $site)
                        <tr>
                            <td>{{ $site->name }}</td>
                            <td>{{ $site->location }}</td>
                            <td>{{ $site->lat }}, {{ $site->lng }}</td>
                            <td>
                                <a href="{{ @route('sites.addresses.index', ['id' => $site->id]) }}" class="btn btn-outline-info pull-left" style="margin-right: 3px;"><i class="fa fa-map-marked-alt"></i> {{ __('Addresses (:addrs)', ['addrs' => $site->countAddresses()]) }}</a>
                                <a href="{{ URL::to('sites/'.$site->id.'/edit') }}" class="btn btn-info">{{ __('Edit') }}</a>
                                <a href="{{ @route('sites.confirm.destroy', ['id' => $site->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                @include('includes/paginator', ['items' => $sites])

            @endif
        </div>

        <a href="{{ URL::to('sites/create') }}" class="btn btn-success">{{ __('Add site') }}</a>

    </div>

@endsection