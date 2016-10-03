<?php
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
namespace Bake\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;

/**
 * The Plugin Task handles creating an empty plugin, ready to be used
 *
 */
class PluginTask extends BakeTask
{
    /**
     * Path to the bootstrap file. Changed in tests.
     *
     * @var string
     */
    public $bootstrap = null;

    /**
     * Tasks this task uses.
     *
     * @var array
     */
    public $tasks = [
        'Bake.BakeTemplate'
    ];

    /**
     * initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->path = current(App::path('Plugin'));
        $this->bootstrap = ROOT . DS . 'config' . DS . 'bootstrap.php';
    }

    /**
     * Execution method always used for tasks
     *
     * @param string|null $name The name of the plugin to bake.
     * @return null|bool
     */
    public function main($name = null)
    {
        if (empty($name)) {
            $this->err('<error>You must provide a plugin name in CamelCase format.</error>');
            $this->err('To make an "MyExample" plugin, run <info>`cake bake plugin MyExample`</info>.');

            return false;
        }
        $plugin = $this->_camelize($name);
        $pluginPath = $this->_pluginPath($plugin);
        if (is_dir($pluginPath)) {
            $this->out(sprintf('Plugin: %s already exists, no action taken', $plugin));
            $this->out(sprintf('Path: %s', $pluginPath));

            return false;
        }
        if (!$this->bake($plugin)) {
            $this->error(sprintf("An error occurred trying to bake: %s in %s", $plugin, $this->path . $plugin));
        }
    }

    /**
     * Bake the plugin's contents
     *
     * Also update the autoloader and the root composer.json file if it can be found
     *
     * @param string $plugin Name of the plugin in CamelCased format
     * @return bool|void
     */
    public function bake($plugin)
    {
        $pathOptions = App::path('Plugin');
        if (count($pathOptions) > 1) {
            $this->findPath($pathOptions);
        }
        $this->out(sprintf("<info>Plugin Name:</info> %s", $plugin));
        $this->out(sprintf("<info>Plugin Directory:</info> %s", $this->path . $plugin));
        $this->hr();

        $this->_generateFiles($plugin, $this->path);

        $source = $this->path . $plugin;
        $destination = dirname(ROOT) . DS . 'Packages' . DS . 'CakePhp3' . DS . $plugin;
        $folder = new Folder();
        $folder->copy([
            'to' => $destination,
            'from' => $source,
            'mode' => 0755,
            'skip' => ['.git', '.gitattributes', '.gitignore'],
            'scheme' => Folder::SKIP,
        ]);
        $folder->delete($source);

        $this->hr();
        $this->out(sprintf('<success>Created:</success> %s in %s', $plugin, $destination), 2);

        $emptyFile = $this->path . 'empty';
        $this->_deleteEmptyFile($emptyFile);

        return true;
    }

    /**
     * Generate all files for a plugin
     *
     * Find the first path which contains `src/Template/Bake/Plugin` that contains
     * something, and use that as the template to recursively render a plugin's
     * contents. Allows the creation of a bake them containing a `Plugin` folder
     * to provide customized bake output for plugins.
     *
     * @param string $pluginName the CamelCase name of the plugin
     * @param string $path the path to the plugins dir (the containing folder)
     * @return void
     */
    protected function _generateFiles($pluginName, $path)
    {
        $namespace = str_replace('/', '\\', $pluginName);

        $name = $pluginName;
        $vendor = 'your-name-here';
        if (strpos($pluginName, '/') !== false) {
            list($vendor, $name) = explode('/', $pluginName);
        }
        $package = $vendor . '/' . $name;

        $this->BakeTemplate->set([
            'package' => $package,
            'namespace' => $namespace,
            'plugin' => $pluginName,
            'routePath' => Inflector::dasherize($pluginName),
            'path' => $path,
            'root' => ROOT,
            'vendor' => $this->params['vendor'],
            'package_vendor' => strtolower($this->params['vendor']),
            'description' => $this->params['package_description'],
            'package_name' => $this->params['package_name'],
        ]);

        $root = $path . $pluginName . DS;

        $paths = [];
        if (!empty($this->params['theme'])) {
            $paths[] = Plugin::path($this->params['theme']) . 'src/Template/';
        }

        $paths = array_merge($paths, Configure::read('App.paths.templates'));
        $paths[] = Plugin::path('Bake') . 'src/Template/';

        do {
            $templatesPath = array_shift($paths) . 'Bake/Plugin';
            $templatesDir = new Folder($templatesPath);
            $templates = $templatesDir->findRecursive('.*\.ctp');
        } while (!$templates);

        sort($templates);
        foreach ($templates as $template) {
            $template = substr($template, strrpos($template, 'Plugin') + 7, -4);
            $this->_generateFile($template, $root);
        }
    }

