@extends('layouts.app')

@section('title', '| Delete User Phone')

@section('breadcrumbs')
    {{ Breadcrumbs::render('user.phone', $user, $phone) }}
@endsection

@section('content')

    <div class='col-lg-4 col-lg-offset-4'>
        <h1><i class='fa fa-phone'></i> {{ __('Phone delete') }}</h1>
        <hr>
        <h2>{{ __('Deleting :type phone :num, for user :name', ['name' => $user->name, 'num' => $phone->phone_number, 'type' => $phone->phone_type]) }}</h2>
        <h3>...are you sure?</h3>

        <form method="POST" action="{{ @route('users.phones.destroy', ['user_id' => $user->id, 'phone_id' => $phone->id])  }}">
            @method('DELETE')
            @csrf
            {!! Form::submit('Yes, delete!', ['class' => 'btn btn-danger']) !!}
        </form>
    </div>

@endsection