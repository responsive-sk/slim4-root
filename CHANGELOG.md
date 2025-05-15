# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.4.0] - 2024-05-30

### Added

- **Improved testing utilities** with better documentation
- **Enhanced TestContainer** with more robust error handling
- **Monolog integration** with `MonologFactory` and `MonologProvider` classes
- **Comprehensive documentation** for Monolog integration

## [1.3.0] - 2024-05-30

### Added

- **Testing module** with `TestContainer` class for easier test setup
- **Bootstrap file** for PHPUnit integration
- Comprehensive documentation for testing utilities
- Reorganized documentation into `docs` directory

### Changed

- Updated README with information about testing utilities
- Improved documentation structure

## [1.2.0] - 2023-11-15

### Added

- **Auto-discovery** of common directory structures via `PathsDiscoverer` class
- **Path validation** with dedicated `PathsValidator` class
- **Path normalization** with `PathsNormalizer` class
- **Dedicated exception handling** with `InvalidPathException`
- New method `getPaths()` to get all paths as an associative array
- Comprehensive test coverage for all new features
- Detailed documentation with examples

### Changed

- Refactored `Paths` class to use the new components
- Updated constructor to support auto-discovery and validation
- Improved path handling for cross-platform compatibility
- Enhanced documentation with feature comparison table

## [1.1.0] - 2023-10-01

### Added

- Initial release
- Basic path management for Slim 4 applications
- Support for custom directory structures
- Middleware for accessing paths in route handlers
- PSR-11 container integration
