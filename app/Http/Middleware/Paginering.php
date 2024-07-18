<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Http\Middleware\Instelling;

class Paginering
{
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

    public static function pagineer($pagina, $aantalPaginas) {
        if (!(self::$_dit instanceof self)) {
            self::$_dit = new self();
        }

        $dta = [];

        // geen of één pagina -> lege lijst
        if ($aantalPaginas == 0) return $dta;

        // 2 of meer paginas
        $paginering = Instelling::get('paginering');
        $midden = intdiv($paginering, 2);

        // knop previous
        if ($pagina > 0) $dta[] = [$pagina - 1, '<i class="bi bi-chevron-double-left"></i> vorige', ''];

        // ...
        if ($pagina > $midden) $dta[] = [-1, ' ... ', 'disabled'];

        // paginering
        if ($pagina <= $midden) $start = 0;
        elseif ($pagina >= $aantalPaginas - $midden) $start = $aantalPaginas - $paginering;
        else $start = $pagina - $midden;

        $stop = $start + $paginering - 1;
        if ($stop >= $aantalPaginas) $stop = $aantalPaginas - 1;

        foreach(range($start, $stop) as $paginaNo) {
            $dta[] = [$paginaNo, $paginaNo + 1, $pagina == $paginaNo ? 'active' : ''];
        }

        // ...
        if ($pagina < $aantalPaginas - $midden - 1) $dta[] = [-1, ' ... ', 'disabled'];

        // knop next
        if ($pagina < $aantalPaginas - 1) $dta[] = [$pagina + 1, 'volgende <i class="bi bi-chevron-double-right"></i>', ''];

        return $dta;
    }

    /**
     * pagina($itemNdx, $pagina)
     * berekent de pagina waarop het item staat
     * @param $volgnummer int: volgnummer betreffende item
     * @param $pagina string: bepaalt aantal items per pagina
     * @return pagina int
     */
    public static function pagina($volgnummer, $perPagina) {
        switch($perPagina) {
            case 'rekening':
                $aantalPerPagina = Instelling::get('rekeningen_per_pagina');
                break;
            case 'transactie':
                $aantalPerPagina = Instelling::get('transacties_per_pagina');
                break;
        }

        $pagina = ceil($volgnummer / $aantalPerPagina) - 1;
        if ($pagina < 0) $pagina = 0;

        return $pagina;
    }
}
