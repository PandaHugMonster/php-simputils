# Files, Data Files and executables processing

## Files Infrastructure

Working with files can be really easy:

```php
use spaf\simputils\Boolean;
use spaf\simputils\PHP;
use function spaf\simputils\basic\fl;
use function spaf\simputils\basic\pd;
use function spaf\simputils\basic\pr;

require_once 'vendor/autoload.php';

PHP::init();

// Creating file in RAM
$file = fl();
pr("File size before write: {$file->size_hr}");
$file->content = "A secret data string :)))) !";
pr("File size after write: {$file->size_hr}");
pr("Content of the file: \"{$file->content}\"");

pr("===============================");

pr("Does file exist before moving from ram: ".Boolean::to($file->exists));
$file->move('/tmp', 'LOCAL_FILE_SYSTEM_FILE_random_name.txt');
pr("Does file exist after moving to HD: ".Boolean::to($file->exists));

pr("===============================");

// The location pointed is exactly the same, where we saved the file from RAM
$new_file = fl("/tmp/LOCAL_FILE_SYSTEM_FILE_random_name.txt");
pr("File {$new_file} | exists: ".Boolean::to($new_file->exists));
pr("File {$new_file} | size: {$new_file->size_hr}");
pr("File {$new_file} | content: \"{$new_file->content}\"");

// Moving from RAM to HD is possible, but from HD to RAM is not yet implemented.

// IMPORTANT: "content" read or write DOES REAL READING/WRITING EVERY SINGLE CALL!
//            So please if you need to use it multiple times in code - please store it in a var
```

**IMPORTANT**: "content" read or write **DOES REAL READING/WRITING EVERY SINGLE CALL**!
So please if you need to use it multiple times in code - please store it in a var

The output would be:

```text
File size before write: 0 B
File size after write: 28 B
Content of the file: "A secret data string :)))) !"
===============================
Does file exist before move from ram: false
Does file exist after move to HD: true
===============================
File /tmp/LOCAL_FILE_SYSTEM_FILE_random_name.txt | exists: true
File /tmp/LOCAL_FILE_SYSTEM_FILE_random_name.txt | size: 28 B
File /tmp/LOCAL_FILE_SYSTEM_FILE_random_name.txt | content: "A secret data string :)))) !"
```

Cool feature is to have ram files without real HD file created, and then moved/saved from
the RAM to the HD file-system.

P.S. Currently there is no explicit property to tell which file is RAM and which is HD,
but this feature should appear in the future releases.

----

In the example above there is a simple textual data is used. The processing capabilities
depend on the Resource App Processor and can be customized in any way you want.

Currently, there are a few of them:

* `\spaf\simputils\models\files\apps\CsvProcessor` - CSV file processing
* `\spaf\simputils\models\files\apps\DotEnvProcessor` - ".env" file processing
* `\spaf\simputils\models\files\apps\JsonProcessor` - JSON files processing
* `\spaf\simputils\models\files\apps\TextProcessor` - Default used for any non-recognized file types
* `\spaf\simputils\models\files\apps\PHPFileProcessor` - Special processor, **it is not allowed to
  be used directly** for security reasons!

By default, if file is not recognized then `TextProcessor` is used.

The processor can be used explicitly when creating file object or can be re-assigned later:

```php
use spaf\simputils\models\files\apps\JsonProcessor;
use spaf\simputils\models\files\apps\TextProcessor;
use spaf\simputils\PHP;
use function spaf\simputils\basic\bx;
use function spaf\simputils\basic\fl;
use function spaf\simputils\basic\pr;

require_once 'vendor/autoload.php';

PHP::init();

$fl = fl(app: new JsonProcessor());
// You can use a normal array `[]` instead of `bx([])` Box.
$fl->content = bx([
	'something' => 'another',
	'a' => 12,
	'b' => [
		'33' => 500,
		'gg' => 'TTTTTT'
	]
]);
pr("Simple ARRAY returned from the file: ", $fl->content);

$fl->app = new TextProcessor();
pr("Textual content of the file: ", $fl->content);

```

Output:

```
Simple ARRAY returned from the file: 
Array
(
    [something] => another
    [a] => 12
    [b] => Array
        (
            [33] => 500
            [gg] => TTTTTT
        )

)

Textual content of the file: 
{"something":"another","a":12,"b":{"33":500,"gg":"TTTTTT"}}
```

It's really nice way to work with files, when the processing/parsing/generation of
the content of the file depends on the app.

