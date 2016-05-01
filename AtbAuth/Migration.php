<?php namespace AwatBayazidi\Foundation\AtbAuth;

use Illuminate\Database\Migrations\Migration as IlluminateMigration;
use Illuminate\Support\Facades\Schema;

/**
 * Class     Migration
 *
 * @package  AwatBayazidi\AtbAuth\Bases
 * @author   AwatBayazidi <AwatBayazidi.maroc@gmail.com>
 */
abstract class Migration extends IlluminateMigration
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The table name.
     *
     * @var string
     */
    protected $table;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make a migration instance.
     */
    public function __construct()
    {
        $this->connection = config('atbauth.database.connection');
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the table name.
     *
     * @param  string  $table
     *
     * @return self
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Run the migrations.
     */
    abstract public function up();

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists($this->table);
    }
}
