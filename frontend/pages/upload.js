import { api } from '../api.js';
import { escHtml, showError, toast } from '../app.js';

export function render(container) {
    container.innerHTML = `
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upload a File</h5>
                        <p class="text-secondary">
                            Submit a file upload request and inspect what the server receives —
                            useful for testing upload endpoints without building a request manually.
                        </p>
                        <form id="upload-form">
                            <div class="mb-3">
                                <label for="file-input" class="form-label">Choose file(s)</label>
                                <input type="file" id="file-input" name="file" class="form-control" multiple required>
                            </div>
                            <button type="submit" class="btn btn-success">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0" id="result-col"></div>
        </div>`;

    container.querySelector('#upload-form').addEventListener('submit', async e => {
        e.preventDefault();
        const form = e.target;
        const btn  = form.querySelector('button[type=submit]');
        btn.disabled = true;

        try {
            const fd   = new FormData(form);
            const data = await api.uploadEcho(fd);
            renderResult(container.querySelector('#result-col'), data);
        } catch (err) {
            showError(container.querySelector('#result-col'), err.message);
        } finally {
            btn.disabled = false;
        }
    });
}

function renderResult(col, data) {
    if (!data.files?.length) {
        col.innerHTML = '<div class="alert alert-warning">No files received.</div>';
        return;
    }

    const rows = data.files.map(f => `
        <tr>
            <td><code>${escHtml(f.field)}</code></td>
            <td>${escHtml(f.name)}</td>
            <td><code>${escHtml(f.type || '—')}</code></td>
            <td>${escHtml(f.tmp_name)}</td>
            <td class="${f.error ? 'text-danger' : ''}">${escHtml(String(f.error))}</td>
            <td>${escHtml(String(f.size))}</td>
        </tr>`).join('');

    col.innerHTML = `
        <div class="card">
            <div class="card-header bg-success">Server received ${data.count} file(s)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Field</th><th>Name</th><th>Type</th>
                                <th>tmp_name</th><th>Error</th><th>Size</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>
        </div>`;

    if (Object.keys(data.post ?? {}).length) {
        col.innerHTML += `
            <div class="card mt-3">
                <div class="card-header">$_POST fields</div>
                <div class="card-body">
                    <pre class="mb-0"><code>${escHtml(JSON.stringify(data.post, null, 2))}</code></pre>
                </div>
            </div>`;
    }
}
