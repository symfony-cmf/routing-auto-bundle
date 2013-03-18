Terminology
===========

 - **Path provider**: Class that provides an ordered list of URL components to be made up
   into a path.

 - **Route patcher**: Class that creates Route objects for missing path components

 - **Endpoint**: The last component in a path / The Route that contains the automatic
   RouteContent.

 - **Route maker**: Class that handles the creation of the route objects. Typically this
   will have a Route Patcher injected into its constructor.

 - **BuilderUnit**: Class which holds one each of PathProvider, PathExistsAction and 
   PathNotExists action as defined by mapping. A builder unit should be able to resolve
   a ``BuilderUnitRouteStack``.
 
 - **Builder**: Wraps a builder unit, executing each element in the unit. I wonder if this
   class is redundant...

 
