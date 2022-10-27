<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowAnathesi
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
        // έλεγχος κατά την καταχώριση βαθμολογίας
        // αν ο καθηγητής έχει την ανάθεση
        if (Auth::user()->role_id == 1) return $next($request);
        if (Auth::user()->role_id == 3) return abort(403);

        $selectedAnathesiId = request()->selectedAnathesiId;
        $anatheseis = Auth::user()->anatheseis();
        // αν το τμήμα που δόθηκε στο url δεν αντιστοιχεί στον χρήστη επιστρέφω πίσω
        if ($selectedAnathesiId && !$anatheseis->where('id', $selectedAnathesiId)->count()) return abort(403);

        return $next($request);
    }
}
