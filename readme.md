# WordPress Hacking

Some useful resources for hacking WordPress and it's plugins and themes.

## Plugin

I have written a plugin designed to help dyanamic analysis and hacking of WordPress plugins and themes, this can be found in the [plugin](https://github.com/stealthcopter/wordpress-hacking/tree/plugin) branch in this repo.

## Reports

The reports directory contains a selection of my publicly disclosed vulnerability reports that were disclosed to bug bounty programs. I've tried to get a good cross-section of different vulnerability types. 

| CVE            | Bounty    | Vulnerability                                     | 📄 Report 🐍 Python PoC 🔗 Blog                                                                                                                                    |
|----------------|-----------|---------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| CVE-2024-30509 | 11.25 AXP | SellKit Subscriber+ Arbitrary File Download       | [📄](reports/sellkit-arbitrary-file-download) [🐍](reports/sellkit-arbitrary-file-download/sellkit.py)                                                             |        
| CVE-2024-3242  | $469      | Brizy Contributor+ Arbitrary File Upload          | [📄](reports/brizy-contributor-rce-2) [🐍](reports/brizy-contributor-rce-2/brizy-rce2.py)                                                                          |        
| CVE-2024-4361  | $325      | SiteOrigin Contributor+ XSS                       | [📄](reports/siteorigin-panels-xxs)                                                                                                                                |        
| CVE-2024-22144 | 270 AXP   | GoTMLS Unauthenticated RCE                        | [📄](reports/gotmls-rce) [🐍](reports/gotmls-rce/gotmls.py) [🔗](https://sec.stealthcopter.com/cve-2024-22144/)                                                    |        
| CVE-2024-6386  | $1639     | WPML Contributor+ SSTI to RCE                     | [📄](reports/sitepress-multilingual-cms-rce/wpml-ssti-rce.md) [🔗](https://sec.stealthcopter.com/wpml-rce-via-twig-ssti/)                                          |
| CVE-2024-4637  | $434      | Slider Revolution Contributor+ XSS                | [📄](reports/revslider-xss) [🐍](reports/revslider-xss/exploit.py)                                                                                                 |
| CVE-2024-5153  | $361      | Starlar Elementor Addons Arbitary Folder Deletion | [📄](reports/startklar-elmentor-forms-extwidgets-arbitrary-folder-deletion) [🐍](reports/startklar-elmentor-forms-extwidgets-arbitrary-folder-deletion/exploit.py) |

These 📄 reports are in their original formats when originally submitted. Any mistakes are mine, for prettier versions please see the ones that have linked blog posts 🔗. Where I created a Python proof-of-concept script you will find it in the folder or directly by clicking on 🐍.

## Python Functions

Feel free to reuse my Python code to help write you own PoCs, in future I may turn some of the functions into an easy-to-use library.
