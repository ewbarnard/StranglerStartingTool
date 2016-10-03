<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

/**
 * Package shell command.
 */
class PackageShell extends Shell
{

    protected $_source = '';

    protected $_destination = '';

    protected static $_files = [
        'README.md', 'CHANGELOG.md', 'CONTRIBUTING.md', 'LICENSE.md', 'composer.json',
        'src/SkeletonClass.php', 'tests/ExampleTest.php',
    ];

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        return parent::getOptionParser()
            ->description(__('Create new plain (non-CakePHP) package from standard skeleton'))
            ->addOption('directory', [
                'short' => 'd',
                'help' => __('CamelCase folder name for the new package'),
                'required' => true,
            ])->addOption('author_name', [
                'short' => 'A',
                'help' => __('Your name'),
                'required' => true,
            ])->addOption('author_username', [
                'short' => 'u',
                'help' => __('Your subversion username'),
                'required' => true,
            ])->addOption('vendor', [
                'short' => 'V',
                'help' => __('Vendor name, i.e., namespace prefix'),
                'default' => 'InboxDollars',
            ])->addOption('package_name', [
                'short' => 'P',
                'help' => __('Package name, e.g., your-package'),
                'required' => true,
            ])->addOption('package_description', [
                'short' => 'D',
                'help' => __('Package description with no funky characters or you\'ll need to fix this script'),
                'required' => true,
            ]);
    }

    /**
     * main() method.
     *
     * @return bool|int Success or error code.
     */
    public function main()
    {
        $this->_copySkeleton();
        $this->_editFiles();
        $this->quiet("Created package at {$this->_destination}", 2);

        return true;
    }

    /**
     * Loop through the list of files to edit
     *
     * @return void
     */
    protected function _editFiles()
    {
        foreach (static::$_files as $file) {
            $this->_editFile($file);
        }
    }

    /**
     * Edit the specific file
     * @param string $name File name
     * @return void
     */
    protected function _editFile($name)
    {
        $file = new File($this->_destination . DS . $name);
        $page = $file->read();
        switch ($name) {
            case 'composer.json':
                $search = [
                    ':vendor\\\\:package_name',
                    '"phpunit"',
                    '"phpcbf',
                    'https://github.com/:vendor/:package_name',
                ];
                $replace = [
                    $this->params['vendor'] . '\\\\' . $this->params['directory'],
                    '"./vendor/bin/phpunit"',
                    '"./vendor/bin/phpcbf',
                    ':author_website',
                ];
                $page = str_replace($search, $replace, $page);
                break;
            case 'README.md':
                $search = [
                    '# :package_name',
                    'League\\Skeleton',
                ];
                $replace = [
                    '# ' . $this->params['vendor'] . '/' . $this->params['directory'],
                    $this->params['vendor'] . '\\' . $this->params['directory'] . '\\' . $this->params['directory'] . 'Class',
                ];
                $page = str_replace($search, $replace, $page);
                $pattern = [
                    '/\`\`\` bash.*?\`\`\`/ms',
                    '/\[\!\[Latest Version.*users and contributors\.\s+/ms',
                    '/\#\# Credits.*/ms',
                ];
                $replace = [
                    '``` bash' . PHP_EOL . '$ composer config repositories.' . $this->params['package_name'] .
                    ' \'' . '{"type": "path", "url": "../../Packages/Plain/' . $this->params['directory'] .
                    '"}\'' . PHP_EOL . '$ composer require ' . strtolower($this->params['vendor']) .
                    '/' . $this->params['package_name'] . ' dev-master' . PHP_EOL . '```',
                    $this->params['package_description'] . PHP_EOL . PHP_EOL,
                    PHP_EOL . PHP_EOL . '## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.',
                ];
                $page = preg_replace($pattern, $replace, $page);
                break;
            case 'CONTRIBUTING.md':
                $page = preg_replace('/via Pull Requests.*/', 'via Pull Requests.', $page);
                break;
            case 'src/SkeletonClass.php':
            case 'tests/ExampleTest.php':
                $search = [
                    'League',
                    'Skeleton',
                ];
                $replace = [
                    $this->params['vendor'],
                    $this->params['directory'],
                ];
                $page = str_replace($search, $replace, $page);
                break;
            default:
                break;
        }
        $search = [
            ':author_name', ':author_username', ':author_website',
            ':author_email', ':vendor', ':package_name', ':package_description',
        ];
        $replace = [
            $this->params['author_name'], $this->params['author_username'],
            'http://inboxdollars.com',
            $this->params['author_username'] . '@inboxdollars.com',
            strtolower($this->params['vendor']), $this->params['package_name'],
            $this->params['package_description'],
        ];
        $page = str_replace($search, $replace, $page);
        $file->write($page);
    }

    /**
     * Copy the league skeleton to the named directory
     *
     * @return void
     */
    protected function _copySkeleton()
    {
        $this->_source = dirname(ROOT) . DS . 'ThePhpLeague' . DS . 'skeleton';
        $this->_destination = dirname(ROOT) . DS . 'Packages' . DS . 'Plain' . DS . $this->params['directory'];
        $folder = new Folder();
        $folder->copy([
            'to' => $this->_destination,
            'from' => $this->_source,
            'mode' => 0755,
            'skip' => ['.git', '.gitattributes', '.gitignore'],
            'scheme' => Folder::SKIP,
        ]);
    }
}
