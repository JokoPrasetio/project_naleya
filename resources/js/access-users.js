import DataTable from 'datatables.net-bs5';
import TomSelect from 'tom-select';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

const tableElement = document.getElementById('usersTable');

if (tableElement) {
    const roleFilter = document.getElementById('roleFilter');
    const canManage = tableElement.dataset.canManage === '1';
    const dataTable = new DataTable(tableElement, {
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'asc']],
        ajax: {
            url: tableElement.dataset.source,
            data: (parameters) => {
                parameters.role_id = roleFilter.value;
            },
        },
        columns: [
            { data: 'name', render: DataTable.render.text() },
            { data: 'email', render: DataTable.render.text() },
            { data: 'roles', orderable: false, render: DataTable.render.text() },
            {
                data: 'is_active',
                render: (active, type) => {
                    if (type !== 'display') return active ? '1' : '0';

                    return active
                        ? '<span class="status confirmed">Aktif</span>'
                        : '<span class="status waiting">Nonaktif</span>';
                },
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: (id) => canManage
                    ? `<button class="btn btn-sm btn-outline-secondary" type="button" data-edit-user="${Number(id)}">Ubah</button>`
                    : '',
            },
        ],
        language: {
            emptyTable: 'Belum ada pengguna.',
            zeroRecords: 'Tidak ada pengguna yang sesuai.',
            processing: 'Memuat data...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ pengguna',
            infoEmpty: 'Belum ada pengguna',
            infoFiltered: '(dari _MAX_ pengguna)',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
        },
    });

    roleFilter?.addEventListener('change', () => dataTable.ajax.reload());

    const modalElement = document.getElementById('userModal');

    if (modalElement) {
        const form = document.getElementById('userForm');
        const method = document.getElementById('userFormMethod');
        const editingId = document.getElementById('editingUserId');
        const title = document.getElementById('userModalTitle');
        const name = document.getElementById('userName');
        const email = document.getElementById('userEmail');
        const password = document.getElementById('userPassword');
        const confirmation = document.getElementById('userPasswordConfirmation');
        const mismatchMessage = document.getElementById('passwordMismatch');
        const saveButton = document.getElementById('saveUserButton');
        const validatePasswordMatch = () => {
            const mismatch = confirmation.value !== '' && password.value !== confirmation.value;
            confirmation.required = password.required || password.value !== '';
            confirmation.setCustomValidity(mismatch ? 'Kata sandi dan konfirmasi tidak sama.' : '');
            confirmation.classList.toggle('is-invalid', mismatch);
            mismatchMessage.classList.toggle('d-none', !mismatch);
            saveButton.disabled = mismatch;

            return !mismatch;
        };
        password.addEventListener('input', validatePasswordMatch);
        confirmation.addEventListener('input', validatePasswordMatch);
        form.addEventListener('submit', (event) => {
            if (!validatePasswordMatch()) {
                event.preventDefault();
                confirmation.reportValidity();
            }
        });
        const passwordButtons = form.querySelectorAll('[data-toggle-password]');
        const hidePasswords = () => {
            passwordButtons.forEach((button) => {
                const input = document.getElementById(button.dataset.togglePassword);
                input.type = 'password';
                button.setAttribute('aria-pressed', 'false');
                button.setAttribute('aria-label', 'Tampilkan ' + (input === confirmation ? 'ulangi kata sandi' : 'kata sandi'));
                button.title = button.getAttribute('aria-label');
                button.querySelector('i').className = 'bi bi-eye';
            });
        };
        passwordButtons.forEach((button) => button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-label', (visible ? 'Sembunyikan ' : 'Tampilkan ') + (input === confirmation ? 'ulangi kata sandi' : 'kata sandi'));
            button.title = button.getAttribute('aria-label');
            button.querySelector('i').className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
        }));
        const isActive = document.getElementById('userIsActive');
        const passwordHint = document.getElementById('passwordHint');
        const roleSelect = document.getElementById('userRoles');
        const roles = new TomSelect(roleSelect, {
            options: Array.from(roleSelect.options, (option) => ({
                value: option.value,
                text: option.textContent.trim(),
            })),
            plugins: { remove_button: { title: 'Hapus peran' } },
            maxItems: null,
            create: false,
            placeholder: 'Pilih satu atau beberapa peran',
        });
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);

        const setMode = (user = null, preserveValues = false) => {
            const isEdit = user !== null;

            if (!preserveValues) {
                form.reset();
                hidePasswords();
                roles.clear();
                name.value = user?.name ?? '';
                email.value = user?.email ?? '';
                roles.setValue((user?.role_ids ?? []).map(String));
                isActive.checked = user?.is_active ?? true;
                password.value = '';
                confirmation.value = '';
            }

            method.disabled = !isEdit;
            editingId.value = isEdit ? String(user.id) : '';
            form.action = isEdit
                ? `${form.dataset.updateBase}/${Number(user.id)}`
                : form.dataset.storeUrl;
            title.textContent = isEdit ? 'Ubah Pengguna' : 'Tambah Pengguna';
            password.required = !isEdit;
            confirmation.required = !isEdit;
            validatePasswordMatch();
            passwordHint.textContent = isEdit
                ? 'Kosongkan jika kata sandi tidak diubah.'
                : 'Minimal 12 karakter.';
        };

        document.getElementById('addUserButton')?.addEventListener('click', () => {
            setMode();
            modal.show();
        });

        tableElement.addEventListener('click', (event) => {
            const button = event.target instanceof Element
                ? event.target.closest('[data-edit-user]')
                : null;
            if (!button) return;

            const user = dataTable.row(button.closest('tr')).data();
            if (!user) return;

            setMode(user);
            modal.show();
        });

        if (modalElement.dataset.hasErrors === '1') {
            const failedId = Number(modalElement.dataset.editingId);
            setMode(failedId ? { id: failedId } : null, true);
            modal.show();
        }
    }
}
