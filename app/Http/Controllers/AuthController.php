<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Merchants\FindOrCreateUser;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login', [
            'facebookEnabled' => (bool) Setting::get('facebook_login_enabled', false),
        ]);
    }

    public function redirect(string $provider): SymfonyRedirect
    {
        $this->guard($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, FindOrCreateUser $action): RedirectResponse
    {
        $this->guard($provider);

        try {
            $social = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('login')->with('notice', 'مقدرناش نكمل تسجيل الدخول. جرّب تاني.');
        }

        $user = $action->handle($provider, (string) $social->getId(), $social->getEmail(), $social->getName(), $social->getAvatar());

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect($user->merchant()->exists() ? '/dashboard' : '/onboarding');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function guard(string $provider): void
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);
        abort_if($provider === 'facebook' && ! Setting::get('facebook_login_enabled', false), 404);
    }
}
