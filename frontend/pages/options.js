import { api } from '../api.js';
import { escHtml, showLoading, showError } from '../app.js';

export async function render(container) {
    showLoading(container);
    let data;
    try {
        data = await api.getOptions();
    } catch (e) {
        showError(container, e.message);
        return;
    }

    const { items } = data;
    const keys = Object.keys(items);

    const rows = keys.map(key => {
        const { value, warning } = items[key];
        return `<tr data-key="${escHtml(key.toLowerCase())}" data-val="${escHtml(value.toLowerCase())}">
            <td class="${warning ? 'text-danger fw-semibold' : ''}">${escHtml(key)}</td>
            <td>${escHtml(value)}</td>
        </tr>`;
    }).join('');

    container.innerHTML = `
        <div class="mt-4">
            <div class="d-flex align-items-center mb-3 gap-3">
                <h5 class="mb-0">WordPress Options <span class="badge bg-secondary">${keys.length}</span></h5>
                <input id="options-search" type="text" class="form-control w-auto flex-grow-1"
                       placeholder="Filter by name or value…" autocomplete="off">
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-sm" id="options-table">
                    <thead class="table-dark sticky-top">
                        <tr><th style="width:30%">Name</th><th>Value</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
            <p id="no-results" class="text-secondary text-center py-3 d-none">No matching options.</p>
        </div>`;

    const tbody     = container.querySelector('#options-table tbody');
    const noResults = container.querySelector('#no-results');

    container.querySelector('#options-search').addEventListener('input', function () {
        const q   = this.value.toLowerCase();
        let visible = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            const match = !q || tr.dataset.key.includes(q) || tr.dataset.val.includes(q);
            tr.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        noResults.classList.toggle('d-none', visible > 0);
    });
}
