/**
 * Stealth Tools — main application entry point.
 *
 * Responsibilities:
 *  - Shared utilities: toast, clipboard, code rendering
 *  - Prism language extensions (sinks / sources)
 *  - Navbar rendering with badge counts
 *  - URL-based page router with dynamic ES module loading
 */

import { api } from './api.js';

// ---------------------------------------------------------------------------
// Config (injected by shell.php)
// ---------------------------------------------------------------------------

const PAGES = window.STEALTH_CONFIG?.pages ?? {};

// ---------------------------------------------------------------------------
// Toast notifications
// ---------------------------------------------------------------------------

export function toast(message, type = 'info') {
    const typeClass = { success: 'toast-header-success', error: 'toast-header-error', info: 'toast-header-info' }[type] ?? 'toast-header-info';
    const title     = { success: 'Success', error: 'Error', info: 'Info' }[type] ?? 'Info';

    const el = document.createElement('div');
    el.innerHTML = `
        <div class="toast m-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="4000">
            <div class="toast-header ${typeClass}">
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>`;
    document.getElementById('toast-container').appendChild(el);
    bootstrap.Toast.getOrCreateInstance(el.querySelector('.toast')).show();
}

// ---------------------------------------------------------------------------
// Clipboard
// ---------------------------------------------------------------------------

export function copyToClipboard(text, label = 'text') {
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text)
            .then(() => toast(`Copied ${label} to clipboard`, 'success'))
            .catch(() => fallbackCopy(text, label));
    } else {
        fallbackCopy(text, label);
    }
}

function fallbackCopy(text, label) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;opacity:0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); toast(`Copied ${label} to clipboard`, 'success'); }
    catch { toast('Copy failed', 'error'); }
    document.body.removeChild(ta);
}

/**
 * Wire up .copy-text spans inside a container after dynamic rendering.
 * Each span gets a clipboard emoji button appended.
 */
export function initCopyables(root = document) {
    root.querySelectorAll('.copy-text:not([data-copy-init])').forEach(span => {
        span.dataset.copyInit = '1';
        const btn = document.createElement('button');
        btn.className = 'copy-btn';
        btn.textContent = '📋';
        btn.addEventListener('click', () => {
            copyToClipboard(span.innerText.replace('📋', '').trim(), span.title || 'value');
        });
        span.appendChild(btn);
    });
}

// ---------------------------------------------------------------------------
// Code rendering
// ---------------------------------------------------------------------------

/**
 * Render a code block with an optional metadata bar above it.
 * Returns an HTMLElement ready to insert into the DOM.
 */
export function renderCode(code, language = 'php', meta = {}) {
    const wrap = document.createElement('div');

    // Metadata bar (filename, lines, function name, etc.)
    const metaEntries = Object.entries(meta).filter(([, v]) => v);
    if (metaEntries.length) {
        const bar = document.createElement('div');
        bar.className = 'code-meta';
        // Values may be pre-escaped plain text or safe HTML (e.g. <a> links)
        bar.innerHTML = metaEntries.map(([k, v]) =>
            `<span><strong>${escHtml(k)}:</strong> <span class="copy-text">${v}</span></span>`
        ).join('');
        wrap.appendChild(bar);
    }

    // Prism block
    const pre  = document.createElement('pre');
    const code_el = document.createElement('code');
    code_el.className = `language-${language}`;

    // PHP: strip leading <?php if present so Prism doesn't double-add it
    let src = (code ?? '').trim();
    if (language === 'php' && !src.startsWith('<?')) {
        src = '<?php\n' + src;
    }
    code_el.textContent = src;
    pre.appendChild(code_el);
    wrap.appendChild(pre);

    Prism.highlightElement(code_el);

    initCopyables(wrap);
    return wrap;
}

/**
 * Render analysis badge row (protection / info / bug).
 */
