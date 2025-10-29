<div id="deleteModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3>Konfirmasi Hapus Marking</h3>
        </div>
        <div class="dialog-body">
            <p>Apakah Anda yakin ingin menghapus marking ini? Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
            <button type="button" class="btn btn-danger" onclick="confirmDeleteMarking()">Hapus</button>
        </div>
    </div>
</div>

<div id="overrideModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3>Konfirmasi Override Marking</h3>
        </div>
        <div class="dialog-body">
            <p>Apakah Anda yakin ingin override marking ini? Marking akan dihapus dan slot akan dibebaskan.</p>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" onclick="closeOverrideModal()">Batal</button>
            <button type="button" class="btn btn-warning" onclick="confirmOverrideMarking()">Override</button>
        </div>
    </div>
</div>

<div id="convertModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3>Konfirmasi Konversi Marking</h3>
        </div>
        <div class="dialog-body">
            <p>Konversi marking ini menjadi pengajuan peminjaman baru?</p>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" onclick="closeConvertModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="confirmConvertMarking()">Konversi</button>
        </div>
    </div>
</div>
