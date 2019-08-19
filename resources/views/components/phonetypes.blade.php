@php
    $selected = isset($selected_type) ? $selected_type : null;
@endphp

<div class="form-group">
    {!! Form::Label('phone_type', __('Type')) !!}
    {!! Form::select('phone_type', StormUtils::getPhoneTypes(), $selected, ['class' => 'form-control']) !!}
</div>