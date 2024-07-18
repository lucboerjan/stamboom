<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Instelling
{
    private $_instellingen = [];
    private static $_dit= null;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    private function __construct() {
        try {
            $this->_instellingen = json_decode(file_get_contents(sprintf('%s/%s', storage_path(), 'instelling.json')), true);
        }
        catch(Exception $ex) {

        }
    }

    private function _get($instellingID) {
        $instellingId = strtoupper(trim($instellingID));
        if (isset($this->_instellingen[$instellingID])) return $this->_instellingen[$instellingID];
        else return '';
    }

    private function _set($instellingID, $waarde) {
        $instellingId = strtoupper(trim($instellingID));

        if (empty($instellingID)) return;

        try {
            $this->_instellingen[$instellingID] = $waarde;
            file_put_contents(sprintf('%s/%s', storage_path(), 'instelling.json'), json_encode($this->_instellingen));
        }
        catch(Exception $ex) {

        }
    }

    private function _del($instellingID) {
        $instellingId = strtoupper(trim($instellingID));

        try {
            unset($this->_instellingen[$instellingID]);
            file_put_contents(sprintf('%s/%s', storage_path(), 'instelling.json'), json_encode($this->_instellingen));
        }
        catch(Exception $ex) {

        }
    }

    public static function get($instellingID='') {
        if (!(self::$_dit instanceof self)) {
            self::$_dit = new self();
        }

        return self::$_dit->_get($instellingID);
    }

    public static function set($instellingID='', $waarde='') {
        if (!(self::$_dit instanceof self)) {
            self::$_dit = new self();
        }

        self::$_dit->_set($instellingID, $waarde);
    }

    public static function del($instellingID='') {
        if (!(self::$_dit instanceof self)) {
            self::$_dit = new self();
        }

        self::$_dit->_del($instellingID);
    }
}
