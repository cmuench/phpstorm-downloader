# PhpStorm EAP Downloader

Downloads PhpStorm to a defined folder and creates a Symlink to the new version.
It can also cleanup old PhpStorm versions in file.

Requirements:

* PHP 5.4.0+
* Linux or MacOS (Windows not supported)
* wget (For PhpStorm download)

## Installation

```
$> composer.phar install
```

Or create phar file. See section  *Create phar file* below.

Or via composer.phar

```
$> composer.phar global require 'cmuench/phpstorm-downloader=1.0.8'
```

Call it as ~/.composer/vendor/bin/phpstorm-downloader

## Usage

### Download

``` 
$> bin/phpstorm-downloader download [--stable] [--download] [--symlink-name <symlink-name>] <target-folder>
```

* `--stable`: download stable version, not EAP.

* `--download`: if the revision was already downloaded (switching stable/eap, change name of symlink), download it
 again regardless (force download).

* ` --symlink-name <symlink-name>`: name of the symlink to use. Default symlink name is: `PhpStorm`.

* `<target-folder>`: folder where PhpStorm versions are installed into. Default target folder is: `$HOME/opt`.

### Cleanup

```
$> bin/phpstorm-downloader clean <target-folder>
```

Default target folder is: $HOME/opt

## Create phar file

You can create a executable phar file of this application.

You need [box.phar from the box-project](https://box-project.github.io/box2/) to create the phar. The repository
contains a valid box.json file for that.
