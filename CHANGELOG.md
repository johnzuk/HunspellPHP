## Changelog
### Version 2.0.0
#### Added
- Added PHP8.0 typed class, 
- Added constructor to main `HunspellPHP` class where the `$dictionary`, `$encoding` and `$dictionary_path` cal be set/overridden during initialization.
- Added `$dictionary_path` as a new argument were the dictionary files path may be specified (system default search locations are used otherwise). Additional `get()` and `set()`methods added. 
- Added functionality to `findCommand` method via new `(bool)$stem_mode` argument.
#### Removed
- Removed `findStemCommand` method.
- Removed unused exception classes.
- Removed `HunspellPHP\Exceptions` namespace.
- Removed composer.lock from repo.
#### Fixed
- Renamed `$language` more appropriately `$dictionary` since that is what that property is referencing.
- Moved HunspellMatchTypeException up one directory to \HunspellPHP namespace.
- Fixed an issue where not all `$match` values were returned from the command response resulting in PHP warnings.
- Fixed a missing type `-` extraction from the matcher regex which resulted in PHP warnings and bad responses.