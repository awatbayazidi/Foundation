<?php

namespace AwatBayazidi\Foundation\Support;

use AwatBayazidi\Foundation\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Uploader
{

    protected $imagine;
    protected $library;

    public $results = [];
    public $dimensions = [
        'square50' => array(50, 50, true),
        'square100' => array(100, 100, true),
        'square200' => array(200, 200, true),
        'square400' => array(400, 400, true),

        'size50' => array(50, 50, false),
        'size100' => array(100, 100, false),
        'size200' => array(200, 200, false),
        'size400' => array(400, 400, false),
    ];
    public function __construct()
    {
        if (!$this->imagine) {
            /*
             * Library used to manipulate image.
             *
             * Options: gd (default), imagick, gmagick
             */
            $this->library = config('uploader.library', 'gd');
            /*
             * Quality for JPEG type.
             *
             * Scale: 1-100;
             */
            $this->quality = config('uploader.quality', 90);
            /*
             * Upload directory.
             *
             * Default: public/uploads/images
             */
            $this->uploadpath = config('uploader.path', public_path() . '/uploads/images');
             /*
              * Use original name. If set to false, will use hashed name.
              *
              * Options:
              *     - original (default): use original filename in "slugged" name
              *     - hash: use filename hash as new file name
              *     - random: use random generated new file name
              *     - timestamp: use uploaded timestamp as filename
              *     - custom: user must provide new name, if not will use original filename
              */
            $this->newfilename = config('uploader.newfilename', 'original');
            /*
             * Sizes, used to crop and create multiple size.
             *
             * array(width, height, square, quality), if square set to TRUE, image will be in square
             */
            $this->dimensions = config('uploader.dimensions',[
                                                        'square50' => [50, 50, true],
                                                        'square100' => [100, 100, true],
                                                        'square200' => [200, 200, true],
                                                        'square400' => [400, 400, true],

                                                        'size50' => [50, 50, false],
                                                        'size100' => [100, 100, false],
                                                        'size200' => [200, 200, false],
                                                        'size400' => [400, 400, false],
                                                   ]);
            /*
             * Dimension identifier. If TRUE will use dimension name as suffix, if FALSE use directory.
             *
             * Example:
             *     - TRUE (default): newname_square50.png, newname_size100.jpg
             *     - FALSE: square50/newname.png, size100/newname.jpg
             */
            $this->suffix = config('uploader.suffix', true);
            /*
            |--------------------------------------------------------------------------
            | Max File Size
            |--------------------------------------------------------------------------
            |
            | Make sure you give a limitation of file size uploaded (in KiloBytes).
            |
            */
            $this->max_size = 10240;



            // Now create the instance
            if ($this->library == 'imagick') $this->imagine = new \Imagine\Imagick\Imagine();
            elseif ($this->library == 'gmagick') $this->imagine = new \Imagine\Gmagick\Imagine();
            elseif ($this->library == 'gd') $this->imagine = new \Imagine\Gd\Imagine();
            else                                 $this->imagine = new \Imagine\Gd\Imagine();
        }
    }

    private function checkPathIsOk($path, $dir = null)
    {
        $path = rtrim($path, '/') . ($dir ? '/' . trim($dir, '/') : '');

        if (File::isDirectory($path) && File::isWritable($path)) {
            return true;
        } else {
            try {
                @File::makeDirectory($path, 0777, true);
                return true;
            } catch (\Exception $e) {
                Log::error('Uploader: ' . $e->getMessage());
                $this->results['error'] = $e->getMessage();
                return false;
            }
        }
    }

    public function upload($filesource, $newfilename = null, $dir = null)
    {
        $isPathOk = $this->checkPathIsOk($this->uploadpath, $dir);

        if ($isPathOk and $filesource) {
            $this->results['path'] = rtrim($this->uploadpath, '/') . ($dir ? '/' . trim($dir, '/') : '');
            $this->results['dir'] = str_replace(public_path() . '/', '', $this->results['path']);
            $this->results['original_filename'] = $filesource->getClientOriginalName();
            $this->results['original_filepath'] = $filesource->getRealPath();
            $this->results['original_extension'] = $filesource->getClientOriginalExtension();
            $this->results['original_filesize'] = $filesource->getSize();
            $this->results['original_mime'] = $filesource->getMimeType();

            switch ($this->newfilename) {
                case 'hash':
                    $this->results['filename'] = md5($this->results['original_filename'] . '.' . $this->results['original_extension'] . strtotime('now')) . '.' . $this->results['original_extension'];
                    break;
                case 'random':
                    $this->results['filename'] = Str::random() . '.' . $this->results['original_extension'];
                    break;
                case 'timestamp':
                    $this->results['filename'] = strtotime('now') . '.' . $this->results['original_extension'];
                    break;
                case 'custom':
                    $this->results['filename'] = (!empty($newfilename) ? $newfilename . '.' . $this->results['original_extension'] : $this->results['original_filename'] . '.' . $this->results['original_extension']);
                    break;
                default:
                    $this->results['filename'] = $this->results['original_filename'];
            }

            $uploaded = $filesource->move($this->results['path'], $this->results['filename']);
            if ($uploaded) {
                $this->results['original_filepath'] = rtrim($this->results['path']) . '/' . $this->results['filename'];
                $this->results['original_filedir'] = str_replace(public_path() . '/', '', $this->results['original_filepath']);
                $this->results['basename'] = pathinfo($this->results['original_filepath'], PATHINFO_FILENAME);

                list($width, $height) = getimagesize($this->results['original_filepath']);
                $this->results['original_width'] = $width;
                $this->results['original_height'] = $height;

                $this->createDimensions($this->results['original_filepath']);
            } else {
                $this->results['error'] = 'File ' . $this->results['original_filename '] . ' is not uploaded.';
                Log::error('Imageupload: ' . $this->results['error']);
            }
        }

        return new Collection($this->results);
    }

    protected function createDimensions($filesource)
    {
        if (!empty($this->dimensions) && is_array($this->dimensions)) {
            foreach ($this->dimensions as $name => $dimension) {
                $width = (int)$dimension[0];
                $height = isset($dimension[1]) ? (int)$dimension[1] : $width;
                $crop = isset($dimension[2]) ? (bool)$dimension[2] : false;

                $this->resize($filesource, $name, $width, $height, $crop);
            }
        }
    }

    public function url($filename, $dir = null)
    {
        $this->results['filename'] = $filename;
        $this->results['path'] = rtrim($this->uploadpath, '/') . ($dir ? '/' . trim($dir, '/') : '');
        $this->results['dir'] = str_replace(public_path() . '/', '', $this->results['path']);
        $this->results['filename'] = $filename;
        $this->results['filepath'] = rtrim($this->results['path']) . '/' . $this->results['filename'];
        if(File::exists($this->results['filepath'])){
            $this->results['filedir'] = str_replace(public_path() . '/', '', $this->results['filepath']);
            $this->results['basename'] = File::name($this->results['filepath']);
            $this->results['filesize'] = File::size($this->results['filepath']);
            $this->results['extension'] = File::extension($this->results['filepath']);
            $this->results['mime'] = File::mimeType($this->results['filepath']);
            if ($this->isImage($this->results['mime'])) {
                list($width, $height) = getimagesize($this->results['filepath']);
                $this->results['width'] = $width;
                $this->results['height'] = $height;
                if (!empty($this->dimensions) && is_array($this->dimensions)) {
                    foreach ($this->dimensions as $name => $dimension) {
                        $suffix = trim($name);

                        $path = $this->results['path'] . ($this->suffix == false ? '/' . trim($suffix, '/') : '');
                        $name = $this->results['basename'] . ($this->suffix == true ? '_' . trim($suffix, '/') : '') . '.' . $this->results['extension'];

                        $pathname = $path . '/' . $name;
                        if(File::exists($pathname)) {
                            list($nwidth, $nheight) = getimagesize($pathname);
                            $filesize = File::size($pathname);
                            $this->results['dimensions'][$suffix] = [
                                'path' => $path,
                                'dir' => str_replace(public_path() . '/', '', $path),
                                'filename' => $name,
                                'filepath' => $pathname,
                                'filedir' => str_replace(public_path() . '/', '', $pathname),
                                'width' => $nwidth,
                                'height' => $nheight,
                                'filesize' => $filesize,
                            ];
                        }else{
                            $this->results['dimensions'][$suffix] = [
                                'path' => $path,
                                'dir' => str_replace(public_path() . '/', '', $path),
                                'filename' => $name,
                                'filepath' => '#',
                                'filedir' => '#',
                            ];
                        }
                    }
                }
            }
            return new Collection($this->results);
        };
        return null;

    }

    public function isImage($type)
    {
        if (in_array($type, ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/bmp'])) {
            return true;
        }
        return false;
    }

    private function resize($filesource, $suffix, $width, $height, $crop)
    {
        if (!$height) $height = $width;

        $suffix = trim($suffix);

        $path = $this->results['path'] . ($this->suffix == false ? '/' . trim($suffix, '/') : '');
        $name = $this->results['basename'] . ($this->suffix == true ? '_' . trim($suffix, '/') : '') . '.' . $this->results['original_extension'];

        $pathname = $path . '/' . $name;

        try {
            $isPathOk = $this->checkPathIsOk($this->results['path'], ($this->suffix == false ? $suffix : ''));

            if ($isPathOk) {
                $size = new \Imagine\Image\Box($width, $height);
                $mode = $crop ? \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND : \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
                $newfile = $this->imagine->open($filesource)->thumbnail($size, $mode)->save($pathname, ['quality' => $this->quality]);

                list($nwidth, $nheight) = getimagesize($pathname);
                $filesize = filesize($pathname);

                $this->results['dimensions'][$suffix] = [
                    'path' => $path,
                    'dir' => str_replace(public_path() . '/', '', $path),
                    'filename' => $name,
                    'filepath' => $pathname,
                    'filedir' => str_replace(public_path() . '/', '', $pathname),
                    'width' => $nwidth,
                    'height' => $nheight,
                    'filesize' => $filesize,
                ];
            }
        } catch (\Exception $e) {

        }
    }
}