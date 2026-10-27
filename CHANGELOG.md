# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [1.0.1] - 2026-10-27

### Fixed
- `__set()` without a handler nor a parent magic method only warns: it no longer writes an inaccessible property from outside, nor creates a dynamic one.
- `__isset()` without a handler nor a parent magic method returns `false` silently, as native `isset()` does.
- `__unset()` without a handler nor a parent magic method only warns: it no longer removes an inaccessible property from outside.

## [1.0.0] - 2026-10-25

Initial release.
