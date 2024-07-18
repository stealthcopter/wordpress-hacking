# WordPress Hacking

Some useful resources for hacking WordPress and it's plugins and themes.

## Reports

The reports directory contains a selection of my publicly disclosed vulnerability reports that were disclosed to bug bounty programs. I've tried to get a good cross-section of different vulnerability types. The only modifications I have made is to add the CVE ID and a link to the report at the top of the markdown files. Where I have attached a proof-of-concept Python script to the report you will find it in the same folder.  

| CVE            | Title                                                                      |
|----------------|----------------------------------------------------------------------------|
| CVE-2024-30509 | [sellkit-arbitrary-file-download](reports/sellkit-arbitrary-file-download) |
| CVE-2024-3242  | [brizy-contributor-rce-2](reports/brizy-contributor-rce-2)                 |
| CVE-2024-4361  | [siteorigin-panels-xxs](reports/siteorigin-panels-xxs)                     |


## Python Functions

Feel free to reuse my Python code to help write you own PoCs, in future I may turn some of the functions into a easy-to-use library.