export function renderAnalysis(analysis) {
    if (!analysis || !Object.keys(analysis).length) return null;

    const colours = { protection: 'success', info: 'warning', bug: 'danger' };
    const row = document.createElement('div');
    row.className = 'analysis-badges mb-2';

    for (const [, item] of Object.entries(analysis)) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `btn btn-sm btn-${colours[item.type] ?? 'secondary'} me-1 mb-1`;
        btn.textContent = item.name;
        btn.title = item.description;
        row.appendChild(btn);
    }
    return row;
}

// ---------------------------------------------------------------------------
// Item / slug badge colours (mirrors PHP defaults.php)
// ---------------------------------------------------------------------------

const RANDOM_COLORS = ['blue','red','teal','orange','green','cyan','pink','lime','indigo','rose','amber','emerald','sky'];
const BADGE_COLOR_OVERRIDES = { theme: 'info', default: 'secondary', woocommerce: 'purple', elementor: 'yellow' };
const _colorCache = {};

export function badgeColor(itemType, slug) {
    const key = `${itemType}:${slug}`;
    if (_colorCache[key]) return _colorCache[key];

    let color;
    if (BADGE_COLOR_OVERRIDES[slug]) {
        color = BADGE_COLOR_OVERRIDES[slug];
    } else if (itemType === 'theme') {
        color = 'info';
    } else if (itemType === 'default') {
        color = 'secondary';
    } else {
        // Deterministic colour from slug string (mirrors PHP crc32 approach)
        let hash = 0;
        for (let i = 0; i < slug.length; i++) {
            hash = (Math.imul(31, hash) + slug.charCodeAt(i)) | 0;
        }
        color = RANDOM_COLORS[Math.abs(hash) % RANDOM_COLORS.length];
    }
    _colorCache[key] = color;
    return color;
}

export function itemBadge(itemType, slug, title = '') {
    const color = badgeColor(itemType, slug);
    return `<span class="badge badge-item bg-${color} me-2" title="${escHtml(title || slug)}">${escHtml(slug)}</span>`;
}

// ---------------------------------------------------------------------------
// Auth cookie display helper (used by login page)
// ---------------------------------------------------------------------------

export function currentPageUrl(page) {
    const url = new URL(window.location.href);
    url.searchParams.delete('stealth_api');
    url.searchParams.set('page', page);
    return url.toString();
}

// ---------------------------------------------------------------------------
// HTML escape
// ---------------------------------------------------------------------------

export function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ---------------------------------------------------------------------------
// Loading / empty / error state helpers
// ---------------------------------------------------------------------------

export function showLoading(container) {
    container.innerHTML = `
        <div class="d-flex justify-content-center align-items-center py-5">
            <div class="spinner-border text-primary me-3" role="status"></div>
            <span class="text-secondary">Loading…</span>
        </div>`;
}

export function showEmpty(container, message = 'Nothing to show.') {
    container.innerHTML = `<p class="text-secondary py-4 text-center">${escHtml(message)}</p>`;
}

export function showError(container, message) {
    container.innerHTML = `<div class="alert alert-danger mt-3">${escHtml(message)}</div>`;
}

// ---------------------------------------------------------------------------
// Shared code detail renderer (used by funcy / shorty / resty)
// ---------------------------------------------------------------------------

/**
 * Render a full code view: analysis badges + meta bar + syntax-highlighted block.
 * codeObj shape: { code, analysis, file, lines, function_name, parameters }
 */
export function renderCodeDetail(container, codeObj, extra = {}) {
    container.innerHTML = '';
    if (!codeObj?.code) {
        container.innerHTML = '<p class="text-secondary fst-italic">Could not retrieve function code 🥲</p>';
        return;
    }
    const analysis = renderAnalysis(codeObj.analysis);
    if (analysis) container.appendChild(analysis);

    const meta = { ...extra };
    if (codeObj.function_name) meta['Function'] = escHtml(codeObj.function_name);
    if (codeObj.file)          meta['File']     = escHtml(codeObj.file);
    if (codeObj.lines)         meta['Lines']    = escHtml(String(codeObj.lines));
    if (codeObj.parameters?.length) meta['Params'] = escHtml(codeObj.parameters.join(', '));

    container.appendChild(renderCode(codeObj.code, 'php', meta));
    initCopyables(container);
}

