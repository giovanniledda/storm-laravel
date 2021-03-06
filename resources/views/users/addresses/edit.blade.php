@extends('layouts.app')

@section('title', '| Edit User address')

@section('breadcrumbs')
    {{ Breadcrumbs::render('user.address', $user, $address) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-map-marked-alt'></i> {{ __('Edit address :id (:name)', ['id' => $address->id, 'name' => $address->country->name]) }}</h1>
        <hr>

        <form method="POST" action="{{ @route('users.addresses.update', ['user_id' => $user->id, 'address_id' => $address->id])  }}">

        @method('PUT')
        @csrf

        {{ Form::hidden('user_id', $user->id) }}

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
        <a href="{{ @route('users.addresses.confirm.destroy', ['user_id' => $user->id, 'address_id' => $address->id]) }}" class="btn btn-danger">{{ __('Delete') }}</a>

        </form>
    </div>

@endsection