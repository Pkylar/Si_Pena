<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-PENA - Login</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body style="background: none;">
    <div class="login-page">
        <div class="login-left">
            <div class="brand-header">
                <img src="{{ asset('images/fri.png') }}" alt="FRI" class="fri-logo">
                <img src="{{ asset('images/telyu.png') }}" alt="Telkom University" class="telkom-logo">
            </div>
            <h1 class="sipena-title">SI-PENA</h1>
            <img src="{{ asset('images/icon.png') }}" alt="Ilustrasi" class="finance-illustration">
        </div>
        <div class="login-right">
            <div class="login-card">
                <h2>Hi, You</h2>
                <p class="login-subtitle">Silakan masuk ke akun Anda</p>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="/login">
                    @csrf
                    <div class="form-group">
                        <label>Username <span class="required">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <div class="input-password-wrapper">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password', this)">👁</button>
                        </div>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" name="agree" id="agree">
                        <label for="agree" style="margin:0;font-weight:normal;">Saya setuju dengan Syarat dan Kebijakan Privasi</label>
                    </div>
                    <button type="submit" class="btn-login">Login</button>
                </form>
                <div class="signup-link">
                    Belum memiliki akun? <a href="/register">Register</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁';
            }
        }
    </script>
</body>
</html>
