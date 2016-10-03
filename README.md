# Standalone Package and Project Builder

Use this project's shell commands to start new packages or projects.
This tool is open source with the MIT license at
[Strangler Pattern Starting Tool](https://github.com/ewbarnard/StranglerStartingTool).

## Usage

Use this tool to:

 - Create a plain (non-CakePHP) package to be used via Composer
 - Create a CakePHP 3.x plugin
 - Start a new CakePHP 3.x project (i.e., a new set of microservices)

This tool assumes the InboxDollars directory structure. Our convention
(for the moment) is to specify package locations by relative path in our
filesystem. Composer pulls them in via symbolic link.

We commit code to Subversion with no developer dependencies
(`composer update --no-dev --no-suggest`) and with the debug flag false
in config/app.php. We roll to production with a simple `svn up` so be
careful!

### Non-CakePHP Plain Package

When you have common code to be shared amongst the CakePHP 3.x projects,
build it as a plain package. You will then edit each project's
composer.json file to "require" the package.

If this is code to be used by _all_ future packages, edit the project.sh
script to add this to its list of dependencies to automatically
include.

To start a new plain (non-CakePHP) package:

``` bash
bin/cake package \
  --author_name 'Your Name' \
  --author_username 'Your Subversion Username' \
  --vendor InboxDollars \
  --package_name 'your-package' \
  --package_description 'Your Description' \
  --directory YourPackage
```

Type `bin/cake package --help` for options and defaults.

### CakePHP 3.x Plugin

To start a CakePHP 3.x plugin package:

``` bash
bin/cake bake plugin --package_name 'your-plugin' \
  --package_description 'Your Description' \
  YourPlugin
```

Type `bin/cake start_plugin --help` for options and defaults.

### CakePHP 3.x Project

This is your starting point for creating new InboxDollars microservices
or sets of microservices. This folder contains a shell script. Pass it
the CamelCased folder name of the new project to create:

``` bash
./project.sh MyNewProject
```

My workflow continues as follows:

 1. cd Projects/MyNewProject
 1. rm .git* .travis*
 1. composer update --no-dev --no-suggest
 1. svn add .
 1. composer update _(bring in dev dependencies)_
 1. In PhpStorm, create new project from existing files
 1. Edit config/app.php as needed: Remove Datasources default
 1. Edit config/bootstrap.php: Remove date_default_timezone_set()
 1. In src/:
    - mkdir Lib/MyNewProject
    - Copy in Shell/BatsShell.php
 1. Our private developer wiki documents the process from here