// ---------------------------------------------------------------------------
// Filters widget (shared by funcy / shorty / resty)
// ---------------------------------------------------------------------------

export async function renderFilters(container, onchange) {
    let data;
    try { data = await api.getFilters(); }
    catch { return; }

    const { available, state } = data;

    const card = document.createElement('div');
    card.className = 'filter-card mb-3';
    card.innerHTML = `<strong class="d-block mb-2">Filters</strong><div class="filter-switches d-flex flex-wrap gap-2"></div>`;

    const switches = card.querySelector('.filter-switches');
    for (const slug of available) {
        const color = badgeColor('plugin', slug);
        const checked = state[slug] !== false;
        const id = `fs_${slug}`;
        const wrap = document.createElement('div');
        wrap.className = 'form-check form-check-inline form-switch';
        wrap.innerHTML = `
            <input class="form-check-input form-check-input-${color}" type="checkbox" role="switch"
                   id="${id}" data-slug="${escHtml(slug)}" ${checked ? 'checked' : ''}>
            <label class="form-check-label" for="${id}">${escHtml(slug)}</label>`;
        switches.appendChild(wrap);
    }

    card.addEventListener('change', async () => {
        const newState = {};
        card.querySelectorAll('input[data-slug]').forEach(cb => {
            newState[cb.dataset.slug] = cb.checked;
        });
        // Persist via API (sets cookie) BEFORE calling onchange so that the
        // subsequent data fetch sees the updated cookie.
        try { await api.saveFilters(newState); } catch { /* ignore */ }
        refreshNavCounts(); // update badge counts to reflect new filter state
        if (onchange) onchange(newState);
    });

    container.appendChild(card);
}

// ---------------------------------------------------------------------------
// Prism extensions (sinks / sources) — loaded once from API
// ---------------------------------------------------------------------------

let prismExtensionsLoaded = false;

async function loadPrismExtensions() {
    if (prismExtensionsLoaded) return;
    prismExtensionsLoaded = true;
    try {
        const highlighting = await api.getHighlighting().catch(() => null);
        if (!highlighting) return;
        const { sinks = [], sources = [] } = highlighting;

        // Separate $variable-style sources from function-style
        const sourceVars  = sources.filter(s => s.startsWith('\\$'));
        const sourceFuncs = sources.filter(s => !s.startsWith('\\$'));

        if (sinks.length) {
            Prism.languages.insertBefore('php', 'function', {
                'function-sink': { pattern: new RegExp(`(${sinks.join('|')})`), alias: 'function-sink' },
            });
        }
        if (sourceFuncs.length) {
            Prism.languages.insertBefore('php', 'function', {
                'function-source': { pattern: new RegExp(`(${sourceFuncs.join('|')})`), alias: 'function-source' },
            });
        }
        if (sourceVars.length) {
            Prism.languages.insertBefore('php', 'variable', {
                'function-source': { pattern: new RegExp(`(${sourceVars.join('|')})`), alias: 'function-source' },
            });
        }
    } catch { /* non-fatal */ }
}

// ---------------------------------------------------------------------------
// Nav count badges — pie-chart style using conic-gradient
// ---------------------------------------------------------------------------

// Hex values that match the bg-* classes in style.css
const BADGE_COLOR_VALUES = {
    purple:    '#9B5DE5', yellow:  '#FACC15', blue:    '#3B82F6',
    red:       '#F43F5E', teal:    '#14B8A6', orange:  '#F97316',
    green:     '#22C55E', cyan:    '#06B6D4', pink:    '#EC4899',
    lime:      '#84CC16', indigo:  '#6366F1', rose:    '#F43F5E',
    amber:     '#F59E0B', emerald: '#10B981', sky:     '#0EA5E9',
    secondary: '#6C747D', info:    '#0DC9F0',
};

