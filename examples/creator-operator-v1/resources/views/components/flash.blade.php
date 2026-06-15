@if (session('status'))
    <div class="ops-flash-success" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="ops-flash-error" role="alert">
        {{ $errors->first() }}
    </div>
@endif
