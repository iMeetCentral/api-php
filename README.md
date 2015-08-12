# api-php

Central Desktop CLI and API Sample Code

Fill in config/client_config.yml with your credentials.   To grab credentials, please see the API tab on the "Company Admin" sections on your account.

This project's dependencies are managed by composer.  See [GetComposer.org](https://getcomposer.org/) for installation instructions.

```composer install```

For example purposes, grab an Auth access token.
```php ./bin/cd.php auth:token```

JSON list of users in your account
```php ./bin/cd.php users:list```

For a full list of commands
```php ./bin/cd.php```