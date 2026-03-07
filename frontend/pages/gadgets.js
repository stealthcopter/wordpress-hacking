import { api } from '../api.js';
import { escHtml, showLoading, showError, toast, renderCode, copyToClipboard, initCopyables } from '../app.js';

export async function render(container) {
    showLoading(container);
    let gadgetInfo;
    try {
        gadgetInfo = await api.getGadgetsInfo();
    } catch (e) {
        showError(container, e.message);
        return;
    }

    const defaultPath = '/tmp/lfi.php';

    container.innerHTML = `
        <div class="mt-4">
            <!-- LFI Section -->
            <h4>Local File Inclusion (LFI)</h4>
            <p class="text-secondary">
                Install an LFI gadget to a specific path on the server, then use the generated
                payloads below to try to execute that PHP file via an LFI vulnerability.
            </p>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text">Path</span>
                        <input id="lfi-path" type="text" class="form-control" value="${escHtml(defaultPath)}">
                        <span class="input-group-text">Traversals</span>
                        <input id="lfi-traversals" type="number" class="form-control" value="20" min="0" style="max-width:90px">
                        <button id="btn-lfi-install" class="btn btn-success">Install</button>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Generated Payloads</div>
                <div class="card-body p-2" id="lfi-payloads" style="background:#1a1a1a;border-radius:0 0 4px 4px">
                </div>
            </div>

            <hr class="border-danger border-2 my-4">

            <!-- PHP Object Injection Section -->
            <h4>PHP Object Injection</h4>
            <p class="text-secondary">
                A gadget class (<code>ObjInjec</code>) is loaded with this plugin. Use it to test
                PHP object injection vulnerabilities in the target plugin.
            </p>
            <p id="obj-status"></p>
            <div id="obj-code"></div>
        </div>`;

    // Payload generation (client-side, same logic as original)
    function generatePayloads() {
        const path  = container.querySelector('#lfi-path').value;
        const depth = parseInt(container.querySelector('#lfi-traversals').value, 10) || 0;
        const traversalPath = '../'.repeat(depth) + path.replace(/^\/+/, '');
        const payloads = [
            { label: 'Basic',                   value: traversalPath },
            { label: 'Bypass ../ removal',      value: traversalPath.replaceAll('../', '..././') },
            { label: 'URL Encoded',             value: encodeURIComponent(traversalPath) },
            { label: 'Double URL Encoded',      value: encodeURIComponent(encodeURIComponent(traversalPath)) },
        ];
        const div = container.querySelector('#lfi-payloads');
        div.innerHTML = payloads.map(p => `
            <div class="d-flex align-items-center gap-2 py-1 px-1 border-bottom border-secondary" style="border-color:#333!important">
                <code class="flex-grow-1 text-light" style="word-break:break-all">${escHtml(p.value)}</code>
                <button class="btn btn-sm btn-outline-secondary copy-payload-btn flex-shrink-0"
                        title="${escHtml(p.label)}" data-val="${escHtml(p.value)}">📋</button>
            </div>`).join('');

        div.querySelectorAll('.copy-payload-btn').forEach(btn => {
            btn.addEventListener('click', () => copyToClipboard(btn.dataset.val, btn.title));
        });
    }

    container.querySelector('#lfi-path').addEventListener('input', generatePayloads);
    container.querySelector('#lfi-traversals').addEventListener('input', generatePayloads);
    generatePayloads();

    // LFI install
    container.querySelector('#btn-lfi-install').addEventListener('click', async () => {
        const path = container.querySelector('#lfi-path').value.trim();
        if (!path) { toast('Enter a path first', 'error'); return; }

        try {
            const data = await api.installLfi(path);
            toast(data.message, data.success ? 'success' : 'error');
        } catch (e) {
            toast(e.message, 'error');
        }
    });

    // PHP Object Injection status
    const statusEl = container.querySelector('#obj-status');
    if (gadgetInfo.obj_class_exists) {
        statusEl.innerHTML = '<span class="text-success">✅ ObjInjec class is defined.</span>';
    } else {
        statusEl.innerHTML = '<span class="text-danger">❌ ObjInjec class is not defined.</span>';
    }

    if (gadgetInfo.obj_class_code) {
        const codeEl = container.querySelector('#obj-code');
        codeEl.appendChild(renderCode(gadgetInfo.obj_class_code, 'php', { File: 'payloads/php_obj.php' }));
        initCopyables(codeEl);
    }
}
