import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import Alpine from 'alpinejs';
import Collapse from '@alpinejs/collapse'

Alpine.plugin(Collapse)
window.Alpine = Alpine;
Livewire.start();

import './bootstrap';
import Swal from 'sweetalert2';
window.Swal = Swal;
import flatpickr from "flatpickr";
window.flatpickr = flatpickr;

// Configuración global de SweetAlert2
window.Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});