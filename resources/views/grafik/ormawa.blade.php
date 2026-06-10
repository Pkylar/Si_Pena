@extends('layouts.app')
@section('title', 'Grafik Pengajuan Dana Ormawa')

@section('content')
<div class="page-header">
    <h1>Grafik Pengajuan Dana</h1>
</div>

<div style="margin-bottom:20px;">
    <a href="/grafik/ormawa" class="btn btn-primary">Organisasi Mahasiswa</a>
    <a href="/grafik/lomba" class="btn {{ request()->is('grafik/lomba') ? 'btn-primary' : '' }}" style="{{ request()->is('grafik/ormawa') ? '' : 'background:#ddd;color:#333;' }}">Lomba</a>
</div>

<div class="card">
    <div class="card-header">
        <span>Grafik Pengajuan Dana Organisasi Mahasiswa</span>
        @if(auth()->user()->role === 'wd2')
            <button class="btn btn-warning btn-sm" onclick="document.getElementById('budgetModal').classList.add('active')">
                <i class="fas fa-edit"></i> Ubah Dana
            </button>
        @endif
    </div>
    <div class="card-body">
        <div class="grafik-header">
            <div class="dana-info">
                <div class="dana-info-item">
                    <div class="label">Total Dana Ormawa FRI</div>
                    <div class="value">Rp. {{ number_format($totalDana, 0, ',', '.') }}</div>
                </div>
                <div class="dana-info-item">
                    <div class="label">Sisa Dana Ormawa FRI</div>
                    <div class="value">Rp. {{ number_format($sisaDana, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="ormawaChart" height="100"></canvas>
        </div>
    </div>
</div>

@if(auth()->user()->role === 'wd2')
<div class="modal-overlay" id="budgetModal">
    <div class="modal-box">
        <h3>Ubah Dana?</h3>
        <form method="POST" id="budgetForm">
            @csrf
            @method('PATCH')
            <div style="margin-bottom:15px;text-align:left;">
                <label style="color:#cbd5e0;font-size:13px;display:block;margin-bottom:5px;">Pilih Unit</label>
                <select id="budgetSelect" class="form-control" onchange="updateBudgetAction()" style="background:#4a5568;color:#fff;border:none;">
                    @foreach($budgets->where('triwulan', 4) as $b)
                        <option value="{{ $b->id }}" data-total="{{ $b->total_dana }}">{{ $b->nama_unit }} - Triwulan {{ $b->triwulan }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:20px;text-align:left;">
                <label style="color:#cbd5e0;font-size:13px;display:block;margin-bottom:5px;">Nominal Dana</label>
                <input type="number" name="total_dana" id="budgetNominal" class="form-control" style="background:#4a5568;color:#fff;border:none;" min="0">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="document.getElementById('budgetModal').classList.remove('active')">Batal</button>
                <button type="submit" class="btn-confirm" style="background:#48bb78;">Iya</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
const chartData = @json($chartData);
const units = @json($units);
const colors = ['#1a3c34', '#5ba4cf', '#28a745', '#ffc107', '#e57373'];

const datasets = [];
for (let t = 1; t <= 4; t++) {
    datasets.push({
        label: 'Triwulan ' + t,
        data: units.map(u => chartData[u] ? chartData[u][t] : 0),
        backgroundColor: colors[t-1],
    });
}

new Chart(document.getElementById('ormawaChart'), {
    type: 'bar',
    data: { labels: units, datasets: datasets },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true, suggestedMax: 10000000, ticks: { stepSize: 1000000, callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); } } } },
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': Rp ' + ctx.raw.toLocaleString('id-ID'); } } }
        }
    }
});

function updateBudgetAction() {
    const sel = document.getElementById('budgetSelect');
    const id = sel.value;
    const total = sel.options[sel.selectedIndex].dataset.total;
    document.getElementById('budgetForm').action = '/budget/' + id;
    document.getElementById('budgetNominal').value = total;
}
updateBudgetAction();
</script>
@endpush
@endsection
