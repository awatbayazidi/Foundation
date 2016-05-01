<?php

namespace AwatBayazidi\Foundation\Mazhool;
use AwatBayazidi\Mazhool\Mazhool;

class Migrator
{
    protected $module;
    protected $laravel;

    public function __construct(Mazhool $module)
    {
        $this->module = $module;
        $this->laravel = $module->getLaravel();
    }

    public function getPath()
    {
        return $this->module->getExtraPath(
            $this->laravel['mazhool']->getPathGenerator('migration')
        );
    }

    public function getMigrations($reverse = false)
    {
        $files = $this->laravel['files']->glob($this->getPath().'/*_*.php');
        // Once we have the array of files in the directory we will just remove the
        // extension and take the basename of the file which is all we need when
        // finding the migrations that haven't been run against the databases.
        if ($files === false) {
            return array();
        }
        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));

        }, $files);
        // Once we have all of the formatted file names we will sort them and since
        // they all start with a timestamp this should give us the migrations in
        // the order they were actually created by the application developers.
        sort($files);

        if ($reverse) {
            return array_reverse($files);
        }

        return $files;
    }


    public function rollback()
    {
        $migrations = $this->getLast($this->getMigrations(true));
        $this->requireFiles($migrations->toArray());
        $migrated = [];
        foreach ($migrations as $migration) {
            $data = $this->find($migration);
            if ($data->count()) {
                $migrated[] = $migration;
                $this->down($migration);
                $data->delete();
            }
        }
        return $migrated;
    }


    public function reset()
    {
        $migrations = $this->getMigrations(true);
        $this->requireFiles($migrations);
        $migrated = [];
        foreach ($migrations as $migration) {
            $data = $this->find($migration);
            if ($data->count()) {
                $migrated[] = $migration;
                $this->down($migration);
                $data->delete();
            }
        }
        return $migrated;
    }


    public function down($migration)
    {
        $this->resolve($migration)->down();
    }

    public function up($migration)
    {
        $this->resolve($migration)->up();
    }

    public function resolve($file)
    {
        $file = implode('_', array_slice(explode('_', $file), 4));
        $class = studly_case($file);
        return new $class();
    }


    public function requireFiles(array $files)
    {
        $path = $this->getPath();
        foreach ($files as $file) {
            $this->laravel['files']->requireOnce($path.'/'.$file.'.php');
        }
    }


    public function table()
    {
        return $this->laravel['db']->table(config('database.migrations'));
    }


    public function find($migration)
    {
        return $this->table()->whereMigration($migration);
    }

    public function log($migration)
    {
        return $this->table()->insert([
            'migration' => $migration,
            'batch' => $this->getNextBatchNumber(),
        ]);
    }

    public function getNextBatchNumber()
    {
        return $this->getLastBatchNumber() + 1;
    }


    public function getLastBatchNumber($migrations)
    {
        return $this->table()
            ->whereIn('migration', $migrations)
            ->max('batch');
    }


    public function getLast($migrations)
    {
        $query = $this->table()
            ->where('batch', $this->getLastBatchNumber($migrations))
            ->whereIn('migration', $migrations);
        $result = $query->orderBy('migration', 'desc')->get();
        return collect($result)->map(function ($item) {
            return (array) $item;
        })->lists('migration');
    }

    public function getRan()
    {
        return $this->table()->lists('migration');
    }
}
