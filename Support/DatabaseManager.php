<?php

namespace AwatBayazidi\Foundation\Support;

use Exception;
use Illuminate\Support\Facades\Artisan;

class DatabaseManager
{

    public function migrateAndSeed()
    {
        return $this->migrate();
    }

    private function migrate()
    {
        try{
            Artisan::call('migrate');
        }
        catch(Exception $e){
            return $this->response($e->getMessage());
        }
        return $this->seed();
    }


    private function seed()
    {
        try{
            Artisan::call('db:seed');
        }
        catch(Exception $e){
            return $this->response($e->getMessage());
        }
        return $this->response(trans('atbauth::messages.final.finished'), 'success');
    }

    private function response($message, $status = 'danger')
    {
        return array(
            'status' => $status,
            'message' => $message
        );
    }
}