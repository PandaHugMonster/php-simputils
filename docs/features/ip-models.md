### IPv4 model

Simple example:

```php

$ic = PHP::init([
	'l10n' => 'AT',
]);

/**
 * @property ?string $name
 * @property ?IPv4 $my_ip
 */
class Totoro extends SimpleObject {

	#[Property]
	protected ?string $_name = null;

	#[Property]
	protected ?IPv4 $_my_ip = null;

}

$t = new Totoro;

$t->name = 'Totoro';
$t->my_ip = '127.0.0.1/16';
$t->my_ip->output_with_mask = false;

pr("I am {$t->name} and my address is {$t->my_ip} (and ip-mask is {$t->my_ip->mask})");

```

The output would be:

```
I am Totoro and my address is 127.0.0.1 (and ip-mask is 255.255.0.0)
```
