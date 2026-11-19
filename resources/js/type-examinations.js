import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';

const tableElement = document.getElementById('typeExaminationsTable');

if (tableElement) {
    const canManage = tableElement.dataset.canManage === '1';
    const dataTable = new DataTable(tableElement, {
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'asc']],
        ajax: tableElement.dataset.source,
        columns: [
            { data: 'name', render: DataTable.render.text() },
            {
                data: 'status',
                render: (status, type) => {
                    if (type !== 'display') return status ? '1' : '0';

                    return status
                        ? '<span class="status confirmed">Aktif</span>'
                        : '<span class="status waiting">Nonaktif</span>';
                },
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-end text-nowrap',
                render: (id) => canManage
                    ? `<button class="btn btn-sm btn-outline-secondary" type="button" data-edit-type="${Number(id)}">Ubah</button> <button class="btn btn-sm btn-outline-danger" type="button" data-delete-type="${Number(id)}">Hapus</button>`
                    : '',
            },
        ],
        language: {
            emptyTable: 'Belum ada jenis pemeriksaan.',
            zeroRecords: 'Tidak ada jenis pemeriksaan yang sesuai.',
            processing: 'Memuat data...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ jenis pemeriksaan',
            infoEmpty: 'Belum ada jenis pemeriksaan',
            infoFiltered: '(dari _MAX_ jenis pemeriksaan)',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
        },
    });

    const toastElement = document.getElementById('typeExaminationToast');
    const toast = window.bootstrap.Toast.getOrCreateInstance(toastElement);
    const showToast = (message, isError = false) => {
        toastElement.classList.toggle('is-error', isError);
        toastElement.querySelector('strong').textContent = isError ? 'Gagal' : 'Berhasil';
        toastElement.querySelector('.app-toast-icon').className = `bi ${isError ? 'bi-exclamation-circle' : 'bi-check-circle'} app-toast-icon`;
        toastElement.querySelector('small').textContent = message;
        toast.show();
    };
    const sendForm = async (form) => {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
            credentials: 'same-origin',
        });
        const payload = await response.json().catch(() => ({ message: 'Respons server tidak dapat dibaca.' }));
        if (!response.ok) {
            const error = new Error(payload.message || 'Permintaan gagal diproses.');
            error.payload = payload;
            throw error;
        }

        return payload;
    };

    const modalElement = document.getElementById('typeExaminationModal');

    if (modalElement) {
        const form = document.getElementById('typeExaminationForm');
        const method = document.getElementById('typeExaminationFormMethod');
        const editingId = document.getElementById('editingTypeExaminationId');
        const title = document.getElementById('typeExaminationModalTitle');
        const description = document.getElementById('typeExaminationModalDescription');
        const createFields = document.getElementById('createTypeExaminationFields');
        const editFields = document.getElementById('editTypeExaminationFields');
        const editName = document.getElementById('typeExaminationName');
        const editStatus = document.getElementById('typeExaminationStatus');
        const rows = document.getElementById('typeExaminationRows');
        const addRowButton = document.getElementById('addTypeExaminationRow');
        const saveButton = document.getElementById('saveTypeExaminationButton');
        const ajaxError = document.getElementById('typeExaminationAjaxError');
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);

        const fieldNameFromErrorKey = (key) => {
            const [first, ...parts] = key.split('.');
            return first + parts.map((part) => `[${part}]`).join('');
        };
        const clearErrors = () => {
            ajaxError.hidden = true;
            ajaxError.textContent = '';
            form.querySelectorAll('.is-invalid').forEach((input) => input.classList.remove('is-invalid'));
        };
        const displayErrors = (payload) => {
            const entries = Object.entries(payload.errors || {});
            ajaxError.textContent = entries[0]?.[1]?.[0] || payload.message || 'Data gagal disimpan.';
            ajaxError.hidden = false;
            entries.forEach(([key]) => {
                form.elements.namedItem(fieldNameFromErrorKey(key))?.classList.add('is-invalid');
            });
        };

        const renumberRows = () => {
            [...rows.querySelectorAll('[data-type-row]')].forEach((row, index) => {
                row.querySelector('[data-row-number]').textContent = String(index + 1);
                row.querySelectorAll('[data-type-field]').forEach((input) => {
                    input.name = `items[${index}][${input.dataset.typeField}]`;
                });
                row.querySelector('[data-remove-row]').disabled = rows.children.length === 1;
            });
            addRowButton.disabled = rows.children.length >= 50;
        };

        const addRow = (values = {}) => {
            if (rows.children.length >= 50) return;

            const row = document.createElement('tr');
            row.dataset.typeRow = '';
            row.innerHTML = `
                <td class="text-center"><span class="badge text-bg-light" data-row-number></span></td>
                <td><input class="form-control form-control-sm" data-type-field="name" maxlength="255" placeholder="Nama jenis pemeriksaan" required></td>
                <td><select class="form-select form-select-sm" data-type-field="status"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></td>
                <td class="text-end"><button class="btn btn-sm btn-outline-danger" type="button" data-remove-row aria-label="Hapus baris"><i class="bi bi-trash" aria-hidden="true"></i></button></td>`;
            rows.append(row);
            row.querySelector('[data-type-field="name"]').value = values.name ?? '';
            row.querySelector('[data-type-field="status"]').value = String(values.status ?? '1');
            row.querySelector('[data-remove-row]').addEventListener('click', () => {
                row.remove();
                renumberRows();
            });
            renumberRows();
        };

        const resetCreateRows = () => {
            rows.replaceChildren();
            addRow();
        };

        const setMode = (record = null, preserveValues = false) => {
            const isEdit = record !== null;

            clearErrors();

            if (!preserveValues) {
                form.reset();
                if (isEdit) {
                    editName.value = record.name;
                    editStatus.value = record.status ? '1' : '0';
                } else {
                    resetCreateRows();
                }
            }

            method.disabled = !isEdit;
            editingId.value = isEdit ? String(record.id) : '';
            form.action = isEdit
                ? `${form.dataset.updateBase}/${Number(record.id)}`
                : form.dataset.storeUrl;
            title.textContent = isEdit ? 'Ubah Jenis Pemeriksaan' : 'Tambah Jenis Pemeriksaan';
            description.textContent = isEdit
                ? 'Perbarui nama dan status jenis pemeriksaan.'
                : 'Tambahkan satu atau beberapa data sekaligus.';
            createFields.hidden = isEdit;
            editFields.hidden = !isEdit;
            createFields.querySelectorAll('input, select, button').forEach((input) => { input.disabled = isEdit; });
            editFields.querySelectorAll('input, select').forEach((input) => { input.disabled = !isEdit; });
        };

        rows.replaceChildren();
        (window.typeExaminationOldItems || [{ name: '', status: '1' }]).forEach(addRow);

        document.getElementById('addTypeExaminationButton')?.addEventListener('click', () => {
            setMode();
            modal.show();
        });
        addRowButton.addEventListener('click', () => addRow());

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearErrors();
            saveButton.disabled = true;

            try {
                const payload = await sendForm(form);
                modal.hide();
                showToast(payload.message);
                dataTable.ajax.reload(null, false);
            } catch (error) {
                displayErrors(error.payload || { message: error.message });
            } finally {
                saveButton.disabled = false;
            }
        });

        const deleteModalElement = document.getElementById('deleteTypeExaminationModal');
        const deleteModal = window.bootstrap.Modal.getOrCreateInstance(deleteModalElement);
        const deleteForm = document.getElementById('deleteTypeExaminationForm');
        const deleteName = document.getElementById('deleteTypeExaminationName');
        const deleteButton = deleteForm.querySelector('button[type="submit"]');

        deleteForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            deleteButton.disabled = true;

            try {
                const payload = await sendForm(deleteForm);
                deleteModal.hide();
                showToast(payload.message);
                dataTable.ajax.reload(null, false);
            } catch (error) {
                deleteModal.hide();
                showToast(error.payload?.message || error.message, true);
            } finally {
                deleteButton.disabled = false;
            }
        });

        tableElement.addEventListener('click', (event) => {
            const target = event.target instanceof Element ? event.target : null;
            const editButton = target?.closest('[data-edit-type]');
            const deleteButton = target?.closest('[data-delete-type]');
            const row = (editButton || deleteButton)?.closest('tr');
            const record = row ? dataTable.row(row).data() : null;
            if (!record) return;

            if (editButton) {
                setMode(record);
                modal.show();
            } else if (deleteButton) {
                deleteName.textContent = record.name;
                deleteForm.action = `${deleteForm.dataset.deleteBase}/${Number(record.id)}`;
                deleteModal.show();
            }
        });

        if (modalElement.dataset.hasErrors === '1') {
            const failedId = Number(modalElement.dataset.editingId);
            setMode(failedId ? { id: failedId } : null, true);
            modal.show();
        }
    }
}
