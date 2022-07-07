# Advanced Forms Tests

`/Core` directory contains tests for all functionality that is common and consistent across both free and pro versions
of the plugin.

`/Free` directory contains tests that are specific to the free version.

`/Pro` directory contains tests that are specific to the pro version.

## Running specific suites

Test suites are defined in `advanced-forms/phpunit.xml.dist`.

`phpunit --testsuite="free"` to run tests while on the free branch.
`phpunit --testsuite="pro"` to run tests while on the pro branch.