function makeCountBadge(count, breakdown) {
    if (count === undefined || count === null) return '';

    const segments = (breakdown ?? []).slice().sort((a, b) => b.count - a.count);
    let gradient;
    if (!segments.length || count === 0) {
        gradient = '#6C747D';
    } else {
        let cum = 0;
        const parts = segments.map(seg => {
            const pct  = (seg.count / count) * 100;
            const name = badgeColor(seg.item_type, seg.slug);
            const hex  = BADGE_COLOR_VALUES[name] ?? '#6C747D';
            const part = `${hex} ${cum.toFixed(2)}% ${(cum + pct).toFixed(2)}%`;
            cum += pct;
            return part;
        });
        gradient = `conic-gradient(${parts.join(', ')})`;
    }

    const tip = segments.map(s => `${s.slug}: ${s.count}`).join(' / ');
    return `<span class="nav-count-badge ms-1" style="background:${gradient}" title="${escHtml(tip)}"><span class="nav-count-num">${count}</span></span>`;
}

// ---------------------------------------------------------------------------
// Navbar
// ---------------------------------------------------------------------------

function buildNavbar(counts = {}) {
    const currentPage = getPage();
    const nav = document.getElementById('navbar');
    if (!nav) return;

    const badgePages = {
        funcy:   { count: counts.actions,    breakdown: counts.actions_breakdown },
        shorty:  { count: counts.shortcodes, breakdown: counts.shortcodes_breakdown },
        resty:   { count: counts.routes,     breakdown: counts.routes_breakdown },
        options: { count: counts.options,    breakdown: null },
    };

    const tabs = Object.entries(PAGES).map(([name, desc]) => {
        const active = name === currentPage ? 'active' : '';
        const bp = badgePages[name];
        const badge = bp ? makeCountBadge(bp.count, bp.breakdown) : '';
        return `<li class="nav-item">
            <a class="nav-link ${active}" href="${currentPageUrl(name)}" title="${escHtml(desc)}">${escHtml(name)}${badge}</a>
        </li>`;
    }).join('');

    nav.className = 'navbar navbar-expand-lg bg-body-tertiary pb-0';
    nav.innerHTML = `
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="${currentPageUrl('home')}">StealthTools</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarContent" aria-controls="navbarContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 nav-tabs">${tabs}</ul>

                <div class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">Quick Nav</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="${escHtml(window.STEALTH_CONFIG.siteUrl)}/wp-admin/post-new.php" target="_blank">Create New Post</a></li>
                        <li><a class="dropdown-item" href="${escHtml(window.STEALTH_CONFIG.siteUrl)}/wp-admin/users.php" target="_blank">Users</a></li>
                        <li><a class="dropdown-item" href="${escHtml(window.STEALTH_CONFIG.siteUrl)}/wp-admin/plugins.php" target="_blank">Plugins</a></li>
                        <li><a class="dropdown-item" href="${escHtml(window.STEALTH_CONFIG.siteUrl)}/wp-admin/themes.php" target="_blank">Themes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item d-flex align-items-center justify-content-between"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#nav-login-collapse"
                                    aria-expanded="false" aria-controls="nav-login-collapse">
                                Login as <span style="font-size:0.7em;opacity:0.5">▼</span>
                            </button>
                            <div class="collapse" id="nav-login-collapse">
                                <ul id="nav-login-submenu" class="list-unstyled mb-0 mt-1">
                                    <li><div class="px-3 py-1"><small class="text-secondary">Loading…</small></div></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>`;
}

// ---------------------------------------------------------------------------
// Router
// ---------------------------------------------------------------------------

