@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                </div>

                @role('admin') {{-- Laravel-permission blade helper --}}
                    <div class="card-body">

                        <h4>Users</h4>
                        <a href="{{ route('roles.index') }}"><i class="fa fa-btn fa-award"></i> Roles</a>
                        <a href="{{ route('permissions.index') }}"><i class="fa fa-btn fa-key"></i> Permissions</a>
                        <a href="{{ route('users.index') }}"><i class="fa fa-btn fa-users"></i> Users</a>

                        <h4>Boat system</h4>
                        <a href="{{ route('sites.index') }}"><i class="fa fa-btn fa-anchor"></i> Dockyards/sites</a>

                    </div>

                @endrole

            </div>
        </div>
    </div>
</div>
@endsection
