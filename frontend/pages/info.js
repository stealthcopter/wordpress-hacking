import { api } from '../api.js';
import { escHtml, showLoading, showError, badgeColor } from '../app.js';

export async function render(container) {
    showLoading(container);
    let data;
    try {
        data = await api.getInfo();
    } catch (e) {
        showError(container, e.message);
        return;
    }

    const { summary, active_plugins, phpinfo_html } = data;

    const siteUrl = summary.site_url ?? '';

    const extIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16" class="ms-1 opacity-50">
        <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
        <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
    </svg>`;

    function colorDot(itemType, slug) {
        const color = badgeColor(itemType, slug);
        return `<span class="bg-${color}" style="display:inline-block;width:10px;height:10px;border-radius:2px;flex-shrink:0;vertical-align:middle" title="${escHtml(slug)}"></span>`;
    }

    const themeSlug = summary.theme_name?.toLowerCase().replace(/\s+/g, '-') ?? 'theme';
    const themeLink = summary.theme_uri
        ? `<a href="${escHtml(summary.theme_uri)}" target="_blank" title="${escHtml(summary.theme_uri)}">${extIcon}</a>`
        : '';
    const themeDot = colorDot('theme', themeSlug);

    const summaryRows = [
        ['WordPress Version', escHtml(summary.wp_version)],
        ['Site URL',          `<a href='${escHtml(summary.site_url)}' target="_blank">${escHtml(summary.site_url)}</a>`],
        ['Theme',             `${themeDot} ${escHtml(summary.theme_name)} ${escHtml(summary.theme_version)}${themeLink}`],
        ['MySQL Version',     escHtml(summary.mysql_version)],
        ['Multisite',         summary.multisite ? 'Yes' : 'No'],
        ['PHP Version',       escHtml(summary.php_version)],
        ['PHP OS',            escHtml(summary.php_os)],
        ['Server Software',   escHtml(summary.server_software)],
        ['ABSPATH',           escHtml(summary.abspath)],
    ].map(([k, v]) => `<tr><td class="fw-semibold ps-3" style="width:40%">${k}</td><td class="ps-2">${v}</td></tr>`).join('');

    const pluginRows = (active_plugins ?? []).map(p => {
        const slug = p.file.split('/')[0];
        const uri  = p.plugin_uri || `https://wordpress.org/plugins/${slug}`;
        const link = `<a href="${escHtml(uri)}" target="_blank" title="${escHtml(uri)}">${extIcon}</a>`;
        const dot  = colorDot('plugin', slug);
        return `<tr>
            <td class="ps-3"><span class="d-flex align-items-center gap-2">${dot}${escHtml(slug)}${link}</span></td>
            <td class="ps-2"><code>${escHtml(p.version)}</code></td>
            <td class="ps-2 text-secondary" style="font-size:0.8rem">${escHtml(p.file)}</td>
        </tr>`;
    }).join('');

    container.innerHTML = `
        <div class="mt-4">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header fw-semibold">WordPress &amp; Environment</div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <tbody>${summaryRows}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header fw-semibold d-flex align-items-center gap-2">
                            Active Plugins <span class="badge bg-secondary">${active_plugins?.length ?? 0}</span>
                            <a href="${escHtml(siteUrl)}/wp-admin/plugins.php" target="_blank"
                               class="btn btn-sm btn-outline-secondary ms-auto">plugins.php ↗</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                                <table class="table table-striped mb-0">
                                    <thead class="table-dark sticky-top">
                                        <tr><th class="ps-3">Name</th><th class="ps-2">Version</th><th class="ps-2">File</th></tr>
                                    </thead>
                                    <tbody>${pluginRows || '<tr><td colspan="3" class="text-secondary ps-3">No active plugins.</td></tr>'}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h5 class="mb-3">phpinfo()</h5>
                <iframe id="phpinfo-frame" sandbox="allow-same-origin"
                        style="width:100%;border:1px solid #444;border-radius:4px;min-height:600px;background:#fff"
                        title="phpinfo"></iframe>
            </div>

        </div>`;

    // Inject phpinfo into iframe via srcdoc to isolate its styles
    const frame = container.querySelector('#phpinfo-frame');
    if (phpinfo_html) {
        frame.srcdoc = `<!DOCTYPE html><html><head><meta charset="UTF-8">
            <style>body{margin:0;padding:8px;font-size:13px}</style>
            </head><body>${phpinfo_html}</body></html>`;
    }
}
