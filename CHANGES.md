# 1.4.3 (11 October 2021)

* [*] Security improvements.

# 1.4.2 (8 October 2021)

* [+] Users can now automatically configure DNS records for domains without hosting. (EXTPLESK-2993)
* [*] Updated supported DNS templates.
* [-] The extension can now determine without authentication if it supports a particular DNS template.
* [-] The extension can no longer crash while configuring DNS records. (EXTPLESK-2602)

# 1.4.1 (11 December 2020)

* [*] Security improvements. 

# 1.4.0 (9 August 2019)

* [+] Users can now automatically configure mail DNS-records.
* [+] Users can now automatically configure DNS for subdomains. 
* [+] Users can now use Domain Connect to verify their domains ownership for Bing Webmaster Tools. 
* [+] The extension now supports SPFM records. This enables to update the existing SPF record with the rules from the SPFM record.

# 1.3.1 (11 April 2019)

* [-] Domain Connect no longer suggests configuring DNS settings for a domain when the domain's DNS hosting is configured in Plesk. (EXTPLESK-590)
* [-] If a domain cannot be resolved, this event is now logged as a warning in `/var/log/plesk/panel.log` (Plesk for Linux) and `%plesk_dir%\admin\logs\php_error.log` (Plesk for Windows). (EXTPLESK-619)

# 1.3.0 (6 March 2019)

* [+] The extension now adds the "nameServers" key to JSON returned to a domain's third-party service. This helps the service provider identify that Plesk is the authoritative DNS provider for the domain.

# 1.2.0 (14 January 2019)

* [*] The /settings call returns a response that indicates the domain does not belong to the DNS provider.

# 1.1.3 (27 December 2018)

* [*] Updated templates

# 1.1.2 (21 November 2018)

* [*] Improved performance with a large number of domains
* [*] Translated the extension and its description into several new languages
* [*] Updated Domain Connect logo
* [*] Improved the loading speed of the extension's screens. (EXTPLESK-466)

# 1.1.1 (31 July 2018)

* [-] Redirect to javascript:window.open(...) when connecting domain in Firefox

# 1.1.0 (10 July 2018)

* [+] The extension can work in the DNS Provider mode
* [+] The extension can work in the Service Provider mode
* [*] Updated DESCRIPTION.md and English localization
* [*] Updated screenshots

# 0.0.1 (10 March 2018)

* [+] The first commit.
