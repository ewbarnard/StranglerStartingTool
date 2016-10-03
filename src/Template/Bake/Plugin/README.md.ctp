<%
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
%>
# <%= $plugin %> plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

``` bash
composer config repositories.<%= $package_name %> '{"type": "path", "url": "../../Packages/CakePhp3/<%= $plugin %>"}'
composer require <%= $package_vendor %>/<%= $package_name %> dev-master
```

## Development

Load dependencies:

``` bash
composer self-update && composer update && composer dump-autoload
```

Run lint check:

``` bash
composer lint
```

