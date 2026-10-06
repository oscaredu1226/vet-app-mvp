<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                background: linear-gradient(135deg, #2980b9 0%, #1a5276 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            
            .login-container {
                width: 100%;
                max-width: 450px;
                padding: 20px;
            }
            
            .login-card {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                overflow: hidden;
            }
            
            .login-header {
                background: linear-gradient(135deg, #2980b9, #1a5276);
                padding: 40px 30px;
                text-align: center;
                color: white;
            }
            
            .login-header i {
                font-size: 4rem;
                margin-bottom: 15px;
                opacity: 0.9;
            }
            
            .login-header h2 {
                margin: 0;
                font-size: 1.8rem;
                font-weight: 600;
            }
            
            .login-header p {
                margin: 10px 0 0 0;
                opacity: 0.9;
                font-size: 0.95rem;
            }
            
            .login-body {
                padding: 40px 30px;
            }
            
            .form-group {
                margin-bottom: 25px;
            }
            
            .form-label {
                display: block;
                margin-bottom: 8px;
                color: #34495e;
                font-weight: 500;
                font-size: 0.95rem;
            }
            
            .form-control {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e9ecef;
                border-radius: 10px;
                font-size: 1rem;
                transition: all 0.3s;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #2980b9;
                box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.1);
            }
            
            .form-check {
                display: flex;
                align-items: center;
                margin: 20px 0;
            }
            
            .form-check-input {
                width: 18px;
                height: 18px;
                margin-right: 8px;
                cursor: pointer;
            }
            
            .form-check-label {
                color: #6c757d;
                font-size: 0.9rem;
                cursor: pointer;
            }
            
            .btn-primary {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #2980b9, #1a5276);
                border: none;
                border-radius: 10px;
                color: white;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                margin-top: 10px;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(41, 128, 185, 0.4);
            }
            
            .forgot-password {
                text-align: center;
                margin-top: 20px;
            }
            
            .forgot-password a {
                color: #2980b9;
                text-decoration: none;
                font-size: 0.9rem;
                transition: all 0.3s;
            }
            
            .forgot-password a:hover {
                color: #1a5276;
                text-decoration: underline;
            }
            
            .alert {
                padding: 12px 15px;
                border-radius: 10px;
                margin-bottom: 20px;
                font-size: 0.9rem;
            }
            
            .alert-success {
                background-color: #d1eddb;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            
            .alert-danger {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            
            .invalid-feedback {
                color: #e74c3c;
                font-size: 0.85rem;
                margin-top: 5px;
                display: block;
            }
            
            .form-control.is-invalid {
                border-color: #e74c3c;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            {{ $slot }}
        </div>
    </body>
</html>
