@extends('layouts.app')

@section('title', '| Delete Dockyard/Site address')

@section('breadcrumbs')
    {{ Breadcrumbs::render('site.address', $site, $address) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-map-marked-alt'></i> {{ __('Address delete') }}</h1>
        <hr>
        <h2>{{ __('Deleting address :id (:name)', ['id' => $address->id, 'name' => $address->country->name]) }}</h2>
        <h3>...are you sure?</h3>

        <form method="POST" action="{{ @route('sites.addresses.destroy', ['site_id' => $site->id, 'address_id' => $address->id])  }}">
            @method('DELETE')
            @csrf
            {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        </form>
    </div>

@endsection