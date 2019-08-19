@extends('layouts.app')

@section('title', '| Delete User address')

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-key'></i> {{ __('Deleting address :id (:name)', ['id' => $address->id, 'name' => $address->country->name]) }}</h1>
        <h3>...are you sure?</h3>
        <hr>

        <form method="POST" action="{{ @route('users.addresses.destroy', ['user_id' => $user->id, 'address_id' => $address->id])  }}">
            @method('DELETE')
            @csrf
            {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        </form>
    </div>

@endsection