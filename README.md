# MageOS MaxMind GeoIP Redirect

Magento module for GeoIP-based redirect using the free [GeoLite2](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data) service by MaxMind.

This module provides a fully open source alternative to commercial GeoIP redirect modules, with built-in support for both IPv4 and IPv6 and full compatibility with Varnish.

---

## Installation

```bash
composer require mage-os/module-maxmind-geoip-redirect
```
```bash
bin/magento module:enable MageOS_MaxMindGeoipRedirect
```
```bash
bin/magento setup:upgrade
```

---

## Features

- Detects user country via IP address (GeoLite2)
- Supports both API and local database modes
- Offers a configurable popup to suggest redirection
- Compatible with Varnish
- Fully configurable per store view
- IPv4 and IPv6 compatible

---

## Configuration

Navigate to: `Stores > Configuration > Mage-OS > MaxMind GeoIP Redirect`

### 1. General (global scope)

- **Enable**: Enable or disable the module.
- **Force an IP for testing**: Override user IP for testing purposes.
- **IP check method**:
    - **API webservices**: Real-time geolocation, limited to 1000 requests/day.
    - **Downloaded local database**: Offline geolocation, limited to 30 downloads/day.
- **Cron expression for IP database import** (only for local database mode): Define how often to update the local database.
- **Last IP database import** (read-only): Shows the date of the last database download.
- **Confirmation Redirect Popup Text**: Customize the popup message. Use `{{geolocated_country}}` to dynamically include the country name.

### 2. GeoLite2 Settings (global scope)

- **Account ID**: From your MaxMind account.
- **License key**: From your MaxMind account.
- **Database URL**: URL to fetch the GeoLite2 database.

> Notes and links to retrieve these credentials are included in the admin comments.

### 3. Country Redirect Settings (per store view)

Change scope to the desired store view to configure these settings:

- **Affected Countries**: List of countries associated with this store view.
- **Country to Currency Mapping**: Match each country with its preferred currency.

### 4. Restriction (global and per store view)

- **Excepted IPs**: IPs excluded from geolocation and redirect logic.
- **Excepted URLs**: URLs excluded from geolocation and redirect logic.
- **Excepted User Agents**: Typically used for bots, crawlers, or specific tests.
- **First visit redirect only**:
    - `Yes`: Show popup only on first visit.
    - `No`: Show popup on every visit if country mismatch is detected.
    - **Recommendation**: Use `No` for single-domain setups, `Yes` for multi-domain setups.

---

## Notes

### API vs Downloaded Database: Which One to Choose?

The module supports two methods for resolving IP geolocation using MaxMind's GeoLite2 service:

#### 1. API Webservices (Real-time)

- **Advantages**:
    - Always up-to-date data — the geolocation results are as accurate as MaxMind’s latest updates.
    - No need for local storage or cron configuration.
- **Disadvantages**:
    - Free plan limited to 1000 API calls per day — not suitable for medium to high traffic sites.
    - Requires internet connectivity for every geolocation call.
    - If MaxMind’s API is down, geolocation fails.

#### 2. Downloaded Local Database (Offline Mode)

- **Advantages**:
    - Works completely offline — ideal for high traffic sites and when uptime is critical.
    - Extremely fast IP resolution thanks to optimized local lookup.
    - Not dependent on MaxMind uptime during normal operations.
    - **Allows you to avoid API rate limits — the free plan allows up to 30 downloads/day.**
- **Disadvantages**:
    - Requires setup of a cron job to download and refresh the local database.
    - Accuracy depends on how often the database is updated (typically daily is enough).

#### When to Use What

- **Use API Mode**:
    - If your site has low daily traffic (less than 1000 unique visits needing geolocation).
    - You want the most accurate and up-to-date geolocation without local maintenance.

- **Use Local Database Mode**:
    - If your site exceeds 1000 visits/day or must guarantee geolocation even in offline scenarios.
    - You prefer performance and stability over real-time precision.
    - You are already managing cron jobs and can schedule the download within the 30/day limit.

In general, local database mode is recommended for production environments, especially when performance and robustness are priorities.

---

## License

[MIT](LICENSE)

---

## Credits

Powered by [GeoLite2](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data) data created by MaxMind.

---

## Support

This module is provided as-is under an open source license. For contributions, bug reports, or feature requests, feel free to open a GitHub issue or submit a pull request.
