# Changelog

## [2.0.0] - 2026-02-12
### Breaking Changes
- Renamed `GeoloateIPInterface` to `GeolocateIPInterface` and `GeoloateIP` to `GeolocateIP`
- Converted `AttributeProvider` from interface to final class
- CheckPopup JSON response now uses associative keys instead of indexed array

### Added
- ACL configuration file (`etc/acl.xml`)
- ARIA attributes and keyboard navigation (Escape key) for popup
- Mobile responsive styles for popup
- Configurable `checkUrl` passed to Luma popup JS instead of hardcoded path

### Changed
- `PopupLanguageMode::toOptionArray()` returns correct Magento option format
- `IpDatabaseImportCron` uses `LocalizedException` instead of base `Exception`
- Cached country-to-store mapping in `ControllerHelper` for better performance
- Indexed country name lookup in `translateCountryName()`
- Store IDs explicitly cast to string in `CheckPopup`
- Loosened Magento module version constraints in `composer.json`

## [1.5.0] - 2025-12-16
### Added
- Compatibility with Hyvä theme

## [1.4.0] - 2025-10-28
### Changed
- Updated MaxMind GeoIP2 Composer package to version 3, compatible with PHP 8

## [1.3.2] - 2025-10-01
### Fixed
- Fixed popup message selector

## [1.3.1] - 2025-09-30
### Fixed
- Fixed popup messages appearing twice
- Added exception handling

## [1.3.0] - 2025-09-22
### Added
- PHP 8.1 support

## [1.2.1] - 2025-07-22
### Fixed
- Fixed strcasecmp funtion usage for PHP 8

## [1.2.0] - 2025-07-22
### Added / Changed
- Added options to translate the popup that proposes the redirect based on geolocation.
- Changed the scope for currency mapping, allowing them to be mapped with a global configuration.

## [1.1.2] - 2025-06-23
### Fixed
- Removed double slash (`//`) in URLs after redirect.
- Fixed country-to-currency mapping to ensure correct currency selection based on user location.

## [1.1.1] - 2025-06-18
### Fixed
- Removed double slash (`//`) in URLs after redirect

## [1.1.0] - 2025-06-05
### Changed
- Replaced dependency on PHP Phar extension with `splitbrain/php-archive`
- Updated code to handle ZIP and TAR archives using the new library (pure PHP implementation)

## [1.0.0] - 2025-05-30
### Added
- Complete README documentation
- Changelog file
- MIT License file
- Module tested and validated for production use

## [0.0.1] - 2025-05-15
### Added
- First development version
- Base module code (unlicensed, untested)
