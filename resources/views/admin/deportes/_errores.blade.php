@if (session('error') || $errors->any())
    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">
        <p class="font-extrabold"><i class="fas fa-exclamation-triangle mr-1"></i> No se pudo completar la operación</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @if (session('error'))
                <li>{{ session('error') }}</li>
            @endif
            @foreach ($errors->all() as $mensaje)
                <li>{{ $mensaje }}</li>
            @endforeach
        </ul>
    </div>
@endif
