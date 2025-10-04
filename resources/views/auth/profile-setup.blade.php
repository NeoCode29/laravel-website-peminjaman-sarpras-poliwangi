@extends('auth.layout')

@section('title', 'Lengkapi Profil')
@section('subtitle', 'Silakan lengkapi profil Anda')

@section('content')
<form method="POST" action="{{ route('profile.complete-setup') }}" class="auth-form">
    @csrf
    
    <div class="form-group">
        <label for="name" class="form-label">Nama Lengkap</label>
        <input 
            type="text" 
            id="name" 
            name="name" 
            class="form-control @error('name') error @enderror" 
            value="{{ old('name', $user->name) }}" 
            required 
            autofocus
            placeholder="Masukkan nama lengkap"
        >
        @error('name')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="phone" class="form-label">Nomor Handphone</label>
        <input 
            type="tel" 
            id="phone" 
            name="phone" 
            class="form-control @error('phone') error @enderror" 
            value="{{ old('phone', $user->phone) }}" 
            required
            placeholder="Masukkan nomor handphone"
        >
        @error('phone')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="address" class="form-label">Alamat</label>
        <textarea 
            id="address" 
            name="address" 
            class="form-control @error('address') error @enderror" 
            rows="3"
            placeholder="Masukkan alamat lengkap"
        >{{ old('address', $user->address) }}</textarea>
        @error('address')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="bio" class="form-label">Bio (Opsional)</label>
        <textarea 
            id="bio" 
            name="bio" 
            class="form-control @error('bio') error @enderror" 
            rows="3"
            placeholder="Ceritakan sedikit tentang diri Anda"
        >{{ old('bio', $user->bio) }}</textarea>
        @error('bio')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <button type="submit" class="btn btn-primary">
        Simpan Profil
    </button>
</form>
@endsection

@section('footer')
<p class="text-muted">
    Anda dapat mengubah profil ini nanti di halaman profil.
</p>
@endsection
