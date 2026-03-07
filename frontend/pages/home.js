import { currentPageUrl } from '../app.js';

export function render(container) {
    container.innerHTML = `
        <div class="mt-5 mb-4 text-center">
            <h1 class="display-5 fw-bold mb-2">Stealth</h1>
            <p class="lead text-secondary mb-4">Dynamic analysis &amp; exploit-creation tools for WordPress bug bounty research.</p>
            <div class="d-inline-flex gap-2 flex-wrap justify-content-center">
                <a href="https://github.com/stealthcopter/wordpress-hacking" target="_blank"
                   class="btn btn-outline-light">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                         viewBox="0 0 16 16" class="me-1" style="vertical-align:-2px">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38
                                 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13
                                 -.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66
                                 .07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15
                                 -.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09
                                 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82
                                 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01
                                 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                    </svg>
                    GitHub
                </a>
                <a href="https://github.com/stealthcopter/wordpress-hacking/issues" target="_blank"
                   class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                         viewBox="0 0 16 16" class="me-1" style="vertical-align:-2px">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                    </svg>
                    Issues
                </a>
                <a href="https://www.buymeacoffee.com/stealthcopter" target="_blank"
                   class="btn" style="background:#FFDD00;color:#000;font-weight:600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="currentColor" class="me-1" style="vertical-align:-2px">
                        <path d="M20.216 6.415l-.132-.666c-.119-.598-.388-1.163-1.001-1.379-.197-.069-.42-.098-.57-.241-.152-.143-.196-.366-.231-.572-.065-.378-.125-.756-.192-1.133-.057-.325-.102-.69-.25-.987-.195-.4-.597-.634-.996-.634H7.017c-.4 0-.801.234-.996.634-.148.297-.193.662-.25.987-.067.377-.127.755-.192 1.133-.035.206-.079.429-.231.572-.15.143-.372.172-.57.241-.613.216-.882.781-1.001 1.379l-.132.666-.521 2.608.017.017c-.025.134-.042.271-.042.411 0 1.437 1.171 2.608 2.608 2.608.957 0 1.787-.534 2.218-1.319.431.785 1.261 1.319 2.218 1.319.957 0 1.787-.534 2.218-1.319.431.785 1.261 1.319 2.218 1.319.957 0 1.787-.534 2.218-1.319.431.785 1.261 1.319 2.218 1.319 1.437 0 2.608-1.171 2.608-2.608 0-.14-.017-.277-.042-.411l.017-.017-.521-2.608zM7 15.667c0 .184.149.333.333.333h9.334c.184 0 .333-.149.333-.333v-1.334H7v1.334zm-.333 2c0 1.103.897 2 2 2h6.667c1.103 0 2-.897 2-2v-.667H6.667V17.667z"/>
                    </svg>
                    Buy me a coffee
                </a>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- What it does -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header fw-semibold">What is this?</div>
                    <div class="card-body">
                        <p>A collection of tools for WordPress plugin analysis built for bug bounty hunters. Install it alongside a target
                           plugin or theme to get deep visibility into how WordPress hooks, REST routes, and shortcodes
                           are wired up, without reading thousands of lines of raw PHP.</p>
                        <ul class="mb-0 text-secondary">
                            <li>Browse registered <strong>actions &amp; AJAX hooks</strong> with source code</li>
                            <li>Enumerate <strong>REST API routes</strong> and raw HTTP request templates</li>
                            <li>Inspect and <strong>execute shortcodes</strong> live</li>
                            <li>Switch user context, generate nonces, test file uploads</li>
                            <li>Install LFI gadgets and PHP object injection payloads</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Bug bounty platforms -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header fw-semibold">Report Your Findings</div>
                    <div class="card-body">
                        <p>Found a vulnerability? Report it through one of these platforms:</p>
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <a href="https://patchstack.com/database/" target="_blank"
                                   class="fw-semibold text-decoration-none">Patchstack</a>
                                <p class="text-secondary mb-0 small">
                                    WordPress-focused vulnerability disclosure. Use the
                                    <a href="https://patchstack.com/alliance/" target="_blank">Patchstack Alliance</a>
                                    researcher program to submit findings and earn rewards.
                                </p>
                            </div>
                            <div>
                                <a href="https://www.wordfence.com/res/BTMF4ED7ERDM" target="_blank"
                                   class="fw-semibold text-decoration-none">Wordfence</a>
                                <p class="text-secondary mb-0 small">
                                    Submit to the
                                    <a href="https://www.wordfence.com/res/BTMF4ED7ERDM" target="_blank">Wordfence Bug Bounty Program</a>.
                                    <br><span class="text-warning">Note: referral link, I get a small kickback on the first 5 bugs you report (thanks!)</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick reference -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header fw-semibold">Quick Reference</div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr><th class="ps-3">Page</th><th>What to look for</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="ps-3"><a href="${currentPageUrl('info')}" class="fw-semibold">info</a></td><td>Information about the current setup</td></tr>
                                <tr><td class="ps-3"><a href="${currentPageUrl('gadgets')}" class="fw-semibold">gadgets</a></td><td>Helpful gadgets for LFI and PHP object injection for testing exploits</td></tr>
                                <tr><td class="ps-3"><a href="${currentPageUrl('instally')}" class="fw-semibold">instally</a></td><td>Quickly install other plugins/themes</td></tr>
                                <tr><td class="ps-3"><a href="${currentPageUrl('shorty')}" class="fw-semibold">shorty</a></td><td>Shortcodes that accept and echo user input without sanitisation (XSS / SQLi, LFI)</td></tr>
                                <tr><td class="ps-3"><a href="${currentPageUrl('funcy')}" class="fw-semibold">funcy</a></td><td>Unauthenticated AJAX/admin-post hooks, classic entry points for unauth XSS/RCE/SQLi/Missing Auth</td></tr>
                                <tr><td class="ps-3"><a href="${currentPageUrl('resty')}" class="fw-semibold">resty</a></td><td>REST routes with <span class="badge bg-danger">No Auth</span>, check permission callbacks carefully</td></tr>
                                <tr><td class="ps-3"><a href="${currentPageUrl('upload')}" class="fw-semibold">upload</a></td><td>Demo upload form</td></tr>
                                <tr><td class="ps-3"><a href="${currentPageUrl('noncy')}" class="fw-semibold">noncy</a></td><td>Generate a nonce for a given action</td></tr>                                   
                                <tr><td class="ps-3"><a href="${currentPageUrl('options')}" class="fw-semibold">options</a></td><td>Dangerous option keys: <code>default_role</code>, <code>users_can_register</code></td></tr>
                                <tr><td class="ps-3"><a href="${currentPageUrl('login')}" class="fw-semibold">login</a></td><td>Switch user context to test auth-gated functionality</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        `;
}