    /**
     * Generate a file
     *
     * @param string $template The template to render
     * @param string $root The path to the plugin's root
     * @return void
     */
    protected function _generateFile($template, $root)
    {
        $this->out(sprintf('Generating %s file...', $template));
        $out = $this->BakeTemplate->generate('Plugin/' . $template);
        $this->createFile($root . $template, $out);
    }

    /**
     * find and change $this->path to the user selection
     *
     * @param array $pathOptions The list of paths to look in.
     * @return void
     */
    public function findPath(array $pathOptions)
    {
        $valid = false;
        foreach ($pathOptions as $i => $path) {
            if (!is_dir($path)) {
                unset($pathOptions[$i]);
            }
        }
        $pathOptions = array_values($pathOptions);
        $max = count($pathOptions);

        if ($max === 0) {
            $this->err('No valid plugin paths found! Please configure a plugin path that exists.');
            throw new \RuntimeException();
        }

        if ($max === 1) {
            $this->path = $pathOptions[0];

            return;
        }

        while (!$valid) {
            foreach ($pathOptions as $i => $option) {
                $this->out($i + 1 . '. ' . $option);
            }
            $prompt = 'Choose a plugin path from the paths above.';
            $choice = $this->in($prompt, null, 1);
            if ((int)$choice > 0 && (int)$choice <= $max) {
                $valid = true;
            }
        }
        $this->path = $pathOptions[$choice - 1];
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description(
            'Create the directory structure, AppController class and testing setup for a new plugin. ' .
            'Can create plugins in any of your bootstrapped plugin paths.'
        )->addArgument('name', [
            'help' => 'CamelCased name of the plugin to create.',
            'required' => true
        ])->addOption('composer', [
            'default' => ROOT . DS . 'composer.phar',
            'help' => 'The path to the composer executable.'
        ])->removeOption('plugin')->addOption('vendor', [
            'short' => 'V',
            'help' => __('Vendor name, i.e., namespace prefix'),
            'default' => 'InboxDollars',
        ])->addOption('package_name', [
            'short' => 'P',
            'help' => __('Package name, e.g., your-package'),
            'required' => true,
        ]);

        return $parser;
    }

    /**
     * Uses either the CLI option or looks in $PATH and cwd for composer.
     *
     * @return string|false Either the path to composer or false if it cannot be found.
     */
    public function findComposer()
    {
        if (!empty($this->params['composer'])) {
            $path = $this->params['composer'];
            if (file_exists($path)) {
                return $path;
            }
        }
        $composer = false;
        $path = env('PATH');
        if (!empty($path)) {
            $paths = explode(PATH_SEPARATOR, $path);
            $composer = $this->_searchPath($paths);
        }

        return $composer;
    }

    /**
     * Search the $PATH for composer.
     *
     * @param array $path The paths to search.
     * @return string|bool
     */
    protected function _searchPath($path)
    {
        $composer = ['composer.phar', 'composer'];
        foreach ($path as $dir) {
            foreach ($composer as $cmd) {
                if (is_file($dir . DS . $cmd)) {
                    $this->_io->verbose('Found composer executable in ' . $dir);

                    return $dir . DS . $cmd;
                }
            }
        }

        return false;
    }
}
