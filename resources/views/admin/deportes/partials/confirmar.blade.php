@once
    <script>
        function confirmarFormularioDeporte(formulario, titulo, texto, textoBoton) {
            if (typeof Swal === 'undefined') {
                if (confirm(titulo + '\n\n' + texto)) {
                    formulario.submit();
                }
                return false;
            }

            Swal.fire({
                title: titulo,
                text: texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#991b1b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: textoBoton || 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then(function (resultado) {
                if (resultado.isConfirmed) {
                    formulario.submit();
                }
            });

            return false;
        }

        document.addEventListener('submit', function (evento) {
            const formulario = evento.target;
            if (!formulario.matches || !formulario.matches('form[data-confirmar]') || formulario.dataset.confirmado === '1') {
                return;
            }
            evento.preventDefault();
            confirmarFormularioDeporte(formulario, formulario.dataset.confirmar, formulario.dataset.confirmarTexto || '', formulario.dataset.confirmarBoton);
        }, true);
    </script>
@endonce
