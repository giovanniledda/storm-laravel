@extends('layouts.app')

@section('title', '| User addresses')

@section('content')

    <div class="col-lg-10 col-lg-offset-1">
        <h1>
            <i class="fa fa-map-marked-alt"></i> {{ __('Addresses for user :name', ['name' => $user->name]) }}
        </h1>
        <hr>
        <div class="table-responsive">
            @if(!count($addresses))
                @include('includes/no-results')
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>{{ __('Address #') }}</th>
                        <th>{{ __('Street') }}</th>
                        <th>{{ __('City') }}</th>
                        <th>{{ __('Post code') }}</th>
                        <th>{{ __('State') }}</th>
                        <th>{{ __('Country') }}</th>
                        <th>{{ __('Note') }}</th>
                        <th>{{ __('Primary') }}</th>
                        <th>{{ __('Operation') }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    {{--street, city, post_code, state, country, note (for internal use), is_primary, is_billing & is_shipping--}}
                    @foreach ($addresses as $addr)
                        <tr>

                            <td>{{ $addr->id }}</td>
                            <td>{{ $addr->street }}</td>
                            <td>{{ $addr->city }}</td>
                            <td>{{ $addr->post_code }}</td>
                            <td>{{ $addr->state }}</td>
                            <td>{{ $addr->country->name . ' - ' . $addr->country->iso_3166_2 }}</td>
                            <td>{{ $addr->note }}</td>
                            <td>{{ $addr->is_primary }}</td>
                            <td>
                                <a href="{{ @route('users.addresses.edit', ['user_id' => $user->id, 'address_id' => $addr->id]) }}" class="btn btn-info">{{ __('Edit') }}</a>
                                <a href="{{ @route('users.addresses.confirm.destroy', ['user_id' => $user->id, 'address_id' => $addr->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <a href="{{ @route('users.addresses.create', ['user_id' => $user->id]) }}" class="btn btn-success">{{ __('Add address') }}</a>

    </div>

@endsection