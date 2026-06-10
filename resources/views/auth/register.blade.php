<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-PENA - Register</title>
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
                <h2>Register</h2>
                <p class="login-subtitle">Buat akun baru untuk mulai mengajukan dana</p>
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="/register">
                    @csrf
                    <div class="form-group">
                        <label>Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Username <span class="required">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <div class="input-password-wrapper">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 8 karakter" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password', this)">👁</button>
                        </div>
                        <small class="field-hint">Gunakan kombinasi huruf, angka, dan simbol</small>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password <span class="required">*</span></label>
                        <div class="input-password-wrapper">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', this)">👁</button>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">Register</button>
                </form>
                <div class="signup-link">
                    Sudah punya akun? <a href="/login">Login</a>
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