P.S. Found a tiny bug in using one file object for different file types, please avoid for now
changing file type. https://github.com/PandaHugMonster/php-simputils/issues/124

Please do not use `PHPFileProcessor`. It's a really special processor, which is used in some rare
cases across the framework. But it should never be explicitly used. Do not override it


-----

Files App Processors can be set as default by "mime-types" as well, instead of explicit
specification
of each object with the exact Resource App Processor.

```php

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\BasicResource;
use spaf\simputils\generic\BasicResourceApp;
use spaf\simputils\models\File;
use spaf\simputils\PHP;
use function spaf\simputils\basic\fl;
use function spaf\simputils\basic\pr;

require_once 'vendor/autoload.php';

PHP::init();

/**
 * @property ?string $dummy_text
 */
class DummyWrapperProcessor extends BasicResourceApp {

	#[Property]
	protected ?string $_dummy_text = '== Default / Special / Dummy / Text ==';

	/**
	 * @inheritDoc
	 */
	public function getContent(mixed $fd, ?BasicResource $file = null): mixed {
		return $this->_dummy_text;
	}

	/**
	 * @inheritDoc
	 */
	public function setContent(mixed $fd, mixed $data, ?BasicResource $file = null): void {
		$this->dummy_text = $data;
	}

}

PHP::redef(File::class)::$processors['text/plain'] = DummyWrapperProcessor::class;

$file = fl();
pr($file->content);
$file->content = '-- NEW STRING --';
pr($file->content);

```

**Important**: `PHP::redef(File::class)` is an easy way to refer to the `File` class,
it is safer than using just a `File` class directly, because in your system you might redefine some
models/classes - and then in this case you might break compatibility.
The `PHP::redef(File::class)` is used to avoid such a problem.

**Additionally**: The `text/plain` mime-type is the default one for unspecified cases. So
if you redefine it, any unspecified/unrecognized files will be using it!

You can redefine the complete set of supported mime-types. And even create your own.

#### Data Files

Data Files are scoped set of files with configs or some stored info in files for your application.

It's a common thing to want to save a small config into JSON or "PHP-Array" files, and
then read them and use.

Or store some test-fixture or dictionary data for DB migrations.

All of that is easily achievable through Data Files.

-----

To use data files you need to specify permitted folders from which those files will be used.
Without specifying those directories - files will not be accessible as "Data Files".

```php
use spaf\simputils\FS;
use spaf\simputils\PHP;
use function spaf\simputils\basic\pd;
use function spaf\simputils\basic\pr;

require_once 'vendor/autoload.php';

PHP::init([
	'allowed_data_dirs' => [
	    // This is the specification of the data folders.
	    // It's always considered from the "working_dir"
		'data',
	]
]);

$data_php = FS::data(['data', 'my-test-php-array-inside.php']);
pr($data_php);

$data_php = FS::data(['data', 'spec', 'my-spec.json']);
pr($data_php);
```

Keep in mind that the data is retrieved through the Files infrastructure.
This is why you could alternatively could use directly through "File" objects.

```php
use spaf\simputils\FS;
use spaf\simputils\PHP;
use function spaf\simputils\basic\pd;
use function spaf\simputils\basic\pr;

require_once 'vendor/autoload.php';

PHP::init([
	'allowed_data_dirs' => [
		'data'
	]
]);

$data_php = FS::dataFile(['data', 'my-test-php-array-inside.php']);
pr($data_php->content);

$data_php = FS::dataFile(['data', 'spec', 'my-spec.json']);
pr($data_php->content);
```

The output would be exactly the same.

It's recommended to use `FS::data()` over `FS::dataFile()`.

## DotEnv

The framework supports out of the box DotEnv (.env) files auto-loading during the init phase.

Some features like "layered conditional loading" is not yet finished, but the default `.env`
loading works fine.

Export the Env-Var of the terminal where you will run the script:
```shell
export DENV_VAR_2="Redefined by EnvVars"
```

Create `.env` file in the code root-dir with the content:
```shell
DENV_VAR_1="My variable 1"
DENV_VAR_2="My variable 2"
#DENV_VAR_3="My variable 3"
DENV_VAR_4="My variable 4"
```

Keep in mind that `DENV_VAR_3` is commented out (just to highlight that commenting in `.env` works)

And the script code:
```php
use spaf\simputils\PHP;
use function spaf\simputils\basic\pr;

require_once 'vendor/autoload.php';

PHP::init();

$all_envs = PHP::allEnvs();

pr($all_envs->extract('DENV_VAR_1', 'DENV_VAR_2', 'DENV_VAR_4'));
```

