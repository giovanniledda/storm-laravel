@php
    $selected = isset($selected_profession_id) ? $selected_profession_id : null;
@endphp

<div class="form-group">
    {!! Form::Label('profession_id', __('Profession')) !!}
    {!! Form::select('profession_id', StormUtils::getStormProfessionsList(), $selected, ['class' => 'form-control']) !!}
</div>