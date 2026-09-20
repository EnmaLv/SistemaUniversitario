@php
    use Illuminate\Support\Str;
    $depth = $depth ?? 0;
    $margin = $depth * 15;

    // Solo en el nivel raíz dividimos los elementos para agrupar los huérfanos
    $submenus = [];
    $directLinks = [];

    if ($depth === 0) {
        foreach ($items as $it) {
            if (isset($it['submenu']) && is_array($it['submenu'])) {
                $submenus[] = $it;
            } else {
                $directLinks[] = $it;
            }
        }
    } else {
        // Si no estamos en la raíz, se procesa la lista de forma normal secuencial
        $submenus = $items;
    }
@endphp

{{-- 1. Renderizamos primero todos los submenús (Carpetas grandes), cada uno en su propio bloque --}}
@foreach($submenus as $it)
    @php
        $margin = $depth * 15;
        $isSubmenu = isset($it['submenu']) && is_array($it['submenu']);
    @endphp

    @if($isSubmenu)
        @if($depth === 0)
            <div class="permission-group-block">
        @endif

        <div class="permission-group-title mt-1 mb-2" style="margin-left:{{ $margin }}px;">
            <strong class="text-uppercase small" style="letter-spacing:0.5px; color:var(--text-main) !important;">
                <i class="fas fa-folder-open mr-1" style="color:var(--color-primary);"></i> {{ $it['text'] }}
            </strong>
        </div>

        {{-- Llamada recursiva para los hijos --}}
        @include('admin.configuracion.permisos._matrix', [
            'items' => $it['submenu'],
            'rolePerms' => $rolePerms,
            'rolePatterns' => $rolePatterns,
            'effective' => $effective,
            'keyToPatterns' => $keyToPatterns,
            'depth' => $depth + 1,
        ])

        @if($depth === 0)
            </div>
        @endif
    @else
        {{-- Procesamiento normal para niveles internos (> 0) --}}
        @php
            $val = $it['key'] ?? ($it['url'] ?? ($it['route'] ?? null));
            if (!$val) { continue; }

            $patterns = $keyToPatterns[$val] ?? [$val];
            $isRoleProvided = in_array($val, (array)$rolePerms) || count(array_intersect($patterns, (array)$rolePatterns)) > 0;
            $checked = (count(array_intersect($patterns, (array)$effective)) > 0);
            $id = 'check_' . Str::slug($val);
        @endphp

        <div class="custom-control custom-checkbox mb-2 permission-item d-flex align-items-center justify-content-between" style="margin-left:{{ $margin }}px;">
            <div class="flex-grow-1">
                <input type="checkbox" class="custom-control-input perm-chk" id="{{ $id }}" value="{{ e($val) }}" @if($checked) checked @endif data-role="{{ $isRoleProvided ? '1' : '0' }}" @if(! $isRoleProvided) name="allow[]" @endif>
                <label class="custom-control-label font-weight-normal mb-0" style="cursor:pointer; font-size:0.9rem; color:var(--text-main);" for="{{ $id }}">
                    {{ e($it['text']) }}
                </label>
            </div>
            @if($isRoleProvided)
                <i class="fas fa-id-badge ml-2" title="Provisto por Rol" style="font-size:0.75rem; color:var(--color-primary);"></i>
            @endif
        </div>
    @endif
@endforeach

{{-- 2. Al final de la raíz, si hay links directos sueltos, los metemos JUNTOS en una sola tarjeta unificada --}}
@if($depth === 0 && count($directLinks) > 0)
    <div class="permission-group-block">
        <div class="permission-group-title mt-1 mb-3">
            <strong class="text-uppercase small" style="letter-spacing:0.5px; color:var(--text-main) !important;">
            <i class="fas fa-link mr-1" style="color:var(--color-primary);"></i> Accesos del sistema
            </strong>
        </div>

        @foreach($directLinks as $it)
            @php
                $val = $it['key'] ?? ($it['url'] ?? ($it['route'] ?? null));
                if (!$val) { continue; }

                $patterns = $keyToPatterns[$val] ?? [$val];
                $isRoleProvided = in_array($val, (array)$rolePerms) || count(array_intersect($patterns, (array)$rolePatterns)) > 0;
                $checked = (count(array_intersect($patterns, (array)$effective)) > 0);
                $id = 'check_' . Str::slug($val);
            @endphp

            <div class="custom-control custom-checkbox mb-2 permission-item d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <input type="checkbox" class="custom-control-input perm-chk" id="{{ $id }}" value="{{ e($val) }}" @if($checked) checked @endif data-role="{{ $isRoleProvided ? '1' : '0' }}" @if(! $isRoleProvided) name="allow[]" @endif>
                    <label class="custom-control-label font-weight-normal mb-0" style="cursor:pointer; font-size:0.9rem; color:var(--text-main);" for="{{ $id }}">
                        {{ e($it['text']) }}
                    </label>
                </div>
                @if($isRoleProvided)
                    <i class="fas fa-id-badge ml-2" title="Provisto por Rol" style="font-size:0.75rem; color:var(--color-primary);"></i>
                @endif
            </div>
        @endforeach
    </div>
@endif