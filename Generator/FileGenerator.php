<?php namespace AwatBayazidi\Foundation\Generator;

use AwatBayazidi\Contracts\Generator\Generator;
use Illuminate\Filesystem\Filesystem;

class FileGenerator extends Generator
{

    protected $path;
    protected $contents;
    protected $filesystem;

    public function __construct($path, $contents, $filesystem = null)
    {
        $this->path = $path;
        $this->contents = $contents;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function setContents($contents)
    {
        $this->contents = $contents;
        return $this;
    }

    public function getFilesystem()
    {
        return $this->filesystem;
    }

    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function generate()
    {
        if (!$this->filesystem->exists($path = $this->getPath())) {
            return $this->filesystem->put($path, $this->getContents());
        }
        throw new \Exception('File already exists!');
    }
}
