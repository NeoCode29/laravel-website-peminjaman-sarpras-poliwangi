<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Sarana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">
    <style>
        :root { font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif; }
        body { margin: 0; background: var(--primary-bg); color: var(--text-primary); }
        .container { max-width: 1100px; margin: 0 auto; padding: 24px; }
        .page-header { display:flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .title { font-size: 24px; font-weight: 600; }
        .subtitle { color: var(--text-secondary); font-size: 14px; margin-top: 4px; }
        .toolbar { display:flex; gap: 8px; }
        .input, .select { height:38px; padding: 0 12px; border:1px solid var(--border-color); background: var(--card-bg); color: var(--text-primary); border-radius: 8px; }
        .grid { display:grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        @media (max-width: 992px){ .grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 576px){ .grid { grid-template-columns: 1fr; } .page-header { flex-direction: column; align-items: stretch; } }
        .card { background: var(--card-bg); border-radius: var(--border-radius); box-shadow: var(--shadow); overflow: hidden; transition: box-shadow .2s ease; }
        .card:hover { box-shadow: var(--shadow-hover); }
        .thumb { aspect-ratio: 16/9; width: 100%; object-fit: cover; background: #e9ecef; }
        .card-body { padding: 14px; }
        .card-title { font-weight: 600; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .meta { display:flex; gap: 8px; flex-wrap: wrap; color: var(--text-secondary); font-size: 12px; }
        .badge { background: #eef2ff; color: #4f46e5; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 500; }
        .empty { text-align:center; color: var(--text-secondary); padding: 48px 0; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2qqe+qB+Yh5C5Vbk+G7niu735Sk7lN1FZC2hY6M1I2z3eZwFg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <div class="title">Daftar Sarana</div>
                <div class="subtitle">Jelajahi sarana yang tersedia untuk peminjaman</div>
            </div>
            <div class="toolbar">
                <input class="input" type="text" placeholder="Cari sarana..." aria-label="Cari sarana">
                <select class="select" aria-label="Filter kategori">
                    <option value="">Semua Kategori</option>
                    <option>Ruang</option>
                    <option>Peralatan</option>
                    <option>Kendaraan</option>
                </select>
            </div>
        </div>

        @php
            // Dummy data sementara; nanti bisa diganti data dari DB/Controller
            $items = [
                [ 'nama' => 'Proyektor Epson X200', 'kategori' => 'Peralatan', 'lokasi' => 'Ruang Rapat 1', 'tersedia' => true, 'gambar' => null ],
                [ 'nama' => 'Ruang Kelas A101', 'kategori' => 'Ruang', 'lokasi' => 'Gedung A Lantai 1', 'tersedia' => false, 'gambar' => null ],
                [ 'nama' => 'Kamera DSLR Canon', 'kategori' => 'Peralatan', 'lokasi' => 'Lab Multimedia', 'tersedia' => true, 'gambar' => null ],
                [ 'nama' => 'Minibus Kampus', 'kategori' => 'Kendaraan', 'lokasi' => 'Garasi Utama', 'tersedia' => true, 'gambar' => null ],
            ];
        @endphp

        @if(empty($items))
            <div class="card empty">Belum ada sarana.</div>
        @else
            <div class="grid">
                @foreach($items as $s)
                <div class="card" role="article">
                    @if($s['gambar'])
                        <img class="thumb" src="{{ $s['gambar'] }}" alt="{{ $s['nama'] }}">
                    @else
                        <div class="thumb" aria-hidden="true"></div>
                    @endif
                    <div class="card-body">
                        <div class="card-title">{{ $s['nama'] }}</div>
                        <div class="meta mb-sm">
                            <span><i class="fa-solid fa-layer-group"></i> {{ $s['kategori'] }}</span>
                            <span><i class="fa-solid fa-location-dot"></i> {{ $s['lokasi'] }}</span>
                        </div>
                        <div class="meta">
                            @if($s['tersedia'])
                                <span class="badge">Tersedia</span>
                            @else
                                <span class="badge" style="background:#fee2e2;color:#b91c1c">Tidak Tersedia</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        // placeholder untuk interaksi ringan nanti (filter, cari, dsb)
    </script>
</body>
</html>


