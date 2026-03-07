import { api } from '../api.js';
import { escHtml, showError, toast } from '../app.js';

export function render(container) {
    container.innerHTML = `
        <div class="mt-4">
            <p class="text-secondary">
                Install and activate plugins or themes by slug (wordpress.org) or zip URL.
                <a href="${window.STEALTH_CONFIG?.siteUrl ?? ''}/wp-admin/plugins.php" target="_blank"
                   class="btn btn-sm btn-outline-secondary ms-1">plugins.php ↗</a>
                <a href="${window.STEALTH_CONFIG?.siteUrl ?? ''}/wp-admin/themes.php" target="_blank"
                   class="btn btn-sm btn-outline-secondary ms-1">themes.php ↗</a>
            </p>

            ${installCard('plugin')}
            ${installCard('theme')}

            <div id="install-result" class="mt-3"></div>
        </div>`;

    container.querySelectorAll('.install-form').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const type     = form.dataset.type;
            const slug     = form.querySelector('.slug-input').value.trim();
            const activate = e.submitter?.name === 'activate';

            if (!slug) { toast('Enter a slug or URL first', 'error'); return; }

            const btn = e.submitter;
            btn.disabled = true;

            const result = container.querySelector('#install-result');
            result.innerHTML = `<div class="d-flex gap-2 align-items-center text-secondary py-2">
                <div class="spinner-border spinner-border-sm"></div>
                <span>Installing ${escHtml(slug)}…</span>
            </div>`;

            try {
                const data = await api.install(type, slug, activate);
                renderResult(result, data);
            } catch (err) {
                showError(result, err.message);
            } finally {
                btn.disabled = false;
            }
        });
    });
}

function installCard(type) {
    const label = type.charAt(0).toUpperCase() + type.slice(1);
    return `
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Install a ${label}</h5>
                <form class="install-form" data-type="${type}">
                    <div class="input-group">
                        <input class="form-control slug-input" type="text"
                               placeholder="${label} slug or zip URL">
                        <button class="btn btn-success" type="submit" name="install">Install</button>
                        <button class="btn btn-success" type="submit" name="activate">+ Activate</button>
                        <a class="btn btn-primary" id="wp-link-${type}" href="#" target="_blank" title="Open wordpress.org page">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 28 28" fill="currentColor">
                                <path d="M13.6 0.9C6.2 0.9 0.1 7 0.1 13.6s6.1 12.7 13.5 12.7 13.5-5.7 13.5-12.7S21 0.9 13.6 0.9z"/>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>
        </div>`;
}

function renderResult(container, data) {
    const stepRows = (data.steps ?? []).map(s => `
        <tr>
            <td>${escHtml(s.label)}</td>
            <td class="${s.success ? 'text-success' : 'text-danger'}">${s.success ? '✅' : '❌'} ${escHtml(s.message)}</td>
        </tr>`).join('');

    let infoCard = '';
    if (data.info) {
        const i = data.info;
        infoCard = `
            <div class="card mt-3">
                <div class="card-header">${escHtml(i.name ?? i.slug)} info</div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm mb-0">
                        <tbody>
                            ${i.name    ? `<tr><td>Name</td><td>${escHtml(i.name)}</td></tr>` : ''}
                            ${i.version ? `<tr><td>Version</td><td>${escHtml(i.version)}</td></tr>` : ''}
                            ${i.installs!== undefined ? `<tr><td>Active Installs</td><td>${Number(i.installs).toLocaleString()}</td></tr>` : ''}
                            ${i.updated ? `<tr><td>Last Updated</td><td>${escHtml(i.updated)}</td></tr>` : ''}
                            ${i.url     ? `<tr><td>URL</td><td><a href="${escHtml(i.url)}" target="_blank">${escHtml(i.url)}</a></td></tr>` : ''}
                        </tbody>
                    </table>
                </div>
            </div>`;
    }

    container.innerHTML = `
        <div class="card">
            <div class="card-header ${data.success ? 'bg-success' : 'bg-danger'}">
                ${data.success ? '✅' : '❌'} ${escHtml(data.type)} — ${escHtml(data.slug)}
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-sm mb-0">
                    <tbody>${stepRows}</tbody>
                </table>
            </div>
        </div>${infoCard}`;
}
