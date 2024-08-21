# WordPress Hacking

Some useful resources for hacking WordPress and it's plugins and themes.

## Reports

The reports directory contains a selection of my publicly disclosed vulnerability reports that were disclosed to bug bounty programs. I've tried to get a good cross-section of different vulnerability types. 

| CVE            | Vulnerability                               | 📄 Report 🐍 Python PoC 🔗 Blog                                                                                           |
|----------------|---------------------------------------------|---------------------------------------------------------------------------------------------------------------------------|
| CVE-2024-30509 | SellKit Subscriber+ Arbitrary File Download | [📄](reports/sellkit-arbitrary-file-download) [🐍](reports/sellkit-arbitrary-file-download/sellkit.py)                    |        
| CVE-2024-3242  | Brizy Contributor+ Arbitrary File Upload    | [📄](reports/brizy-contributor-rce-2) [🐍](reports/brizy-contributor-rce-2/brizy-rce2.py)                                 |        
| CVE-2024-4361  | SiteOrigin Contributor+ XSS                 | [📄](reports/siteorigin-panels-xxs)                                                                                       |        
| CVE-2024-22144 | GoTMLS Unauthenticated RCE                  | [📄](reports/gotmls-rce) [🐍](reports/gotmls-rce/gotmls.py) [🔗](https://sec.stealthcopter.com/cve-2024-22144/)           |        
| CVE-2024-6386  | Contributor+ SSTI to RCE                    | [📄](reports/sitepress-multilingual-cms-rce/wpml-ssti-rce.md) [🔗](https://sec.stealthcopter.com/wpml-rce-via-twig-ssti/) |

These 📄 reports are in their original formats when originally submitted. Any mistakes are mine, for prettier versions please see the ones that have linked blog posts 🔗. Where I created a Python proof-of-concept script you will find it in the folder or directly by clicking on 🐍.

## Python Functions

Feel free to reuse my Python code to help write you own PoCs, in future I may turn some of the functions into an easy-to-use library.
