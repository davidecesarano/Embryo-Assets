# Embryo Assets
Embryo Assets is a simple PHP library that combines, minify and serves css or javascript files in inline mode or in a bundle file.

## Features
* Files combined, compressed and cached until they are modified.
* Uses an online service provided by Andy Chilton, [http://chilts.org/](http://chilts.org/).
* Replace relative path with root public path in css file.
* PSR-7 compatible.

## Requirements
* PHP >= 7.1
* A [PSR-7](https://www.php-fig.org/psr/psr-7/) http message implementation and [PSR-17](https://www.php-fig.org/psr/psr-17/) http factory implementation (ex. [Embryo-Http](https://github.com/davidecesarano/Embryo-Http))

## Installation
Using Composer:
```
$ composer require davidecesarano/embryo-assets
```

## Usage
```php
use Embryo\Assets\Assets;
use Embryo\Http\Factory\ServerRequestFactory;

$request = (new ServerRequestFactory)->createServerRequestFromServer();
$css = Assets::css([
    '/path/to/css/file1.css',
    '/path/to/css/file2.css',
    '/path/to/css/file3.css'
])
->setRequest($request)
->build('/path/to/source/compilers/');
```
This will produce:
```
/path/to/compilers/app.css.map -> Sources array
/path/to/compilers/app.css -> Css code
```
Now, you may use it in your template file:
```php
<style>
    <?php echo $css; ?>
</style>
```
Files are combined, compressed and cached in one file. When you modify one file or change array css file, it compiling the file again.

You may quickly test this using the built-in PHP server going to http://localhost:8000.
```
$ cd example
$ php -S localhost:8000
```

### Use bundle
If you want include bundle file instead of inline mode, be sure the compile file in a public folder (for example, in `assets` folder). 
```php
Assets::css([
    '/path/to/css/file1.css',
    '/path/to/css/file2.css',
    '/path/to/css/file3.css'
])
->setRequest($request)
->build('/path/to/assets/');
```
Now, you may include the file in `<link>` tag:
```html
<link rel="stylesheet" href="/assets/app.css" />
```

### JS
It's same the CSS example. You must replace css static method with `js` static method.

## Options
### `forceBuild(bool $forceBuild): self`
If `true` build file for every request. Default is `false`.

### `setFilename(string $filename): self`
Set the filename. Default is `app`.