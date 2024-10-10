This set of little helpers for WordPress hacking can either be installed as a plugin or just dumped into the webroot.

## Installation Methods

If you have the WordPress cli installed you can automatically install the plugin with this command:

### Manually

- Download the [stealth.zip](https://github.com/stealthcopter/wordpress-hacking/releases/latest/download/stealth.zip)
- Install the plugin from the `/wp-admin/plugin-install.php` page

### WordPress CLI

If you have the WordPress CLI installed you can get it running with this one-liner:

```
wp plugin install --activate https://github.com/stealthcopter/wordpress-hacking/releases/latest/download/stealth.zip
```

## Web Installation Methods

If you don't want to install it as a plugin you can just dump this zip into the webroot and it will try and find `wp-load.php` automatically so it can hook in.

```
cd /var/www/html
wget https://github.com/stealthcopter/wordpress-hacking/releases/latest/download/stealth.zip
unzip stealth.zip
```

# Usage

Navigate to `/stealth` and start using the tools

# Contributing

If you have an idea for a new feature please create a new issue on GitHub. If you would like to contribute a bug fix, or feature please feel free to fork the repo and submit a PR against this one.

# TODO:
- View user_meta and other similar key/value tables
- Create PoC / Write up from plugin name
- Update feature
