<?php namespace AwatBayazidi\Foundation\Model;

use AwatBayazidi\Abzar\Collection;
use AwatBayazidi\Foundation\Support\Uploader;
use Illuminate\Database\Eloquent\Model as BaseModel;


abstract class Model extends BaseModel
{
    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     */

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($connection = config('database.default')) {
            $this->setConnection($connection);
        }
    }

}
