@extends('layouts.app')

@section('title', '| Add User address')

@section('breadcrumbs')
    {{ Breadcrumbs::render('user.addresses.new', $user) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1><i class='fa fa-map-marked-alt'></i> {{ __('Add address') }}</h1>
        <hr>

        <form method="POST" action="{{ @route('users.addresses.store', ['id' => $user->id])  }}">

        @csrf

        {{ Form::hidden('user_id', $user->id) }}

        <div class="form-group">
            {{ Form::label('street', __('Street')) }}
            {{ Form::text('street', null, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
            {{ Form::label('city', __('City')) }}
            {{ Form::text('city', null, array('class' => 'form-control')) }}
        </div>


        <div class="form-group">
            {{ Form::label('post_code', __('Post Code')) }}
            {{ Form::text('post_code', null, array('class' => 'form-control')) }}
        </div>


        <div class="form-group">
            {{ Form::label('state', __('State')) }}
            {{ Form::text('state', null, array('class' => 'form-control')) }}
        </div>

        @countries()
        @endcountries

        <div class="form-group">
            {{ Form::label('note', __('Note')) }}
            {{ Form::text('note', null, array('class' => 'form-control')) }}
        </div>


        <div class="form-group">
            {{ Form::label('is_primary', __('')) }}
            {{ Form::checkbox('is_primary', 1, null, array('class' => 'form-control')) }}
        </div>

        {{ Form::submit(__('Add'), array('class' => 'btn btn-primary')) }}

        </form>

    </div>

@endsection