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
