<form id="form-actualizar-tasa" action="{{ route('productos.actualizar.tasa') }}" method="POST" style="display:none;">
    @csrf
</form>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isDark = document.documentElement.classList.contains('dark');
        const primaryColor = '#9f1239';

        @if (session()->has('tasa_obligatoria'))
            Swal.fire({
                title: '🚨 Tasa requerida',
                html: `<p class="text-sm opacity-90">Debe registrar la <b>tasa del dólar</b> para poder navegar en el sistema.</p>`,
                icon: 'error',
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Registrar tasa',
                confirmButtonColor: primaryColor,
                customClass: {
                    popup: 'swal2-popup-custom'
                }
            }).then(() => {
                document.getElementById('form-actualizar-tasa').submit();
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
                customClass: {
                    popup: 'swal2-popup-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-actualizar-tasa').submit();
                } else {
                    fetch('{{ route('tasa.ignorar') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                }
            });
        @endif
    });
</script>