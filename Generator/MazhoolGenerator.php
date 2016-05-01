<?php namespace AwatBayazidi\Foundation\Generator;

use AwatBayazidi\Contracts\Generator\Generator;
use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use AwatBayazidi\Abzar\Stub;
use AwatBayazidi\Mazhool\Repository;

class MazhoolGenerator extends Generator
{

    protected $name;
    protected $config;
    protected $filesystem;
    protected $console;
    protected $module;
    protected $force = false;
    protected $plain = false;
    protected $stubs_replacements=[
        'start' => ['LOWER_NAME'],
        'routes' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE'],
        'json' => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE'],
        'views/index' => ['LOWER_NAME','MODEL_NAME_CAMEL'],
        'views/master' => ['STUDLY_NAME','LOWER_NAME'],
        'scaffold/config' => ['STUDLY_NAME'],
        'composer' => [
            'LOWER_NAME',
            'STUDLY_NAME',
            'VENDOR',
            'AUTHOR_NAME',
            'AUTHOR_EMAIL',
            'MODULE_NAMESPACE',
        ],
    ];
    protected $stubs_files = [
        'start'  => 'start.php',
        'routes' => 'Http/routes.php',
        'json'   => 'setting.json',
        'fields_example'   => 'fields_example.json',
        'views/index' => 'Resources/views/index.blade.php',
        'views/master' => 'Resources/views/layouts/master.blade.php',
        'scaffold/config' => 'Config/config.php',
        'composer' => 'composer.json',
    ];

    protected $pathsGenerator = [
        'assets' => 'Assets',
        'config' => 'Config',
        'command' => 'Console',
        'migration' => 'Database/Migrations',
        'model' => 'Models',
        'repository' => 'Repositories',
        'seeder' => 'Database/Seeders',
        'controller' => 'Http/Controllers',
        'controller_api' => 'Http/Controllers/Api',
        'filter' => 'Http/Middleware',
        'request' => 'Http/Requests',
        'provider' => 'Providers',
        'lang' => 'Resources/lang',
        'views' => 'Resources/views',
        'test' => 'Tests',
    ];

    public function __construct($name, Repository $module = null, Config $config = null, Filesystem $filesystem = null, Console $console = null) {
        $this->name = $name;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->module = $module;
    }


    public function setPlain($plain)
    {
        $this->plain = $plain;
        return $this;
    }
    public function getName()
    {
        return Str::studly($this->name);
    }
    //--------------------------------------------------------
    public function getConfig()
    {
        return $this->config;
    }
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }
    //--------------------------------------------------------
    public function getFilesystem()
    {
        return $this->filesystem;
    }
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }
    //--------------------------------------------------------
    public function getConsole()
    {
        return $this->console;
    }
    public function setConsole($console)
    {
        $this->console = $console;
        return $this;
    }
    //--------------------------------------------------------
    public function getModule()
    {
        return $this->module;
    }
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    //--------------------------------------------------------
    public function getPathsGenerator()
    {
        return $this->pathsGenerator;
    }

    public function setPathsGenerator($pathsGenerator)
    {
        $this->pathsGenerator = $pathsGenerator;
        return $this;
    }
    //--------------------------------------------------------

    public function getFolders()
    {
        return array_values($this->getPathsGenerator());
    }

    public function generateFolders()
    {
        foreach ($this->getFolders() as $folder) {
            $path = $this->module->getModulePath($this->getName()).'/'.$folder;
            $this->filesystem->makeDirectory($path, 0755, true);
            $this->generateGitKeep($path);
        }
    }

    public function generateGitKeep($path)
    {
        $this->filesystem->put($path.'/.gitkeep', '');
    }

    public function getFiles()
    {
        return $this->stubs_files;
    }

    public function generateFiles()
    {
        foreach ($this->getFiles() as $stub => $file) {
            $path = $this->module->getModulePath($this->getName()).$file;

            if (!$this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }
            $this->filesystem->put($path, $this->getStubContents($stub));
            $this->console->info("Created : {$path}");
        }
    }

    //--------------------------------------------------------
    public function setForce($force)
    {
        $this->force = $force;
        return $this;
    }

    public function generate()
    {
        $name = $this->getName();
        if ($this->module->has($name)) {
            if ($this->force) {
                $this->module->delete($name);
            } else {
                $this->console->error("Mazhool [{$name}] already exist!");
                return;
            }
        }
        $this->generateFolders();
        $this->generateFiles();
        if (!$this->plain) {
            $this->generateResources();
        }
        $this->console->info("Mazhool [{$name}] created successfully.");
    }

    public function generateResources()
    {
        $this->console->call('awat:mazhool-create-seed', [
            'name' => $this->getName(),
            'mazhool' => $this->getName(),
            '--master' => true,
        ]);
        $this->console->call('awat:mazhool-create-provider', [
            'name' => $this->getName().'ServiceProvider',
            'mazhool' => $this->getName(),
            '--master' => true,
        ]);
        $this->console->call('awat:mazhool-create-controller', [
            'name' => $this->getName().'Controller',
            'mazhool' => $this->getName(),
        ]);
    }
    //--------------------------------------------------------

    protected function getStubContents($stub)
    {
        return (new Stub('/'.$stub.'.stub', $this->getReplacement($stub)))->render();
    }

    //--------------------------------------------------------
    public function getReplacements()
    {
        return $this->stubs_replacements;
    }

    protected function getReplacement($stub)
    {
        $replacements = $this->getReplacements();
        $namespace = $this->module->config('namespace');
        if (!isset($replacements[$stub])) {
            return [];
        }
        $keys = $replacements[$stub];
        $replaces = [];
        foreach ($keys as $key) {
            if (method_exists($this, $method = 'get'.ucfirst(studly_case(strtolower($key))).'Replacement')) {
                $replaces[$key] = call_user_func([$this, $method]);
            } else {
                $replaces[$key] = null;
            }
        }
        return $replaces;
    }

    //--------------------------------------------------------

    protected function getLowerNameReplacement()
    {
        return strtolower($this->getName());
    }

    protected function getStudlyNameReplacement()
    {
        return $this->getName();
    }

    protected function getVendorReplacement()
    {
        return $this->module->config('composer.vendor');
    }

    protected function getModuleNamespaceReplacement()
    {
        return str_replace('\\', '\\\\', $this->module->config('namespace'));
    }

    protected function getAuthorNameReplacement()
    {
        return $this->module->config('composer.author.name');
    }

    protected function getAuthorEmailReplacement()
    {
        return $this->module->config('composer.author.email');
    }
}