function getPage() {
    return new URLSearchParams(window.location.search).get('page') || 'home';
}

async function loadPage(name) {
    const app = document.getElementById('app');
    showLoading(app);

    const frontendUrl = window.STEALTH_CONFIG?.frontendUrl ?? '';

    try {
        const { render } = await import(`${frontendUrl}pages/${name}.js`);
        app.innerHTML = '';
        await render(app);
    } catch (e) {
        app.innerHTML = `
            <div class="text-center py-5">
                <h4 class="text-secondary mb-2">${escHtml(name)}</h4>
                <p class="text-muted">This page hasn't been implemented yet — coming in Step 6.</p>
                <small class="text-muted">${escHtml(String(e))}</small>
            </div>`;
    }
}

// ---------------------------------------------------------------------------
// Boot
// ---------------------------------------------------------------------------

const COUNTS_CACHE_KEY = 'stealth_counts';
let _loginData = null; // cached {users, current} — avoids re-fetching on every navbar rebuild

// Role colours — mirrors login.js ROLE_COLOR but as hex for inline squares
const ROLE_COLOR_HEX = {
    administrator: '#dc3545',
    editor:        '#ffc107',
    author:        '#6c757d',
    contributor:   '#0d6efd',
    customer:      '#0dcaf0',
    subscriber:    '#198754',
};

function populateLoginSubmenu() {
    const submenu = document.getElementById('nav-login-submenu');
    if (!submenu) return;

    if (!_loginData?.users?.length) {
        submenu.innerHTML = `<li><div class="px-3 py-1"><small class="text-secondary">${_loginData ? 'No users found.' : 'Could not load users.'}</small></div></li>`;
        return;
    }

    const { users, current } = _loginData;
    submenu.innerHTML = '';
    for (const user of users) {
        const color = ROLE_COLOR_HEX[user.roles?.[0]] ?? '#adb5bd';
        const isCurrent = user.id === current?.id;
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.className = 'dropdown-item d-flex align-items-center gap-2' + (isCurrent ? ' active' : '');
        a.href = '#';
        a.innerHTML = `<span style="display:inline-block;width:10px;height:10px;border-radius:2px;flex-shrink:0;background:${color}"></span>
            <span>${escHtml(user.login)}</span>
            <small class="text-muted ms-auto">${escHtml(user.roles?.[0] ?? '')}</small>`;
        a.addEventListener('click', async e => {
            e.preventDefault();
            try {
                await api.login(user.id);
                window.open(window.STEALTH_CONFIG?.siteUrl + '/wp-admin/', '_blank');
                // Refresh cache to update the active marker
                _loginData = await api.getUsers();
                populateLoginSubmenu();
            } catch (err) {
                toast('Login failed: ' + err.message, 'error');
            }
        });
        li.appendChild(a);
        submenu.appendChild(li);
    }
}

async function loadNavLoginUsers() {
    try {
        _loginData = await api.getUsers();
    } catch {
        _loginData = null;
    }
    populateLoginSubmenu();
}

function refreshNavCounts() {
    api.getCounts().then(counts => {
        localStorage.setItem(COUNTS_CACHE_KEY, JSON.stringify(counts));
        buildNavbar(counts);
        populateLoginSubmenu(); // repopulate from cache after DOM rebuild
    }).catch(() => {});
}

async function init() {
    // Render navbar immediately using cached counts (avoids flicker on navigation)
    const cachedCounts = JSON.parse(localStorage.getItem(COUNTS_CACHE_KEY) || '{}');
    buildNavbar(cachedCounts);

    // Refresh counts in background and update cache + navbar when done
    refreshNavCounts();

    // Populate login-as user list in the Quick Nav dropdown
    loadNavLoginUsers();

    // Load Prism extensions in background
    loadPrismExtensions();

    // Load the current page
    await loadPage(getPage());
}

document.addEventListener('DOMContentLoaded', init);
