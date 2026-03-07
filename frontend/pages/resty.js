import { api } from '../api.js';
import { escHtml, showLoading, showError, showEmpty,
         renderCodeDetail, renderFilters, itemBadge } from '../app.js';

const METHOD_COLOR = { GET:'success', POST:'primary', PUT:'warning', PATCH:'info', DELETE:'danger' };

function methodBadge(method, route) {
    const color = METHOD_COLOR[method] ?? 'secondary';
    return `<span class="badge method-badge bg-${color} me-1"
                  style="cursor:pointer;min-width:5em"
                  data-method="${escHtml(method)}"
                  data-route="${escHtml(route)}">${escHtml(method)}</span>`;
}

export async function render(container) {
    showLoading(container);
    let data;
    try {
        data = await api.getRoutes();
    } catch (e) {
        showError(container, e.message);
        return;
    }

    const { namespaces, items } = data;
    const siteUrl = window.STEALTH_CONFIG?.siteUrl ?? '';

    container.innerHTML = `
        <div class="mt-4">
            <p class="text-secondary">
                REST API routes registered with
                <a href="https://developer.wordpress.org/reference/functions/register_rest_route/"
                   class="inline-code" target="_blank">register_rest_route</a>.
                Click a <strong>method badge</strong> to see the raw HTTP request,
                or click the <strong>function name</strong> to view its source.
            </p>
            <div id="filter-area"></div>
            <div class="accordion accordion-flush mb-4" id="resty-accordion"></div>
            <div id="resty-detail" class="mt-2"></div>
        </div>

        <!-- Raw HTTP request modal -->
        <div class="modal fade" id="httpModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Raw HTTP Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <pre><code id="http-request-code" class="language-http"></code></pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>`;

    const accordionEl = container.querySelector('#resty-accordion');
    const detailEl    = container.querySelector('#resty-detail');
    const httpModal   = bootstrap.Modal.getOrCreateInstance(container.querySelector('#httpModal'));
    const httpCode    = container.querySelector('#http-request-code');

    const selectedId  = parseInt(new URLSearchParams(window.location.search).get('id') ?? '', 10);

    // Auth cookies for the raw request preview
    let authCookies = '';
    try {
        const users = await api.getUsers();
        authCookies = users.cookies ?? '';
    } catch { /* non-fatal */ }

    function buildRawRequest(method, route, params, cookies) {
        const urlObj    = new URL(siteUrl);
        const basePath  = urlObj.pathname === '/' ? '' : urlObj.pathname;
        const queryStr  = params.map(p => `${encodeURIComponent(p)}=value`).join('&');

        let path = `/wp-json${basePath}${route}`;
        let body = '';
        let contentType = '';

        if (method === 'GET' && queryStr) {
            path += `?${queryStr}`;
        } else if (method !== 'GET' && queryStr) {
            contentType = 'Content-Type: application/x-www-form-urlencoded\n';
            body = `\n${queryStr}`;
        }

        return `${method} ${path} HTTP/1.1\nHost: ${urlObj.host}\nCookie: ${cookies}\nAccept: application/json\n${contentType}${body}`.trim();
    }

    function renderAccordion(items) {
        accordionEl.innerHTML = '';

        // Group routes by namespace
        const grouped = {};
        for (const ns of namespaces) grouped[ns] = [];
        for (const route of items) {
            if (grouped[route.namespace] !== undefined) {
                grouped[route.namespace].push(route);
            } else {
                (grouped['none'] = grouped['none'] ?? []).push(route);
            }
        }

        Object.entries(grouped).forEach(([ns, routes], i) => {
            const isEmpty    = routes.length === 0;
            const hasSelected = routes.some(r => r.id === selectedId);
            const shouldOpen  = !isEmpty && (hasSelected || (!isNaN(selectedId) ? false : i === 0));

            const listItems = routes.map(route => {
                const methods     = route.method.split(', ');
                const methodBadges= methods.map(m => methodBadge(m, route.route)).join('');
                const noAuthBadge = route.no_auth
                    ? `<span class="badge bg-danger ms-1" title="No authentication required">No Auth</span>` : '';
                const paramInfo   = route.parameters?.length
                    ? `<small class="text-muted ms-1">— ${route.parameters.length} param${route.parameters.length !== 1 ? 's' : ''}</small>` : '';
                const fnLabel     = route.callback === 'Closure' ? '<em>Closure</em>' : escHtml(route.callback);

                return `<li class="py-2 border-bottom d-flex align-items-center gap-2 flex-wrap"
                             style="border-color:#444!important">
                    ${itemBadge(route.item_type, route.slug)}
                    <span>${methodBadges}</span>
                    <code>${escHtml(route.route)}</code>
                    <span class="text-secondary">→</span>
                    <a href="#" class="route-link" data-id="${route.id}">${fnLabel}</a>
                    ${noAuthBadge}${paramInfo}
                </li>`;
            }).join('');

            const item = document.createElement('div');
            item.className = 'accordion-item';
            item.innerHTML = `
                <h2 class="accordion-header">
                    <button class="accordion-button ${shouldOpen ? '' : 'collapsed'} bg-secondary text-white ${isEmpty ? 'opacity-50' : ''}"
                            type="button" data-bs-toggle="collapse"
                            data-bs-target="#resty-collapse-${i}">
                        Namespace: ${escHtml(ns)} (${routes.length})
                    </button>
                </h2>
                <div id="resty-collapse-${i}" class="accordion-collapse collapse ${shouldOpen ? 'show' : ''}"
                     style="background:#2d2d2d">
                    <div class="accordion-body py-2">
                        ${isEmpty
                            ? '<span class="text-secondary">No routes in this namespace.</span>'
                            : `<ul class="list-unstyled mb-0">${listItems}</ul>`}
                    </div>
                </div>`;
            accordionEl.appendChild(item);
        });

        // Method badge → raw HTTP modal
        accordionEl.querySelectorAll('.method-badge').forEach(badge => {
            badge.addEventListener('click', () => {
                const route  = items.find(r => r.route === badge.dataset.route);
                const params = route?.parameters ?? [];
                const raw    = buildRawRequest(badge.dataset.method, badge.dataset.route, params, authCookies);
                httpCode.textContent = raw;
                Prism.highlightElement(httpCode);
                httpModal.show();
            });
        });

        // Route link → code detail
        accordionEl.querySelectorAll('.route-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                loadDetail(parseInt(a.dataset.id, 10));
            });
        });

        if (!isNaN(selectedId)) loadDetail(selectedId);
    }

    async function loadDetail(id) {
        detailEl.innerHTML = `<div class="d-flex gap-2 align-items-center text-secondary py-2">
            <div class="spinner-border spinner-border-sm"></div><span>Loading…</span>
        </div>`;
        detailEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        let detail;
        try {
            detail = await api.getRouteDetail(id);
        } catch (e) {
            showError(detailEl, e.message);
            return;
        }

        detailEl.innerHTML = `<hr class="border-danger border-2"><h5 id="function_code">Function Code</h5>`;

        // Permission callback (if meaningful)
        const perm = detail.permission_callback;
        if (perm && !['none', '__return_true'].includes(perm) && detail.permission_code?.code) {
            const permHead = document.createElement('h6');
            permHead.textContent = 'Permission Callback';
            detailEl.appendChild(permHead);
            const permWrap = document.createElement('div');
            detailEl.appendChild(permWrap);
            renderCodeDetail(permWrap, detail.permission_code, { Function: escHtml(perm) });
        }

        const extra = { Route: escHtml(detail.route), 'Method(s)': escHtml(detail.method) };
        if (detail.parameters?.length) extra['Parameters'] = escHtml(detail.parameters.join(', '));

        const cbHead = document.createElement('h6');
        cbHead.textContent = 'Callback';
        detailEl.appendChild(cbHead);

        const codeWrap = document.createElement('div');
        detailEl.appendChild(codeWrap);
        renderCodeDetail(codeWrap, detail.callback_code, extra);
    }

    renderAccordion(items);

    // Filter widget
    await renderFilters(container.querySelector('#filter-area'), async () => {
        showLoading(accordionEl);
        try {
            const refreshed = await api.getRoutes();
            renderAccordion(refreshed.items);
        } catch { /* ignore */ }
    });
}
