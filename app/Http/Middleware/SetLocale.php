<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Desteklenen diller
        $supportedLocales = ['en', 'tr', 'ar', 'fr'];
        
        // Session'dan dil ayarını kontrol et
        $sessionLocale = Session::get('locale');
        if ($sessionLocale && in_array($sessionLocale, $supportedLocales)) {
            App::setLocale($sessionLocale);
        } else {
            // Browser'ın dil ayarını al
            $browserLocale = $request->getPreferredLanguage($supportedLocales);
            
            if ($browserLocale) {
                App::setLocale($browserLocale);
                Session::put('locale', $browserLocale);
            } else {
                // Varsayılan dil
                App::setLocale('en');
                Session::put('locale', 'en');
            }
        }
        
        return $next($request);
    }
}