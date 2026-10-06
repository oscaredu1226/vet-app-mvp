<x-guest-layout>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-paw"></i>
            <h2>VetApp</h2>
            <p>Sistema de Gestión Veterinaria</p>
        </div>
        
        <div class="login-body">
            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Usuario -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-user me-2"></i>Usuario
                    </label>
                    <input id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           type="text" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           placeholder="admin">
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Contraseña
                    </label>
                    <input id="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="current-password"
                           placeholder="Ingrese su contraseña">
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="form-check">
                    <input id="remember_me" 
                           type="checkbox" 
                           class="form-check-input" 
                           name="remember">
                    <label for="remember_me" class="form-check-label">
                        Recordarme
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </button>

                <!-- Forgot Password -->
                @if (Route::has('password.request'))
                    <div class="forgot-password">
                        <a href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</x-guest-layout>
