# Changelog

## v4.3.0

- Added support for Symfony 6,
- Removed support for Symfony <4.4 & >5.0 - <5.3

## v4.2.1

- Support for PHP8

## v4.2.0

- Fix: PSR-4 autoloading (#132)
- Add `@coversNothing` annotation to prevent raising risky warnings in PHPUnit 9
- Fix:  run `prepend()` for all extensions first, then `load()` then extensions (#126)

## v4.1.0

- Support for Symfony 5. 

## v4.0.0

- Added support for PHPUnit 8.0
- Dropped support for PHPUnit 7.x
- Dropped support for PHP 7.1
- Made classes and methods final
- Added more type hints. 

## v3.1.0

- Added support for `yaml` file extension. 

## v3.0.0

- Dropped support for PHPUnit < 7.0
- [BC Break] Add return type hints to all `toString()` methods of the constraint classes for compatibility with PHPUnit 7
- Dropped support for PHP < 7.1

## v2.3.0

- Testing on Symfony 4 and make sure test passes
- Support for named arguments
- Service/alias names might be FQCNs

## v2.2.0

- Support for Symfony 4

## v2.1.0

- Added support for validating invocation index order of method call (see #69).

## v2.0.0

- Only support PHPUnit 6
- Only support Symfony 2.* and 3.* LTS versions
- Require PHP ^7.0
- Drop support for HHVM

## v0.7.2

- Made alias/service id comparison case insensitive. Reported and fixed by Christian Flothmann (@xabbuh).

## v0.7.1

- Fixed a bug in `DefinitionHasTagConstraint`: it didn't throw an exception when a tag was missing, which resulted in
  false positives. Reported and fixed by Christian Flothmann (@xabbuh).

## v0.7.0

- Add `assertContainerBuilderNotHasService()`, contributed by Uwe Jäger (@uwej711).

## v0.6.0

- Fix AbstractCompilerPassTestCase when using strict-mode, contributed by Sebastiaan Stok (@sstok).

## v0.5.0

- Automatically resolve a definition's class before comparing it to the expected class (in
  ``ContainerBuilderHasServiceDefinitionConstraint``).

## v0.4.0

- Added ``ContainerBuilderHasSyntheticServiceConstraint`` and corresponding assertion to
  ``AbstractContainerBuilderTestCase`` (as suggested by @WouterJ).

## v0.3.0

- Renamed ``AbstractCompilerPassTest`` to ``AbstractCompilerPassTestCase`` (contributed by @mbadolato).

## v0.2.0

- Added ``AbstractExtensionConfigurationTestCase`` for testing different types of configuration files.
- Made the library compatible with Symfony versions 2.0 and up.

## v0.1.1

- Renamed ``ContainerBuilderTestCase`` to ``AbstractContainerBuilderTestCase`` and made it abstract.
- Added ``DefinitionIsChildOfConstraint`` to verify that a given service has the expected parent service.
  Also added a shortcut for using this constraint in the test cases: ``assertContainerBuilderHasServiceDefinitionWithParent($serviceId, $parentServiceId)``