Output:
```text
spaf\simputils\models\Box Object
(
    [DENV_VAR_1] => My variable 1
    [DENV_VAR_2] => Redefined by EnvVars
    [DENV_VAR_4] => My variable 4
)
```

As you can see, the `.env` is loaded, but the values specified in the shell-environment
overriding those if specified.

## Executables Processing

Files Infrastructure is not supposed to be used to execute "PHP" files (except through Data Files).

So the following code:

```php
use spaf\simputils\PHP;
use function spaf\simputils\basic\fl;
use function spaf\simputils\basic\pd;
use function spaf\simputils\basic\pr;

require_once 'vendor/autoload.php';

PHP::init();

$file = fl(['run.php']);
pr($file->content);
```

and

```php
use spaf\simputils\PHP;
use function spaf\simputils\basic\fl;
use function spaf\simputils\basic\pd;
use function spaf\simputils\basic\pr;

require_once 'vendor/autoload.php';

PHP::init();

// spec-file-exec contains PHP code and system identifies it by mime-type as PHP code
$file = fl(['spec-file-exec']);
pr($file->content);
```

Both above code cases will cause exception:

```text

Fatal error: Uncaught spaf\simputils\exceptions\ExecutablePermissionException: Executables like PHP should not be processed through the File infrastructure (except some rare cases) in /home/ivan/development/php-simputils/src/models/files/apps/PHPFileProcessor.php:33
Stack trace:
#0 /home/ivan/development/php-simputils/src/generic/BasicResourceApp.php(80): spaf\simputils\models\files\apps\PHPFileProcessor->getContent(Resource id #59, Object(spaf\simputils\models\File))
#1 /home/ivan/development/php-simputils/src/models/File.php(454): spaf\simputils\generic\BasicResourceApp->__invoke(Object(spaf\simputils\models\File), Resource id #59, true, NULL)
#2 /home/ivan/development/php-simputils/src/traits/PropertiesTrait.php(74): spaf\simputils\models\File->getContent(NULL, 'get', 'content')
#3 /home/ivan/development/php-simputils/run.php(16): spaf\simputils\generic\SimpleObject->__get('content')
#4 {main}
  thrown in /home/ivan/development/php-simputils/src/models/files/apps/PHPFileProcessor.php on line 33

Process finished with exit code 255

```

**This behaviour is very intended due to security reasons!** Do not try to override this behaviour.

-----

## Streamlined reading of file

Under streamlined reading considered reading byte-by-byte data from a file/stream without
loading your whole file/stream into memory. For files over a couple MB it's a must!

**Note:** This functionality is not fully finished, and Streamlined writing is not yet there.
You still can use directly `fd` (file descriptor) to write if you need.

```php

use function spaf\simputils\basic\fl;
use function spaf\simputils\basic\with;

// Temp/anonymous file
$f = fl();

// Writing content to a file (non-streamlined)
$f->content =
	"my content 1\n" .
	"my content 2\n" .
	"my content 3\n" .
	"my content 4\n" .
	"my content 5\n";

// Reading line by line file 2 times (streamlined reading)
with($f, function ($fa) {
	/** @var TextFileDataAccess $fa */
	// Reading group in case of text file means "line", for other
	// processing apps it could mean something else.
	while ($line = $fa->readGroup()) {
		$line = trim($line);
		echo "|{$line}|\n\n";
	}

    // Rewinding the cursor to start of the file
	$fa->rewind();

    // Reading again
	while ($line = $fa->readGroup()) {
		$line = trim($line);
		echo "|{$line}|\n\n";
	}
});

```

Output:
```text
|my content 1|

|my content 2|

|my content 3|

|my content 4|

|my content 5|

|my content 1|

|my content 2|

|my content 3|

|my content 4|

|my content 5|
```

## Special notes

1. For an every `PHP::init()` process the ".env" file is searched and processed, so it's easy
   to specify/modify env variables. The values are accessible through `env()` function.
   In case you want to disable it:
   ```php
     PHP::init([
       'disable_init_for' => [
         DotEnvInitBlock::class,
       ]
     ]);
   ```
2. **ALL THE ENV VARS MUST BE REFERRED IN UPPER CASE!!!** So if you have the .env vars like this:
   "test_1" - then in `env('TEST_1')` always use the UPPER CASE! It's mandatory due
   to best practices.
