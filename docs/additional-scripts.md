## Additional composer scripts
You can test and analyze code with some additional scripts in composer.

List all available scripts:
```shell
composer run -l
```

Output something like:
```text
scripts:
  test             Run the whole PHPUnit test suit                                                                                              
  coverage         Run the whole PHPUnit test with Coverage suit. Output in HTML at "docs/coverage/html"                                        
  coverage-clover  Run the whole PHPUnit test with Coverage suit. Output in clover xml at "docs/coverage/"                                      
  mess             Runs the mess script as defined in composer.json.                                                                            
  pipeline-mess    Runs phpmd Mess Analysis on some scopes and return non 0 exit status if rules are violated. Reasonable for CI/CD pipelines.
```

### Automated Testing and Coverage
#### Testing
Requires `PHPUnit`, `php-mbstring`, `php-xdebug`, `php-bcmath` (GMP extension will not work. It has loss of precision,
so some tests will fail)

For APT-GET compatible OS those could be installed like this:
```shell
sudo apt install php-mbstring php-xdebug php-bcmath
```

Running tests
```shell
composer run test
```

#### Code Coverage
Requires `PHPUnit`

```shell
composer run coverage
```

----

### Mess Analysis
Requires `phpmd`

```shell
composer run mess
```
