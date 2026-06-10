<div id="logout-modal" class="fixed inset-0 z-[80] hidden items-center justify-center px-4">
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-start gap-4 border-b border-slate-200 bg-gradient-to-r from-rose-50 to-amber-50 px-5 py-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                <span class="material-symbols-outlined text-[28px]">logout</span>
            </div>
            <div class="min-w-0">
                <h3 class="text-lg font-extrabold text-slate-900">Keluar dari akun?</h3>
                <p class="mt-1 text-sm text-slate-600">Sesi kasir akan berakhir dan Anda perlu login lagi untuk melanjutkan transaksi.</p>
            </div>
        </div>

        <div class="px-5 py-4">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Pastikan semua transaksi yang sedang dibuka sudah disimpan atau ditunda sebelum logout.
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4">
            <button type="button" id="logout-modal-cancel" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                Batal
            </button>
            <button type="button" id="logout-modal-confirm" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                <span class="material-symbols-outlined text-[18px]">lock_open_right</span>
                Logout Sekarang
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        const logoutModal = document.getElementById('logout-modal');
        const cancelBtn = document.getElementById('logout-modal-cancel');
        const confirmBtn = document.getElementById('logout-modal-confirm');
        let activeLogoutForm = null;

        if (!logoutModal || !cancelBtn || !confirmBtn) {
            return;
        }

        const openModal = (form) => {
            activeLogoutForm = form;
            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('flex');
        };

        const closeModal = () => {
            activeLogoutForm = null;
            logoutModal.classList.add('hidden');
            logoutModal.classList.remove('flex');
        };

        document.querySelectorAll('.js-logout-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                openModal(form);
            });
        });

        cancelBtn.addEventListener('click', closeModal);
        confirmBtn.addEventListener('click', () => {
            if (activeLogoutForm) {
                activeLogoutForm.submit();
            }
        });

        logoutModal.addEventListener('click', (event) => {
            if (event.target === logoutModal || event.target.classList.contains('absolute')) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !logoutModal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
</script>
<?php /**PATH /home/mrgana/pos-inventory/backend/resources/views/cashier/partials/logout-modal.blade.php ENDPATH**/ ?>