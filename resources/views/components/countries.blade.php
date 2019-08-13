@php
    $selected = isset($country) ? $country->iso_3166_2 : null;
@endphp

<div class="form-group">
    {!! Form::Label('country', 'Country') !!}
    {!! Form::select('country', StormUtils::getCountriesList(), $selected, ['class' => 'form-control']) !!}
</div>