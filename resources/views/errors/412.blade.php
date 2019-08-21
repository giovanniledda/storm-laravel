@extends('errors::minimal')

@section('title', __('Precondition Failed'))
@section('code', '412')
@section('message', $exception->getMessage())