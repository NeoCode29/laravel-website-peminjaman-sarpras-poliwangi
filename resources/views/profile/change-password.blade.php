@extends('profile.layout')

@section('title', 'Ubah Password')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-key mr-2"></i>
                        Ubah Password
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Kembali
                        </a>
                    </div>
                </div>
                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Tips:</strong> Gunakan password yang kuat dengan kombinasi huruf, angka, dan simbol.
                        </div>

                        <div class="form-group">
                            <label for="current_password">Password Lama <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Minimal 8 karakter</small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="show_password">
                                <label class="form-check-label" for="show_password">
                                    Tampilkan Password
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Ubah Password
                        </button>
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#show_password').change(function() {
        var passwordFields = ['#current_password', '#password', '#password_confirmation'];
        var type = $(this).is(':checked') ? 'text' : 'password';
        
        passwordFields.forEach(function(field) {
            $(field).attr('type', type);
        });
    });
});
</script>
@endpush
