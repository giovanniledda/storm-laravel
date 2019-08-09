<div class="alert alert-danger">
    <div class="alert-title">{{ $title }}</div>

    <hr />
    {{ $slot }}

    <hr />
    @if(isset($foo))
        {{ $foo }}
    @endif

    @datetime(time())
</div>