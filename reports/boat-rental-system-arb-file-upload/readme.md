# Post Report Info

- Link: https://patchstack.com/database/vulnerability/boat-rental-system/wordpress-boat-rental-plugin-for-wordpress-plugin-1-0-1-arbitrary-file-upload-vulnerability
- CVE: 2024-52376
- Bounty: 60 AXP

# Unauth RCE

https://wordpress.org/plugins/boat-rental-system/

The Boat Rental Plugin for WordPress Plugin is vulnerable to an unauthenticated arbitrary file upload allowing for an attacker to upload a PHP file and execute arbitrary commands. 

1. Install the plugin
2. Modify the Python PoC for your target and execute
```python
TARGET = 'http://wordpress.local:1337'  # No trailing slash
```
3. Profit
