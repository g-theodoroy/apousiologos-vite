<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdministrator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! request()->user()->permissions['admin']) return abort(403, "ΔΕΝ ΕΧΕΤΕ ΔΙΚΑΙΩΜΑ ΠΡΟΣΒΑΣΗΣ");
        return $next($request);
    }
}
