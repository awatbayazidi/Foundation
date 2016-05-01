<?php namespace AwatBayazidi\Foundation\Firewall;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
class Firewall
{

    private $ip;
    private $config;
    private $request;




    public function __construct()
    {
        $this->request = app('request');
        $this->config = config('atbauth.firewall');
        $this->setIp(null);
    }

    public function setIp($ip)
    {
        $this->ip = $ip ?: ($this->ip ?: $this->request->getClientIp());
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function isWhited($ip = null, $force = false)
    {
        $ip = $ip ?: $this->getIp();
        if($force){
            return $this->isInWhiteList($ip) && !($this->isInBlackList($ip));
        }
        return $this->isInWhiteList($ip);
    }


    public function isBlacked($ip  = null)
    {
        $ip = $ip ?: $this->getIp();
        return $this->isInBlackList($ip);
    }

    public  function isAllowed($mode = null,$ip  = null,  $force = false)
    {
        $ip = $ip ?: $this->getIp();
        $mode = $mode?:config('atbauth.firewall.mode');
        switch($mode) {
            case 'whitelist':
                return $this->isWhited($ip,$force);
                break;
            case 'blacklist':
                return !($this->isBlacked($ip));
                break;
            default:
                return false;
        }
    }


    public  function isNotAllowed($mode = null,$ip  = null,  $force = false)
    {
        $ip = $ip ?: $this->getIp();
        return !$this->isAllowed($mode,$ip,$force);

    }

    public  function isInWhiteList($ip)
    {
        return $this->check(config('atbauth.firewall.whitelist', []), $ip);
    }


    public  function isInBlackList($ip)
    {
        return $this->check(config('atbauth.firewall.blacklist', []), $ip);
    }

    public  function renderAccessDenied($view = null, $viewParameters = [])
    {
       return is_null($view)? config('atbauth.firewall.message'):View::make($view, $viewParameters);
    }

    private  function check($source, $ip)
    {
        if( count($source) ){
			if( in_array($ip, $source) ){
                return true;
            }
		}
        foreach($source as $pattern) {
            if (Str::is($pattern, $ip)) {
                return true;
            }
        }
        return false;
    }

}