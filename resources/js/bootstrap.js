import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const getCookieValue = (name) => {
    return document.cookie
        .split("; ")
        .find((cookie) => cookie.startsWith(`${name}=`))
        ?.split("=")
        .slice(1)
        .join("=");
};

window.csrfFetch = async (url, options = {}) => {
    const init = { ...options };
    init.headers = new Headers(init.headers || {});

    const token = getCookieValue("XSRF-TOKEN");
    if (token) {
        init.headers.set("X-XSRF-TOKEN", decodeURIComponent(token));
    }

    if (!init.headers.has("Accept")) {
        init.headers.set("Accept", "application/json");
    }

    if (
        init.body &&
        !(init.body instanceof FormData) &&
        !init.headers.has("Content-Type")
    ) {
        init.headers.set("Content-Type", "application/json");
    }

    if (!init.credentials) {
        init.credentials = "include";
    }

    return fetch(url, init);
};

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
