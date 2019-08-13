<div class="form-group">

    {!! Form::Label('country', 'Country') !!}
    {!! Form::select('country', StormUtils::getCountriesList(), null, ['class' => 'form-control']) !!}
</div>