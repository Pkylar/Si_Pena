@extends('layouts.app')
@section('title', 'Grafik Pengajuan Dana Lomba')

@section('content')
<div class="page-header">
    <h1>Grafik Pengajuan Dana</h1>
</div>

<div style="margin-bottom:20px;">
    <a href="/grafik/ormawa" class="btn" style="background:#ddd;color:#333;">Organisasi Mahasiswa</a>
    <a href="/grafik/lomba" class="btn btn-primary">Lomba</a>
</div>

<div class="card">
    <div class="card-header">
        <span>Grafik Pengajuan Dana Lomba</span>
        @if(auth()->user()->role === 'wd2')
            <button class="btn btn-warning btn-sm" onclick="document.getElementById('budgetModal').classList.add('active')">
                <i class="fas fa-edit"></i> Ubah Dana
            </button>
        @endif
    </div>
    <div class="card-body">
        <div class="grafik-header">
            <div class="dana-info">
                @foreach($budgetsByUnit as $unit => $info)
                <div class="dana-info-item">
                    <div class="label">Dana Lomba {{ $unit }} Triwulan 4</div>
                    <div class="value">Rp. {{ number_format($info['total'], 0, ',', '.') }}</div>
                    <div class="label" style="margin-top:4px;">Sisa: Rp. {{ number_format($info['sisa'], 0, ',', '.') }}</div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="chart-container">
            <canvas id="lombaChart" height="100"></canvas>
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
                        <option value="{{ $b->id }}" data-total="{{ $b->total_dana }}">Dana Lomba {{ $b->nama_unit }} Triwulan 4</option>
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
const colors = ['#1a3c34', '#5ba4cf', '#28a745', '#ffc107'];

const datasets = [];
for (let t = 1; t <= 4; t++) {
    datasets.push({
        label: 'Triwulan ' + t,
        data: units.map(u => chartData[u] ? chartData[u][t] : 0),
        backgroundColor: colors[t-1],
    });
}

new Chart(document.getElementById('lombaChart'), {
    type: 'bar',
    data: { labels: units, datasets: datasets },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); } } } },
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
if (document.getElementById('budgetSelect')) updateBudgetAction();
</script>
@endpush
@endsection
