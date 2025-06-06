<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Where to redirect users after registration.
     *
     * @return string
     */
    protected function redirectTo(): string
    {
        if (Gate::allows('access-admin-dashboard')) {
            return route('admin.dashboard');
        }

        if (Gate::allows('access-user-dashboard')) {
            return route('user.dashboard');
        }

        Auth::logout();
        return route('login');
    }

    /**
     * Show the application registration form.
     *
     * @return Response|RedirectResponse
     */
    public function index(): Response|RedirectResponse
    {
        if (config('settings.register.enabled' == false)) {
            return redirect()->back()->with('error', 'Registration disabled. Please contact the administrator.');
        }

        return Inertia::render('Auth/Register', [
            'social_login' => config('settings.login.social_enabled'),
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function register(Request $request): RedirectResponse
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        Auth::login($user);

        if (config('settings.email_notification')){
            Mail::mailer(config('settings.email_provider'))->to($user->email)->send(new \App\Mail\Registered($user));
        }

        return redirect()->intended($this->redirectTo());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', Password::defaults()]
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User
     * @throws Exception
     */
    protected function create(array $data): User
    {
        try {
            return User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'admin'
            ]);
        } catch (Exception $e) {
            Log::error('User Registration Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
