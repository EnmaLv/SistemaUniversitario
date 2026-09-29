import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Obtener valores de Vite o del Meta Tag inyectado por Laravel
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY || document.querySelector('meta[name="pusher-app-key"]')?.content;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || document.querySelector('meta[name="pusher-app-cluster"]')?.content || 'sa1';

if (pusherKey) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster,
        forceTLS: true,
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
        },
    });
} else {
    console.error('Error: No se encontró la PUSHER_APP_KEY en el entorno ni en los meta tags.');
}

window.axios.interceptors.request.use((config) => {
    if (window.Echo?.socketId()) {
        config.headers['X-Socket-ID'] = window.Echo.socketId();
    }
    return config;
});