# Changelog

## v0.3.0

- Renamed ``AbstractCompilerPassTest`` to ``AbstractCompilerPassTestCase`` (contributed by @mbadolato).

## v0.2.0

- Added ``AbstractExtensionConfigurationTestCase`` for testing different types of configuration files.
- Made the library compatible with Symfony versions 2.0 and up.

## v0.1.1

- Renamed ``ContainerBuilderTestCase`` to ``AbstractContainerBuilderTestCase`` and made it abstract.
- Added ``DefinitionIsChildOfConstraint`` to verify that a given service has the expected parent service.
  Also added a shortcut for using this constraint in the test cases: ``assertContainerBuilderHasServiceDefinitionWithParent($serviceId, $parentServiceId)``
