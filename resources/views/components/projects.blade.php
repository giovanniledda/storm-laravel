@php
    $selected = isset($selected_proj_id) ? $selected_proj_id : null;
@endphp

<div class="form-group">
    {!! Form::Label('project_id', __('Project')) !!}
    {!! Form::select('project_id', StormUtils::getActiveProjectsList(), $selected, ['class' => 'form-control']) !!}
</div>