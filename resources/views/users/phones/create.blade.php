@extends('layouts.app')

@section('title', '| Add User Phone')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>

        <h1>
            <i class='fa fa-phone'></i> {{ __('Add phone for user :name', ['name' => $user->name]) }}
        </h1>
        <hr>

        <form method="POST" action="{{ @route('users.phones.store', ['id' => $user->id]) }}">

            @csrf

            {{ Form::hidden('user_id', $user->id) }}

            <div class="form-group">
                {{ Form::label('phone_number', 'Number') }}
                {{ Form::text('phone_number', '', array('class' => 'form-control')) }}
            </div>

            @phonetypes()
            @endphonetypes

            {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}

        </form>

    </div>

@endsection