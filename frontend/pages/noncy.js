import { api } from '../api.js';
import { escHtml, toast, copyToClipboard, showError } from '../app.js';

const ROLE_COLOR = {
    administrator: 'danger', editor: 'warning', author: 'secondary',
    contributor: 'primary',  customer: 'info',  subscriber: 'success',
};
function roleColor(roles) { return ROLE_COLOR[roles?.[0]] ?? 'light'; }

export async function render(container) {
    // Fetch current user info
    let userData = { current: { id: -1, login: 'Not Logged In', roles: [] } };
    try { userData = await api.getUsers(); } catch { /* ignore */ }

    const { current } = userData;
    const color = roleColor(current.roles);

    container.innerHTML = `
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Nonce Generator</h5>
                        <p class="text-secondary">
                            Generate a nonce for a given action as the current user.
                            You can change users on the <a href="?page=login">Login</a> page.
                        </p>
                        <div class="form-floating mb-3">
                            <input type="text" id="current-user" class="form-control" disabled
                                   value="${escHtml(current.login)}">
                            <label for="current-user" class="text-${color}">Current User</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" id="action-name" class="form-control" placeholder="Action Name">
                            <label for="action-name">Action Name</label>
                        </div>
                        <button id="btn-generate" class="btn btn-success">Create nonce</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" id="result-col"></div>
        </div>`;

    container.querySelector('#btn-generate').addEventListener('click', async () => {
        const action = container.querySelector('#action-name').value.trim();
        if (!action) { toast('Enter an action name first', 'error'); return; }

        const btn = container.querySelector('#btn-generate');
        btn.disabled = true;
        try {
            const data = await api.getNonce(action);
            const col  = container.querySelector('#result-col');
            col.innerHTML = `
                <div class="card mt-4 mt-lg-0">
                    <div class="card-header bg-success">Nonce Result</div>
                    <div class="card-body">
                        <table class="table table-striped mb-0">
                            <tbody>
                                <tr><td><strong>User</strong></td><td>${escHtml(data.user?.login ?? '')}</td></tr>
                                <tr><td><strong>Action</strong></td><td>${escHtml(data.action)}</td></tr>
                                <tr>
                                    <td><strong>Nonce</strong></td>
                                    <td>
                                        <code id="nonce-val">${escHtml(data.nonce)}</code>
                                        <button class="btn btn-sm btn-outline-secondary ms-2" id="btn-copy-nonce">📋 Copy</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>`;
            col.querySelector('#btn-copy-nonce').addEventListener('click', () => {
                copyToClipboard(data.nonce, 'nonce');
            });
        } catch (e) {
            showError(container.querySelector('#result-col'), e.message);
        } finally {
            btn.disabled = false;
        }
    });
}
