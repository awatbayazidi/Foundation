<?php namespace AwatBayazidi\Foundation\AtbAuth;

use AwatBayazidi\Contracts\Support\Command as BaseCommand;


abstract class Command extends BaseCommand
{

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    protected function displayLogo()
    {
        // LOGO
        $this->line('awat bayazidi');

        // Copyright
        $this->comment('Version 1.0.0 - Created by AwatBayazidi');
        $this->line('');
    }
}
