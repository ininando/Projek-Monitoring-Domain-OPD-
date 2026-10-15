{{-- Modal konfirmasi hapus. Tombol pemicu wajib punya atribut data-action (URL) dan data-nama. --}}
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-describedby="modalHapusDesc" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="formHapus">
            @csrf
            @method('DELETE')
            <div class="modal-body p-4">
                <div class="d-flex gap-3">
                    <div class="ds-modal-icon" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></div>
                    <div>
                        <h2 class="h5 mb-2" id="modalHapusLabel">Hapus data ini?</h2>
                        <p class="text-muted mb-0" id="modalHapusDesc">
                            <strong class="text-body" id="namaHapus"></strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light-ds" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash" aria-hidden="true"></i> Ya, hapus</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('modalHapus').addEventListener('show.bs.modal', function (event) {
            const tombol = event.relatedTarget;
            document.getElementById('formHapus').action = tombol.dataset.action;
            document.getElementById('namaHapus').textContent = tombol.dataset.nama;
        });
    </script>
@endpush
