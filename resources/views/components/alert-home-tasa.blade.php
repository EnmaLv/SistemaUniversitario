@if (session()->has('tasa_pendiente') || session()->has('tasa_obligatoria'))

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // En vez de esperar, cierra el toast y muestra la tasa
        if (Swal.isVisible()) {
            Swal.close();
            setTimeout(mostrarAlertaTasa, 100);
        } else {
            mostrarAlertaTasa();
        }

        const isDark       = document.documentElement.classList.contains('dark');
        const primaryColor = '#9f1239';
        const actionUrl    = '{{ route('productos.actualizar.tasa') }}';
        const ignoreUrl    = '{{ route('tasa.ignorar') }}';
        const csrf         = '{{ csrf_token() }}';

        // Form se crea solo cuando el usuario confirma
        function enviarActualizacionTasa() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;
            form.style.display = 'none';

            const token = document.createElement('input');
            token.type  = 'hidden';
            token.name  = '_token';
            token.value = csrf;
            form.appendChild(token);

            document.body.appendChild(form);
            form.submit();
        }

        function ignorarTasa() {
            fetch(ignoreUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/json'
                }
            });
        }

        // Eleva el z-index del Swal para que SIEMPRE esté encima
        function elevarZIndex() {
            const containers = document.querySelectorAll('.swal2-container');
            containers.forEach(c => c.style.zIndex = '100000');
        }

        // Espera a que cualquier Swal previo cierre y luego muestra el de tasa
        function cuandoSwalLimpio(callback, intentos = 0) {
            if (!Swal.isVisible()) {
                callback();
                return;
            }
            if (intentos > 30) { // 15 seg máx
                callback();
                return;
            }
            setTimeout(() => cuandoSwalLimpio(callback, intentos + 1), 500);
        }

        function mostrarAlertaTasa() {
            @if (session()->has('tasa_obligatoria'))
                Swal.fire({
                    title: '🚨 Tasa requerida',
                    html: `<p class="text-sm opacity-90">Debe registrar la <b>tasa del dólar</b> para poder continuar.</p>`,
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Registrar tasa',
                    cancelButtonText: 'Más tarde',
                    confirmButtonColor: primaryColor,
                    cancelButtonColor: isDark ? '#374151' : '#6b7280',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: { popup: 'swal2-popup-custom' },
                    didOpen: elevarZIndex
                }).then((result) => {
                    if (result.isConfirmed) {
                        enviarActualizacionTasa();
                    } else {
                        ignorarTasa();
                    }
                });
            @elseif (session()->has('tasa_pendiente'))
                Swal.fire({
                    title: '🔄 Tasa no actualizada',
                    html: `<p class="text-sm opacity-90">Hay una tasa registrada, pero no corresponde al día de hoy.<br>¿Desea actualizarla ahora?</p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Actualizar',
                    cancelButtonText: 'Más tarde',
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: isDark ? '#374151' : '#6b7280',
                    customClass: { popup: 'swal2-popup-custom' },
                    didOpen: elevarZIndex
                }).then((result) => {
                    if (result.isConfirmed) {
                        enviarActualizacionTasa();
                    } else {
                        ignorarTasa();
                    }
                });
            @endif
        }

        // Arranca: si hay otro Swal, espera; si no, muestra de una
        cuandoSwalLimpio(mostrarAlertaTasa);

    });
</script>

@endif