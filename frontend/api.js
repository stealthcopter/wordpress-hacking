/**
 * API client module.
 *
 * All requests go to the current page URL with ?stealth_api= appended,
 * so it works transparently in both plugin mode (/stealth/) and standalone
 * mode (/stealth/stealth.php).
 */

function apiUrl(endpoint, params = {}) {
    const url = new URL(window.location.href);
    // Strip UI params so they don't leak into API requests
    url.searchParams.delete('page');
    url.searchParams.delete('stealth_api');
    url.searchParams.set('stealth_api', endpoint);
    for (const [k, v] of Object.entries(params)) {
        url.searchParams.set(k, v);
    }
    return url.toString();
}

async function get(endpoint, params = {}) {
    const r = await fetch(apiUrl(endpoint, params));
    if (!r.ok) {
        const err = await r.json().catch(() => ({ error: `HTTP ${r.status}` }));
        throw new Error(err.error || `HTTP ${r.status}`);
    }
    return r.json();
}

/** POST with FormData (PHP reads via $_POST / $_FILES). */
async function postForm(endpoint, data = {}) {
    const fd = data instanceof FormData ? data : formData(data);
    const r = await fetch(apiUrl(endpoint), { method: 'POST', body: fd });
    if (!r.ok) {
        const err = await r.json().catch(() => ({ error: `HTTP ${r.status}` }));
        throw new Error(err.error || `HTTP ${r.status}`);
    }
    return r.json();
}

/** POST with JSON body (PHP reads via php://input). */
async function postJson(endpoint, body = {}) {
    const r = await fetch(apiUrl(endpoint), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
    });
    if (!r.ok) {
        const err = await r.json().catch(() => ({ error: `HTTP ${r.status}` }));
        throw new Error(err.error || `HTTP ${r.status}`);
    }
    return r.json();
}

function formData(obj) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(obj)) {
        fd.append(k, v);
    }
    return fd;
}

export const api = {
    // Navbar counts
    getCounts: () => get('counts'),

    // Prism highlighting patterns (lightweight, no phpinfo)
    getHighlighting: () => get('highlighting'),

    // Info page
    getInfo: () => get('info'),

    // Actions / funcy
    getActions:     ()   => get('actions'),
    getActionDetail: (id) => get('actions', { id }),

    // Shortcodes / shorty
    getShortcodes:        ()          => get('shortcodes'),
    getShortcodeDetail:   (tag)       => get('shortcodes', { tag }),
    getShortcodeAttrs:    (tag)       => get('shortcodes', { tag, attrs: 1 }),
    executeShortcode:     (shortcode) => postForm('shortcodes_execute', { shortcode: btoa(shortcode) }),

    // REST routes / resty
    getRoutes:      ()   => get('routes'),
    getRouteDetail: (id) => get('routes', { id }),

    // Nonce / noncy
    getNonce: (action) => get('nonce', { action }),

    // Login / user switcher
    getUsers: ()    => get('users'),
    login:    (uid) => postForm('login', { uid }),

    // Options
    getOptions: () => get('options'),

    // Gadgets
    getGadgetsInfo: ()     => get('gadgets_info'),
    installLfi:     (path) => postForm('gadgets_lfi', { path }),

    // Installer
    install: (type, slug, activate = false) =>
        postForm('install', { type, slug, ...(activate ? { activate: '1' } : {}) }),

    // Filters
    getFilters:   ()      => get('filters'),
    saveFilters:  (state) => postJson('filters', state),

    // Upload echo
    uploadEcho: (formDataObj) => postForm('upload_echo', formDataObj),
};
