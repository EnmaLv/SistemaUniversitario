@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const Toast = window.Toast || Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: 'success',
            title: '{{ session('success') }}'
        });
    });
</script>
@endif

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const Toast = window.Toast || Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: 'error',
            title: '{{ session('error') }}'
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

        Swal.fire({
            title: title,
            text: text,
            icon: isActivate ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: isActivate ? '#10b981' : '#9f1239',
            cancelButtonColor: '#6b7280',
            confirmButtonText: isActivate ? 'Sí, activar' : 'Sí, inactivar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'rounded-2xl dark:bg-gray-800 dark:text-gray-100 dark:border dark:border-gray-700'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById(formPrefix + id);
                if (form) {
                    form.submit();
                }
            }
        });
    }
}
</script>