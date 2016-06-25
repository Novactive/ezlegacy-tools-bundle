# Novactive eZ Publish Legacy Tools bundle

## About

This bundle provides post Composer install/update scripts to :

* install legacy settings from a bundle (the same way legacy extensions are managed natively)
* execute legacy scripts

## Configuration

You can add the following [extra](https://getcomposer.org/doc/04-schema.md#extra) parameters into your project's composer file :

Configuration            | Type             | Description                                                                                       |
-------------------------|------------------|---------------------------------------------------------------------------------------------------|
legacy-settings-install  | array of strings | ezpublish:legacybundles:install_settings command options. Possible entries : copy, symlink, force |
legacy-scripts-execution | array of strings | List of legacy scripts (with params) to be executed                                               |


## Installation

With composer :

    php composer.phar require novactive/ezlegacy-tools-bundle 

### Legacy settings installation

Add the following line to your composer.json file :

    ...
    "scripts": {
        ...
        "post-install-cmd": [
            ...,
            "Novactive\\EzLegacyToolsBundle\\Composer\\ScriptHandler::installLegacyBundlesSettings"
        ],
        "post-update-cmd": [
            ...,
            "Novactive\\EzLegacyToolsBundle\\Composer\\ScriptHandler::installLegacyBundlesSettings"
        ]
    }
    ...,
    "extra": {
        ...,
        "legacy-settings-install": ["force", "relative"]
    },
    ...


### Legacy scripts execution

Add the following line to your composer.json file :

    ...
    "scripts": {
        ...
        "post-install-cmd": [
            ...,
            "Novactive\\EzLegacyToolsBundle\\Composer\\ScriptHandler::executeLegacyScripts"
        ],
        "post-update-cmd": [
            ...,
            "Novactive\\EzLegacyToolsBundle\\Composer\\ScriptHandler::executeLegacyScripts"
        ]
    }
    ...,
    "extra": {
        ...,
        "legacy-scripts-execution": [
            "bin/php/ezpgenerateautoloads.php --kernel",
            "bin/php/ezpgenerateautoloads.php --extension",
            "bin/php/ezpgenerateautoloads.php --kernel-override"
        ]
    },
    ...


## Usage

### Legacy settings installation

The command will search for a legacy_settings folder in all project specific bundles. You can only have one bundle containing a legacy_settings directory otherwise an exception will be thrown.

The command will then search for a 'override' and 'siteaccess' directories in the legacy_settings directory.


## Contributing

In order to be accepted, your contribution needs to pass a few controls : 

* PHP files should be valid
* PHP files should follow the [PSR-2](http://www.php-fig.org/psr/psr-2/) standard
* PHP files should be [phpmd](https://phpmd.org) and [phpcpd](https://github.com/sebastianbergmann/phpcpd) warning/error free

To ease the validation process, install the [pre-commit framework](http://pre-commit.com) and install the repository pre-commit hook :

    pre-commit install

Finally, in order to homogenize commit messages across contributors (and to ease generation of the CHANGELOG), please apply this [git commit message hook](https://gist.github.com/GMaissa/f008b2ffca417c09c7b8) onto your local repository. 
