<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        
        // Intentar login por usuario o email
        $loginSuccess = Auth::attempt(['usuario' => $credentials['email'], 'password' => $credentials['password']], $this->boolean('remember')) ||
                       Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $this->boolean('remember'));

        if (! $loginSuccess) {
            RateLimiter::hit($this->throttleKey(), 60); 
            
            // Verificar si se alcanzó el límite después de incrementar
            $attempts = RateLimiter::attempts($this->throttleKey());
            
            if ($attempts >= 3) {
                event(new Lockout($this));
                
                throw ValidationException::withMessages([
                    'email' => 'Cuenta bloqueada por seguridad. Contacte al administrador para desbloquear su acceso.',
                ]);
            }

            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas son incorrectas. Intento ' . $attempts . ' de 3.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'email' => 'Cuenta bloqueada por seguridad. Contacte al administrador para desbloquear su acceso.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
