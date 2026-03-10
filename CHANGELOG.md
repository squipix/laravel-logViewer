# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-03-10

### Fixed

- Replace `new static()` with `new self()` in `LogEntryCollection`, `FilesystemException`, `LogNotFoundException`, and `StatsTable` to resolve PHPStan unsafe static instantiation errors.

### Added

- `PublishCommand` now supports publishing multiple asset tags in a single call, streamlining the publishing process.
- Added CSRF token to delete requests across all view themes (Bootstrap 3, 4, 5) to prevent cross-site request forgery.
- Implemented Subresource Integrity (SRI) for Font Awesome CDN links in all view themes.
- Log date route parameter is now validated with a regex constraint to prevent path traversal attacks.
- Added `security/security_audit-1-09032026.md` and `security/security_fix-1-09032026.md` documenting the security audit and remediation.
- `LogViewerController` now validates the log date before serving log entries.
- Log listing views now display only filenames rather than full paths.

### Changed

- `Filesystem::getFiles()` updated to return only log filenames instead of full paths.
- `LogViewerRoute` route definition updated to apply the date pattern constraint.
- `LogViewerServiceProvider` streamlined tag registration.

## [1.0.0] - 2026-03-09

### Added

- Initial release of `squipix/laravel-log-viewer`.
- Log viewer dashboard with Bootstrap 3, 4, and 5 themes.
- Commands: `log-viewer:check`, `log-viewer:clear`, `log-viewer:publish`, `log-viewer:stats`.
- Log entry parsing, pagination, and level filtering.
- Log statistics table.
- Route-based log viewer with configurable prefix and middleware.
- Deferred service provider for lazy-loading utilities.
- Translations for 29 languages.
- GitHub Actions CI workflow with `actions/cache` v4.

[Unreleased]: https://github.com/squipix/LogViewer/compare/1.1.0...HEAD
[1.1.0]: https://github.com/squipix/LogViewer/compare/1.0...1.1.0
[1.0.0]: https://github.com/squipix/LogViewer/releases/tag/1.0
