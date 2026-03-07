import { api } from '../api.js';
import { escHtml, showLoading, showError, showEmpty, toast,
         renderCodeDetail, renderFilters, itemBadge } from '../app.js';

// Hook prefix groups, in display order
const GROUPS = [
    { prefix: 'wp_ajax_nopriv_', label: 'wp_ajax_nopriv_', unauth: true },
    { prefix: 'wp_ajax_',        label: 'wp_ajax_',         unauth: false },
    { prefix: 'admin_post_nopriv_', label: 'admin_post_nopriv_', unauth: true },
    { prefix: 'admin_post',      label: 'admin_post',        unauth: false },
    { prefix: 'init',            label: 'init',              unauth: true },
    { prefix: 'admin_init',      label: 'admin_init',        unauth: false },
];

export async function render(container) {
    showLoading(container);
    let data;
    try {
        data = await api.getActions();
    } catch (e) {
        showError(container, e.message);
        return;
    }

    container.innerHTML = `
        <div class="mt-4">
            <p class="text-secondary">
                Functions registered with
                <a href="https://developer.wordpress.org/reference/functions/add_action/"
                   class="inline-code" target="_blank">add_action</a>
                for AJAX, admin-post, and init hooks.
                Click a function name to view its source code.
            </p>
            <div id="filter-area"></div>
            <div class="accordion accordion-flush mb-4" id="funcy-accordion"></div>
            <div id="funcy-detail" class="mt-2"></div>
        </div>`;

    const accordionEl = container.querySelector('#funcy-accordion');
    const detailEl    = container.querySelector('#funcy-detail');

    // Pre-selected action from URL
    const selectedId = new URLSearchParams(window.location.search).get('action');

    function renderAccordion(items) {
        accordionEl.innerHTML = '';
        GROUPS.forEach((group, i) => {
            const matches = Object.entries(items).filter(([, e]) => e.hook.startsWith(group.prefix) && (
                group.prefix.includes('nopriv') || !e.hook.includes('_nopriv_')
            ));

            const authBadge = group.unauth
                ? `<span class="badge bg-danger ms-2">Unauthenticated</span>`
                : `<span class="badge bg-info ms-2 text-dark">Authenticated</span>`;

            const title = `${escHtml(group.label)} (${matches.length})${authBadge}`;

            const isEmpty = matches.length === 0;
            const shouldOpen = !isEmpty && (
                matches.some(([id]) => id === selectedId) ||
                (!selectedId && i === 0 && matches.length > 0)
            );

            const listItems = matches.map(([id, entry]) => {
                const actionLabel = entry.action === 'Closure' ? 'Closure' : entry.action;
                const suffix = entry.hook.slice(group.prefix.length);
                const display = suffix && suffix !== entry.hook
                    ? `${escHtml(suffix)} → <a href="#" class="action-link" data-id="${escHtml(id)}">${escHtml(actionLabel)}</a>`
                    : `<a href="#" class="action-link" data-id="${escHtml(id)}">${escHtml(actionLabel)}</a>`;
                return `<li class="py-1 border-bottom d-flex align-items-center gap-2"
                             style="border-color:#444!important">
                    ${itemBadge(entry.item_type, entry.slug)}
                    <span>${display}</span>
                </li>`;
            }).join('');

            const item = document.createElement('div');
            item.className = 'accordion-item';
            item.innerHTML = `
                <h2 class="accordion-header">
                    <button class="accordion-button ${shouldOpen ? '' : 'collapsed'} bg-secondary text-white ${isEmpty ? 'opacity-50' : ''}"
                            type="button" data-bs-toggle="collapse"
                            data-bs-target="#funcy-collapse-${i}">
                        ${title}
                    </button>
                </h2>
                <div id="funcy-collapse-${i}" class="accordion-collapse collapse ${shouldOpen ? 'show' : ''}"
                     style="background:#2d2d2d">
                    <div class="accordion-body py-2">
                        ${isEmpty
                            ? '<span class="text-secondary">No functions defined.</span>'
                            : `<ul class="list-unstyled mb-0">${listItems}</ul>`}
                    </div>
                </div>`;
            accordionEl.appendChild(item);
        });

        // Bind action links
        accordionEl.querySelectorAll('.action-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                loadDetail(a.dataset.id);
            });
        });

        // Auto-load preselected
        if (selectedId) loadDetail(selectedId);
    }

    async function loadDetail(id) {
        detailEl.innerHTML = `<div class="d-flex gap-2 align-items-center text-secondary py-2">
            <div class="spinner-border spinner-border-sm"></div><span>Loading…</span>
        </div>`;
        detailEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        let detail;
        try {
            detail = await api.getActionDetail(id);
        } catch (e) {
            showError(detailEl, e.message);
            return;
        }

        detailEl.innerHTML = `<hr class="border-danger border-2"><h5 id="function_code">Function Code</h5>`;

        const extra = {};
        if (detail.hook)  extra['Action'] = escHtml(detail.hook);
        if (detail.link)  extra['URL']    = `<a href="${escHtml(detail.link)}" target="_blank">${escHtml(detail.link)}</a>`;

        const codeWrap = document.createElement('div');
        detailEl.appendChild(codeWrap);
        renderCodeDetail(codeWrap, detail, extra);
    }

    // Render initial accordion
    renderAccordion(data.items);

    // Filter widget
    await renderFilters(container.querySelector('#filter-area'), async () => {
        showLoading(accordionEl);
        try {
            const refreshed = await api.getActions();
            renderAccordion(refreshed.items);
        } catch { /* ignore */ }
    });
}
