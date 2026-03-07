import { api } from '../api.js';
import { escHtml, showLoading, showError, showEmpty, toast,
         renderCodeDetail, renderFilters, itemBadge } from '../app.js';

export async function render(container) {
    showLoading(container);
    let data;
    try {
        data = await api.getShortcodes();
    } catch (e) {
        showError(container, e.message);
        return;
    }

    container.innerHTML = `
        <div class="mt-4">
            <p class="text-secondary">
                Shortcodes registered with
                <a href="https://developer.wordpress.org/reference/functions/add_shortcode/"
                   class="inline-code" target="_blank">add_shortcode</a>.
                Click a shortcode to inspect it, or use the executor directly.
                Note: some shortcodes only function correctly when rendered inside a post or page.
                <a href="${window.STEALTH_CONFIG?.siteUrl ?? ''}/wp-admin/post-new.php" target="_blank"
                   class="btn btn-sm btn-outline-secondary ms-1">post-new.php ↗</a>
            </p>

            <div id="filter-area"></div>
            <div id="shortcode-list"></div>

            <!-- Executor (always visible, populated when a shortcode is selected) -->
            <div class="card mt-3" id="sc-executor">
                <div class="card-header fw-semibold">Execute Shortcode</div>
                <div class="card-body">
                    <div class="input-group mb-2">
                        <textarea id="sc-quick-input" class="form-control font-monospace" rows="2"
                                  placeholder='[my_shortcode attr="value"]'></textarea>
                        <button id="btn-quick-run" class="btn btn-success">Run</button>
                    </div>
                    <div id="sc-quick-output" class="d-none">
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <strong class="d-block mb-1">Rendered Output</strong>
                                <iframe id="sc-quick-iframe" sandbox="allow-same-origin allow-scripts"
                                        style="width:100%;min-height:120px;border:1px solid #444;border-radius:4px;background:#fff"
                                        title="Shortcode output"></iframe>
                            </div>
                            <div class="col-md-6">
                                <strong class="d-block mb-1">Raw HTML</strong>
                                <div id="sc-quick-raw" style="min-height:120px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="shortcode-detail" class="mt-4"></div>
        </div>`;

    const listEl   = container.querySelector('#shortcode-list');
    const detailEl = container.querySelector('#shortcode-detail');

    // Wire up the always-visible quick executor
    async function runShortcode(sc, outputArea, iframe, rawDiv, btn) {
        btn.disabled = true;
        outputArea.classList.remove('d-none');
        try {
            const res = await api.executeShortcode(sc);
            if (res.empty) {
                iframe.srcdoc = '<body style="margin:8px;font-family:sans-serif;color:#888">Empty output</body>';
                rawDiv.innerHTML = '<p class="text-secondary">Empty output. Some shortcodes require inner content or a closing tag, e.g. <code>[tag attr="val"]content[/tag]</code>.</p>';
            } else {
                iframe.srcdoc = res.output;
                rawDiv.innerHTML = '';
                const pre = document.createElement('pre');
                const code = document.createElement('code');
                code.className = 'language-html';
                code.textContent = res.output;
                pre.appendChild(code);
                rawDiv.appendChild(pre);
                Prism.highlightElement(code);
            }
        } catch (err) {
            rawDiv.innerHTML = `<div class="alert alert-danger">${escHtml(err.message)}</div>`;
        } finally {
            btn.disabled = false;
        }
    }

    container.querySelector('#btn-quick-run').addEventListener('click', () => {
        const sc = container.querySelector('#sc-quick-input').value;
        if (!sc.trim()) return;
        runShortcode(
            sc,
            container.querySelector('#sc-quick-output'),
            container.querySelector('#sc-quick-iframe'),
            container.querySelector('#sc-quick-raw'),
            container.querySelector('#btn-quick-run'),
        );
    });

    function renderList(items) {
        const entries = Object.values(items);
        if (!entries.length) { showEmpty(listEl, 'No shortcodes registered.'); return; }

        listEl.innerHTML = `
            <h5 class="mb-3">Registered Shortcodes <span class="badge bg-secondary">${entries.length}</span></h5>
            <ul class="list-unstyled" id="sc-ul">
                ${entries.map(sc => `
                    <li class="py-1 border-bottom border-secondary d-flex align-items-center gap-2" style="border-color:#333!important">
                        ${itemBadge(sc.item_type, sc.slug)}
                        <a href="#" class="sc-link fw-semibold" data-tag="${escHtml(sc.tag)}">${escHtml(sc.tag)}</a>
                        <span class="text-secondary">→ <code>${escHtml(sc.function_name)}</code></span>
                        <small class="text-muted ms-auto">${sc.attr_count} attr${sc.attr_count !== 1 ? 's' : ''}</small>
                    </li>`).join('')}
            </ul>`;

        listEl.querySelectorAll('.sc-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                loadDetail(a.dataset.tag);
            });
        });
    }

    // Load shortcode detail
    async function loadDetail(tag) {
        detailEl.innerHTML = `<div class="d-flex gap-2 align-items-center text-secondary py-2">
            <div class="spinner-border spinner-border-sm"></div><span>Loading ${escHtml(tag)}…</span>
        </div>`;
        detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

        let detail;
        try {
            detail = await api.getShortcodeDetail(tag);
        } catch (e) {
            showError(detailEl, e.message);
            return;
        }

        const attrs = detail.attributes ?? [];
        const testShortcode = `[${tag} ${attrs.map(a => `${a}="test"`).join(' ')}]`;

        // Populate the executor with a pre-filled test shortcode
        const quickInput = container.querySelector('#sc-quick-input');
        quickInput.value = testShortcode;
        container.querySelector('#sc-executor').scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        const attrBtns = attrs.map(a =>
            `<button class="btn btn-sm btn-outline-success me-1 mb-1 attr-btn"
                     data-attr="${escHtml(a)}">${escHtml(a)}</button>`
        ).join('');

        detailEl.innerHTML = `
            <hr class="border-danger border-2">
            <h4>Shortcode: [${escHtml(tag)}]</h4>

            ${attrs.length ? `
            <div class="mb-3">
                <strong class="d-block mb-2">Attributes</strong>
                <div>${attrBtns}</div>
            </div>` : '<p class="text-muted">No attributes detected.</p>'}

            <hr class="border-danger border-2 mt-4">
            <h5>Function Code</h5>
            <div id="sc-code"></div>`;

        // Attr buttons append to the executor textarea
        detailEl.querySelectorAll('.attr-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const cur = quickInput.value;
                if (!cur.includes(btn.dataset.attr + '=')) {
                    quickInput.value = cur.replace(/\]$/, ` ${btn.dataset.attr}="test"]`);
                }
            });
        });

        // Code view
        const codeEl = detailEl.querySelector('#sc-code');
        renderCodeDetail(codeEl, detail.code);
    }

    // Initial render
    renderList(data.items);

    // Filter widget — reload list when filter changes
    await renderFilters(container.querySelector('#filter-area'), async () => {
        showLoading(listEl);
        try {
            const refreshed = await api.getShortcodes();
            renderList(refreshed.items);
        } catch { /* ignore */ }
    });

    // Re-append filter before list (renderFilters appended to filter-area, which is already in place)

    // If URL has ?shortcode= param, auto-load it
    const preselect = new URLSearchParams(window.location.search).get('shortcode');
    if (preselect) loadDetail(preselect);
}
