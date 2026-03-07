import { api } from '../api.js';
import { escHtml, showLoading, showError, toast, copyToClipboard } from '../app.js';

const ROLE_COLOR = {
    administrator: 'danger', editor: 'warning',     author: 'secondary',
    contributor:   'primary', customer: 'info',     subscriber: 'success',
};
function roleColor(roles) { return ROLE_COLOR[roles?.[0]] ?? 'light'; }
function roleLabel(roles) { return roles?.length ? roles.join(', ') : 'unauthenticated'; }

export async function render(container) {
    showLoading(container);
    let data;
    try {
        data = await api.getUsers();
    } catch (e) {
        showError(container, e.message);
        return;
    }

    const { current, users, cookies } = data;
    const curColor = roleColor(current.roles);

    // Check for ?login redirect from the old navbar link
    const uid = new URLSearchParams(window.location.search).get('login_as_uid');
    if (uid !== null) {
        await doLogin(parseInt(uid, 10), container);
        // Clean URL
        const url = new URL(window.location.href);
        url.searchParams.delete('login_as_uid');
        history.replaceState({}, '', url.toString());
        return render(container);
    }

    const cookieSection = current.logged_in && cookies ? `
        <div class="mb-3">
            <label class="form-label fw-semibold">Auth Cookies
                <button class="btn btn-sm btn-outline-secondary ms-2" id="btn-copy-cookies">📋 Copy</button>
            </label>
            <pre class="p-2 rounded" style="background:#1a1a1a;font-size:0.78rem;word-break:break-all;">${escHtml(cookies)}</pre>
        </div>` : '';

    const userRows = users.map(u => {
        const color = roleColor(u.roles);
        return `<tr>
            <td>${escHtml(String(u.id))}</td>
            <td>${escHtml(u.login)}</td>
            <td>${escHtml(roleLabel(u.roles))}</td>
            <td><button class="btn btn-sm btn-${color} login-btn" data-uid="${u.id}">
                Login as ${escHtml(u.login)}
            </button></td>
        </tr>`;
    }).join('');

    container.innerHTML = `
        <div class="row mt-4">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Current Session</h5>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" disabled
                                   value="${escHtml(current.login)}">
                            <label class="text-${curColor}">Logged in as</label>
                        </div>
                        ${cookieSection}
                        <button class="btn btn-outline-secondary btn-sm" id="btn-logout">Logout</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 mt-4 mt-lg-0">
                <div class="card">
                    <div class="card-header">Users</div>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead><tr><th>ID</th><th>Login</th><th>Role</th><th></th></tr></thead>
                            <tbody>${userRows}</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>`;

    container.querySelector('#btn-logout')?.addEventListener('click', () => doLogin(-1, container));
    container.querySelector('#btn-copy-cookies')?.addEventListener('click', () => {
        copyToClipboard(cookies, 'cookies');
    });

    container.querySelectorAll('.login-btn').forEach(btn => {
        btn.addEventListener('click', () => doLogin(parseInt(btn.dataset.uid, 10), container));
    });
}

async function doLogin(uid, container) {
    try {
        const res = await api.login(uid);
        toast(res.message, res.success ? 'success' : 'error');
        if (res.success) render(container);
    } catch (e) {
        toast(e.message, 'error');
    }
}
