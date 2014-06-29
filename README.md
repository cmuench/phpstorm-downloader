# PhpStorm EAP Downloader

Downloads PhpStorm to a defined folder and creates a Symlink to the new version.
It can also cleanup old PhpStorm versions in file.

Requirements:

* >= PHP 5.4.0
* Linux or MacOS (Windows not supported)
* wget (For PhpStorm download)

## Installation

```
composer.phar install
```

or create phar file. See "Create phar file".

Or via composer.phar

```
$> composer.phar global require 'cmuench/phpstorm-downloader=1.0.5'
```

Call it as ~/.composer/vendor/bin/phpstorm-downloader

## Usage

### Download

``` 
$> bin/phpstorm-downloader download <target-folder>
```

Default target folder is: $HOME/opt

### Cleanup

```
$> bin/phpstorm-downloader clean <target-folder>
```

Default target folder is: $HOME/opt

## Create phar file

You can create a executable phar file of this application.
You need [box.phar](http://box-project.org) to create the phar. The repository contains a
valid box.json file for that.
