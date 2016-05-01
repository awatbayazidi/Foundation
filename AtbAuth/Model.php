<?php namespace AwatBayazidi\Foundation\AtbAuth;


use AwatBayazidi\Foundation\Model\Model as BaseModel;


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

        if ($connection = config('atbauth.database.connection')) {
            $this->setConnection($connection);
        }
    }


}
