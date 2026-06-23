<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-PENA - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <a href="/dashboard" class="navbar-brand">SI-PENA</a>
        <div class="navbar-menu">
            <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="/pengajuan" class="{{ request()->is('pengajuan*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i> Pengajuan Dana
            </a>
            <a href="/grafik/ormawa" class="{{ request()->is('grafik*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Grafik Pengajuan Dana
            </a>
            <a href="/report" class="{{ request()->is('report*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i> Report
            </a>
        </div>
        <div class="navbar-right">
            <span class="user-badge">
                @php
                    $u = auth()->user();
                    $badges = [
                        'keuangan' => 'Keu / Keuangan FRI',
                        'kaur_keuangan' => 'Kaur Keuangan / Kaur Keuangan FRI',
                        'ormawa' => ($u->organization_name ?? 'Ormawa') . ' / Ormawa ' . ($u->organization_name ?? ''),
                        'kemahasiswaan' => 'Kem / Kemahasiswaan FRI',
                        'kaur_kemahasiswaan' => 'Kaur Kemahasiswaan / Kaur Kemahasiswaan FRI',
                        'wd2' => 'WD2 / Wakil Dekan 2',
                        'mahasiswa' => $u->name . ' / Mahasiswa',
                    ];
                @endphp
                {{ $badges[$u->role] ?? $u->name }}
            </span>
            <button class="btn-logout" onclick="document.getElementById('logoutModal').classList.add('active')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </nav>

    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </div>

    <div class="modal-overlay" id="logoutModal">
        <div class="modal-box">
            <h3>Yakin ingin keluar?</h3>
            <p>Anda akan diarahkan ke halaman login</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="document.getElementById('logoutModal').classList.remove('active')">Batal</button>
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" class="btn-confirm">Keluar</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                if (btn && !btn.classList.contains('btn-confirm') && !btn.classList.contains('btn-cancel')) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                }
                this.querySelectorAll('.input-rupiah').forEach(input => {
                    input.value = input.value.replace(/\./g, '');
                });
            });
        });

        document.querySelectorAll('.input-rupiah').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
