<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman</title>
    <style>
        @page { margin: 24px 32px; }
        body { font-family: 'Poppins', Arial, sans-serif; font-size: 12px; color: #333333; }
        h1 { font-size: 20px; margin: 0 0 8px 0; font-weight: 600; }
        h2 { font-size: 14px; margin: 16px 0 8px 0; font-weight: 600; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid #e0e0e0; padding-bottom: 12px; }
        .meta { font-size: 11px; line-height: 1.4; }
        .summary-grid { display: flex; gap: 12px; margin-bottom: 16px; }
        .summary-card { flex: 1; border: 1px solid #e0e0e0; border-radius: 6px; padding: 10px; }
        .summary-label { font-size: 10px; text-transform: uppercase; color: #666666; letter-spacing: 0.5px; }
        .summary-value { font-size: 16px; font-weight: 600; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #e0e0e0; text-align: left; }
        th { background: #f5f7fa; font-weight: 600; font-size: 11px; }
        td { font-size: 11px; }
        .footer { margin-top: 24px; font-size: 10px; color: #666666; text-align: right; }
        .status-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; }
        .status-pending { background: #e3f2fd; color: #1976d2; }
        .status-approved { background: #e8f5e8; color: #2e7d32; }
        .status-rejected { background: #ffebee; color: #c62828; }
        .status-picked_up { background: #e1f5fe; color: #0277bd; }
        .status-returned { background: #e8f5e8; color: #2e7d32; }
        .status-cancelled { background: #fff3e0; color: #f57c00; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Laporan Peminjaman Sarpras</h1>
            <div class="meta">
                Periode: {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}<br>
                Tanggal Cetak: {{ now()->format('d M Y H:i') }}
            </div>
        </div>
        <div class="meta">
            Status Filter: {{ $filters['status'] ?? 'Semua' }}<br>
            Peminjam: {{ $filters['user_id'] ?? 'Semua' }}<br>
            Sarpras: @if(($filters['sarpras_type'] ?? null) === 'sarana') Sarana ID {{ $filters['sarpras_id'] ?? 'Semua' }} @elseif(($filters['sarpras_type'] ?? null) === 'prasarana') Prasarana ID {{ $filters['sarpras_id'] ?? 'Semua' }} @else Semua Sarpras @endif
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total Pengajuan</div>
            <div class="summary-value">{{ $summary['total_records'] ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Peserta</div>
            <div class="summary-value">{{ $summary['total_participants'] ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Jam Terpakai</div>
            <div class="summary-value">{{ $summary['total_duration_hours'] ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Item Disetujui</div>
            <div class="summary-value">{{ $summary['total_items_approved'] ?? 0 }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Acara</th>
                <th>Peminjam</th>
                <th>Periode</th>
                <th>Lokasi / Sarpras</th>
                <th>Sarana Dipinjam</th>
                <th>Status</th>
                <th>Item Disetujui</th>
                <th>Peserta</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $peminjaman)
            <tr>
                <td>{{ $peminjaman->id }}</td>
                <td>
                    <strong>{{ $peminjaman->event_name ?? '-' }}</strong><br>
                    <span style="color:#666666;">{{ optional($peminjaman->ukm)->nama }}</span>
                </td>
                <td>{{ optional($peminjaman->user)->name ?? '-' }}</td>
                <td>
                    {{ optional($peminjaman->start_date)->format('d M Y') }} - {{ optional($peminjaman->end_date)->format('d M Y') }}<br>
                    @if($peminjaman->start_time || $peminjaman->end_time)
                        <span style="color:#666666;">{{ $peminjaman->start_time }} - {{ $peminjaman->end_time }}</span>
                    @endif
                </td>
                <td>
                    @if($peminjaman->prasarana)
                        {{ $peminjaman->prasarana->name }}
                    @elseif($peminjaman->lokasi_custom)
                        {{ $peminjaman->lokasi_custom }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($peminjaman->items->isNotEmpty())
                        @foreach($peminjaman->items as $item)
                            @php $approvedQty = (int) $item->approved_quantity; @endphp
                            <div>
                                {{ optional($item->sarana)->name ?? '-' }}{{ $approvedQty > 0 ? ' (' . $approvedQty . ')' : '' }}
                            </div>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td>
                    <span class="status-badge status-{{ $peminjaman->status }}">
                        {{ ucfirst(str_replace('_', ' ', $peminjaman->status)) }}
                    </span>
                </td>
                <td>
                    {{ $peminjaman->items->sum(fn($item) => (int) $item->approved_quantity) }}
                </td>
                <td>{{ $peminjaman->jumlah_peserta ?? '-' }}</td>
                <td>{{ optional($peminjaman->created_at)->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center; padding:16px;">Tidak ada data peminjaman untuk filter yang dipilih.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Peminjaman Sarpras Poliwangi
    </div>
</body>
</html>
