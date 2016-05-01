<?php namespace AwatBayazidi\Foundation\Payam;

use Illuminate\Session\Store;
use AwatBayazidi\Contracts\Payam\SessionStore;

class PayamSessionStore implements SessionStore {

    private $session;

    function __construct(Store $session)
    {
        $this->session = $session;
    }

    public function flash($name, $data)
    {
        $this->session->flash($name, $data);
    }

    public function forget($keys)
    {
        $this->session->forget($keys);
    }

    public function put($key, $value = null)
    {
        $this->session->put($key, $value);
    }

}