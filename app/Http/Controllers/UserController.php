<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Show the signup form
     */
    public function show()
    {
        return view('landingpage.signup');
    }

    /**
     * Handle the signup form submission
     */
    public function register(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['required', 'accepted'],
            'sector' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'İsim alanı zorunludur.',
            'email.required' => 'E-posta alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.confirmed' => 'Şifre onayı eşleşmiyor.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'terms.required' => 'Kullanım şartlarını kabul etmelisiniz.',
            'terms.accepted' => 'Kullanım şartlarını kabul etmelisiniz.',
        ]);

        try {
            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
                'plan_type' => 'free',
                'token_balance' => 50, // Default token balance
                'sector' => $validated['sector'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);

            // Log the user in
            Auth::login($user);

            // Redirect to dashboard
            return redirect()->route('dashboard')->with('success', 'Hesabınız başarıyla oluşturuldu! Hoş geldiniz.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors([
                'error' => 'Kayıt işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.'
            ]);
        }
    }

    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('landingpage.login');
    }

    /**
     * Handle the login form submission
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'E-posta alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre alanı zorunludur.',
        ]);

        // Check if request is AJAX
        if ($request->ajax()) {
            if (Auth::attempt($validated, $request->boolean('remember'))) {
                $request->session()->regenerate();

                return response()->json([
                    'success' => true,
                    'message' => 'Başarıyla giriş yaptınız!',
                    'redirect' => route('dashboard')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Girdiğiniz bilgiler hatalı.'
            ], 422);
        }

        // Regular form submission
        if (Auth::attempt($validated, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with('success', 'Başarıyla giriş yaptınız!');
        }

        return back()->withInput()->withErrors([
            'email' => 'Girdiğiniz bilgiler hatalı.',
        ]);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Başarıyla çıkış yaptınız.');
    }

    /**
     * Show terms and conditions
     */
    public function terms()
    {
        return view('landingpage.terms');
    }

    /**
     * Show password reset request form
     */
    public function showPasswordRequest()
    {
        return view('landingpage.forgot-password');
    }

    /**
     * Handle password reset request
     */
    public function passwordRequest(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'E-posta alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
        ]);

        // Check if user exists
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu e-posta adresi ile kayıtlı kullanıcı bulunamadı.'
                ], 422);
            }

            return back()->withErrors([
                'email' => 'Bu e-posta adresi ile kayıtlı kullanıcı bulunamadı.'
            ]);
        }

        // Here you would typically send a password reset email
        // For now, we'll just show a success message
        $message = 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi. Lütfen e-postanızı kontrol edin.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }
}
