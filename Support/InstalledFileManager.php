<?php

namespace AwatBayazidi\Foundation\Support;


class InstalledFileManager
{
    /**
     * Create installed file.
     *
     * @return int
     */
    public function create($file = null,$dir = null)
    {
        $dir1 = is_null($dir)? 'atb' : $dir;
        $file1 = is_null($file)? $dir1.'/installed' : $dir1.'/'.$file;
        if (! is_dir(storage_path($dir1))) {
            mkdir(storage_path($dir1), 0755, true);
        }
        file_put_contents(storage_path($file1), '');
    }

    /**
     * Update installed file.
     *
     * @return int
     */
    public function update($file = null,$dir = null)
    {
        return $this->create($file,$dir);
    }

    public function alreadyInstalled($file)
    {
        return file_exists(storage_path($file));
    }
}