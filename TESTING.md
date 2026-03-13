# Running the Test Suite

This document is targeted at developers looking to make modifications to this application's code base and want to run the existing test suite.

Before starting, follow the [instructions](README.md#installation) for installing the application locally and ensure you can load it in a browser properly.

## Unit and Feature Tests

Before attempting to run the test suite copy the example environment file for tests and update the values to match your environment:

`cp .env.testing.example .env.testing`

The following should work for running tests in memory with sqlite:
```
# --------------------------------------------
# REQUIRED: BASIC APP SETTINGS
# --------------------------------------------
APP_ENV=testing
APP_DEBUG=true
APP_KEY=base64:glJpcM7BYwWiBggp3SQ/+NlRkqsBQMaGEOjemXqJzOU=
APP_URL=http://localhost:8000
APP_TIMEZONE='UTC'
APP_LOCALE=en-US

# --------------------------------------------
# REQUIRED: DATABASE SETTINGS
# --------------------------------------------
DB_CONNECTION=sqlite_testing
#DB_HOST=127.0.0.1
#DB_PORT=3306
#DB_DATABASE=null
#DB_USERNAME=null
#DB_PASSWORD=null
```

To use MySQL you should update the `DB_` variables to match your local test database:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={}
DB_USERNAME={}
DB_PASSWORD={}
```

Now you are ready to run the entire test suite from your terminal:

```shell
php artisan test
````

To run individual test files, you can pass the path to the test that you want to run:

```shell
php artisan test tests/Unit/AccessoryTest.php
```

Some tests, like ones concerning LDAP, are marked with the `@group` annotation. Those groups can be run, or excluded, using the `--group` or `--exclude-group` flags:

```shell
php artisan test --group=ldap

php artisan test --exclude-group=ldap
```
This can be helpful if a set of tests are failing because you don't have an extension, like LDAP, installed.
