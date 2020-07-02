# Embryo Assets
Embryo Assets is a simple PHP library that combines, minify and serves css or javascript files in inline mode or in a bundle file.

## Features
* Files combined, compressed and cached until they are modified.
* Uses an online service provided by Andy Chilton, [http://chilts.org/](http://chilts.org/).
* Replace relative path with absolute path in css file.

## Requirements
* PHP >= 7.1

## Installation
Using Composer:
```
$ composer require davidecesarano/embryo-assets
```

## Usage

```php
use Embryo\Assets\Assets;

Assets::css([
    'css/file1.css',
    'css/file2.css',
    'css/file3.css'
])
->setAssetsPath('/path/to/source/assets/')
->setCompilersPath('/path/to/source/compilers/')
->build()
->inline();
```

This will return:
```html
<style>
    // css code here
</style>
```

Files are combined, compressed and cached in one file. When you modify one file or change array css file, it compiling the file again.
```
/path/to/source/compilers/app.css.map -> Sources array
/path/to/source/compilers/app.css -> Css code
```

### Use bundle
If you want include bundle file instead of inline mode, use this:

```php
use Embryo\Assets\Assets;

Assets::css([
    'css/file1.css',
    'css/file2.css',
    'css/file3.css'
])
->setAssetsPath('/path/to/source/assets/')
->setCompilersPath('/path/to/source/compilers/')
->build()
->bundle();
```

This will return:

```html
<link rel="stylesheet" href="/path/to/source/compilers/app.css" />
```

### Resolve the relative path in css file
If you have CSS with relative paths use this:

```php
use Embryo\Assets\Assets;

Assets::css([
    'css/file1.css',
    'css/file2.css',
    'css/file3.css'
])
->setAssetsPath('/path/to/source/assets/')
->setCompilersPath('/path/to/source/compilers/'),
->resolveRelativePath('/path/to/absolute/')
->build()
->bundle();
```

### JS
It's same the CSS example. You must replace css static method with js static method. The `inline()` and `bundle()` methods returns:

```
// inline()
<script>
    // js code here
</script>

// bundle()
<script src="/path/to/source/compilers/app.js"></script>
```
