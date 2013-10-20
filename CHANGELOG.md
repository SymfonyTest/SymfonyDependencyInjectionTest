# Changelog

## v0.1.1

- Renamed ``ContainerBuilderTestCase`` to ``AbstractContainerBuilderTestCase`` and made it abstract.
- Added ``DefinitionIsChildOfConstraint`` to verify that a given service has the expected parent service.
  Also added a shortcut for using this constraint in the test cases: ``assertContainerBuilderHasServiceDefinitionWithParent($serviceId, $parentServiceId)``
-