@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const primaryColor = getComputedStyle(document.documentElement)
                .getPropertyValue('--color-primary').trim() || '#dc2626';

            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: primaryColor,
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: '{{ session('error') }}',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#ef4444'
            });
        });
    </script>
@endif

<script>
    if (typeof confirmToggleEstado === 'undefined') {
        function confirmToggleEstado(id, action = 'inactivar', formPrefix = 'form-toggle-') {
            const isActivate = action === 'activar';
            const title = isActivate ? '¿Activar registro?' : '¿Inactivar registro?';
            const text = isActivate ?
                'El registro volverá a estar disponible en el sistema.' :
                'El registro dejará de estar disponible en el sistema.';

            window.AppModal.show(title, text, {
                type: 'confirm',
                btnText: isActivate ? 'Sí, activar' : 'Sí, inactivar',
                intent: isActivate ? 'success' : 'danger'
            }).then(result => {
                if (result) {
                    const form = document.getElementById(formPrefix + id);
                    if (form) {
                        form.submit();
                    }
                }
            });
        }
    }
</script>