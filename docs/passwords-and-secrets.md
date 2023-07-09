# Passwords and Secrets explained

There are 2 models that let developer to operate easily with such security-sensitive information
like passwords, tokens, etc.

## Usage

The usage of those 2 models is really simple. Basically you need to specify `value`,
and optionally you can specify `name` and even `type` - which is basically just an additional 
identifier for you to mark objects to certain group/category.

```php
use spaf\simputils\models\Password;
use spaf\simputils\models\Secret;
use function spaf\simputils\basic\pr;

$secret = new Secret('To-Ke-N', 'my-secret-token');
$password = new Password('q1w-E5t6T7l-r8t1Y__23', 'my-password');

pr('SECRET MODEL:', $secret, "Stringified secret: {$secret}");
pr('PASSWORD MODEL:', $password, "Stringified password: {$password}");

```

Output:
```text
SECRET MODEL:
spaf\simputils\models\Secret Object
(
    [for_system] => ****
    [for_user] => **[S:my-secret-token]**
    [name] => my-secret-token
    [type] => secret
    [value] => ****
)

Stringified secret: **[S:my-secret-token]**
PASSWORD MODEL:
spaf\simputils\models\Password Object
(
    [for_system] => ****
    [for_user] => **[P:my-password]**
    [hash] => ****
    [name] => my-password
    [type] => password
    [value] => ****
)

Stringified password: **[P:my-password]**
```

P.S. If you access `value` property directly of those objects it will return to you the real value
(as well as `for_system` property)

The `\spaf\simputils\models\Password` model is inherited from `spaf\simputils\models\Secret`,
so their functionality is very similar.
The difference is that `\spaf\simputils\models\Password` adds additional functionality,
for password-hashing and verifying (both of which are customizable, if you will).

```php
use spaf\simputils\models\Password;
use function spaf\simputils\basic\pr;

$password = new Password('q1w-E5t6T7l-r8t1Y__23', 'my-password');
$verified = $password->verifyPassword('q1w-E5t6T7l-r8t1Y__23');

pr("Password hash: {$password->hash}");
pr("Is verified: {$verified}");

```

Output:
```text
Password hash: $2y$10$ocowJ/9gcrqMzVBfRKg1FOs0.tJ7P1P288ogPj4azrOBtodiUBCn6
Is verified: 1
```

To customize hashing/verifying functionality you can either rely on the default PHP
password hashing mechanics and specify algorithm which you desire by redefining like this:

```PHP
use spaf\simputils\models\Password;

Password::$default_hashing_algo = PASSWORD_ARGON2I;
```

or if you want having full control over the hashing/verifying, you can specify your own
callables for both functions like this (will set those globally):

```php
use spaf\simputils\models\Password;

Password::$global_hashing_callable = function ($password): string {
	// your hashing must happen here
	return $res;
};

Password::$global_hashing_verify_callable = function ($password, $hash): bool {
	// your hashing must happen here
	return $res;
};

```

or if you want to do it per object basis, just specify your callables when creating objects.
