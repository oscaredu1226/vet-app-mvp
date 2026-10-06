import _ from 'lodash';
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// MVP_POSTERIOR: WebSockets y Laravel Reverb
// MVP_POSTERIOR | import Echo from 'laravel-echo';
// MVP_POSTERIOR | 
// MVP_POSTERIOR | import Pusher from 'pusher-js';
// MVP_POSTERIOR | window.Pusher = Pusher;
// MVP_POSTERIOR | 
// MVP_POSTERIOR | window.Echo = new Echo({
// MVP_POSTERIOR |     broadcaster: 'reverb',
// MVP_POSTERIOR |     key: import.meta.env.VITE_REVERB_APP_KEY,
// MVP_POSTERIOR |     wsHost: import.meta.env.VITE_REVERB_HOST,
// MVP_POSTERIOR |     wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
// MVP_POSTERIOR |     wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
// MVP_POSTERIOR |     forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
// MVP_POSTERIOR |     enabledTransports: ['ws', 'wss'],
// MVP_POSTERIOR | });
