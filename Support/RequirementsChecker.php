<?php

namespace AwatBayazidi\Foundation\Support;

use Illuminate\Support\Facades\Artisan;

class RequirementsChecker
{

    /**
     * Check for the server requirements.
     *
     * @param array $requirements
     * @return array
     */
    public function check(array $requirements)
    {
        Artisan::call('key:generate');
        $results = [];
        // PHP version check
        if (version_compare(PHP_VERSION, config('atbauth.installer.php-version')) > 0) {
            $results['requirements']['PHP'] = true;
        }else{
            $results['requirements']['PHP'] = false;
            $results['errors'] = true;
        }
        foreach($requirements as $requirement)
        {
            $results['requirements'][$requirement] = true;

            if(!extension_loaded($requirement))
            {
                $results['requirements'][$requirement] = false;
                $results['errors'] = true;
            }
        }

        return $results;
    }
}