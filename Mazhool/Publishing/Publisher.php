<?php

namespace AwatBayazidi\Foundation\Mazhool\Publishing;

use Illuminate\Console\Command;
use AwatBayazidi\Contracts\Mazhool\PublisherInterface;
use AwatBayazidi\Mazhool\Mazhool;
use AwatBayazidi\Mazhool\Repository;

abstract class Publisher implements PublisherInterface
{

    protected $module;
    protected $repository;
    protected $console;
    protected $success;
    protected $error = '';
    protected $showMessage = true;

    public function __construct(Mazhool $module)
    {
        $this->module = $module;
    }

    public function showMessage()
    {
        $this->showMessage = true;
        return $this;
    }

    public function hideMessage()
    {
        $this->showMessage = false;
        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }


    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setConsole(Command $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function getFilesystem()
    {
        return $this->repository->getFiles();
    }


    abstract public function getDestinationPath();
    abstract public function getSourcePath();


    public function publish()
    {
        if (!$this->console instanceof Command) {
            $message = "The 'console' property must instance of \\Illuminate\\Console\\Command.";
            throw new \RuntimeException($message);
        }

        if (!$this->getFilesystem()->isDirectory($sourcePath = $this->getSourcePath())) {
            return;
        }

        if (!$this->getFilesystem()->isDirectory($destinationPath = $this->getDestinationPath())) {
            $this->getFilesystem()->makeDirectory($destinationPath, 0775, true);
        }

        if ($this->getFilesystem()->copyDirectory($sourcePath, $destinationPath)) {
            if ($this->showMessage == true) {
                $this->console->line("<info>Published</info>: {$this->module->getStudlyName()}");
            }
        } else {
            $this->console->error($this->error);
        }
    }
}
