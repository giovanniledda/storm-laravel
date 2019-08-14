@extends('layouts.app')

@section('title', '| Edit Dockyard address')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-anchor'></i> {{ __('Edit address :id (:name)', ['id' => $address->id, 'name' => $address->country->name]) }}</h1>
        <hr>

        <form method="POST" action="{{ @route('sites.addresses.update', ['site_id' => $site->id, 'address_id' => $address->id])  }}">

        @method('PUT')
        @csrf

        {{ Form::hidden('site_id', $site->id) }}

        <div class="form-group">
            {{ Form::label('street', __('Street')) }}
            {{ Form::text('street', $address->street, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('city', __('City')) }}
            {{ Form::text('city', $address->city, array('class' => 'form-control')) }}
        </div>


        <div class="form-group">
            {{ Form::label('post_code', __('Post Code')) }}
            {{ Form::text('post_code', $address->post_code, array('class' => 'form-control')) }}
        </div>


        <div class="form-group">
            {{ Form::label('state', __('State')) }}
            {{ Form::text('state', $address->state, array('class' => 'form-control')) }}
        </div>

        @countries(['selected_country' => $address->country])
        @endcountries

        <div class="form-group">
            {{ Form::label('note', __('Note')) }}
            {{ Form::text('note', $address->note, array('class' => 'form-control')) }}
        </div>


        <div class="form-group">
            {{ Form::label('is_primary', __('Primary')) }}
            {{ Form::checkbox('is_primary', 1, $address->is_primary, array('class' => 'form-control')) }}
        </div>


        <br>
        {{ Form::submit(__('Save'), array('class' => 'btn btn-primary')) }}
        <a href="{{ @route('sites.addresses.confirm.destroy', ['site_id' => $site->id, 'address_id' => $address->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>

        </form>
    </div>

@endsection