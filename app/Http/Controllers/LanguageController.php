<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Change the application locale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeLanguage(Request $request, $locale)
    {
        // Desteklenen diller
        $supportedLocales = ['en', 'tr', 'ar', 'fr'];
        
        if (in_array($locale, $supportedLocales)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
        }
        
        // Önceki sayfaya geri dön
        return redirect()->back();
    }
    
    /**
     * Get current locale.
     *
     * @return string
     */
    public function getCurrentLocale()
    {
        return App::getLocale();
    }
    
    /**
     * Get supported locales.
     *
     * @return array
     */
    public function getSupportedLocales()
    {
        return [
            'en' => 'English',
            'tr' => 'Türkçe',
            'ar' => 'العربية',
            'fr' => 'Français',
        ];
    }
    
    /**
     * Reset to browser default language.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetToBrowserDefault(Request $request)
    {
        // Session'dan dil ayarını temizle
        Session::forget('locale');
        
        // Browser'ın dil ayarını al
        $supportedLocales = ['en', 'tr', 'ar', 'fr'];
        $browserLocale = $request->getPreferredLanguage($supportedLocales);
        
        if ($browserLocale) {
            App::setLocale($browserLocale);
        } else {
            App::setLocale('en');
        }
        
        return redirect()->back();
    }
}