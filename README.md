# api-php

iMeet® Central CLI and API Sample Code

Fill in config/client_config.yml with your credentials.   To grab credentials, please see the API tab on the "Company Admin" sections on your account.

This project's dependencies are managed by composer.  See [GetComposer.org](https://getcomposer.org/) for installation instructions.

```composer install```

For example purposes, grab an Auth access token.
```php ./bin/imc auth:token```

JSON list of users in your account
```php ./bin/imc users:list```

For a full list of commands
```php ./bin/imc```
