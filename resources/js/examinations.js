const tableElement = document.getElementById('examinationsTable');

if (tableElement) {
    const body = tableElement.querySelector('tbody');
    const canManage = tableElement.dataset.canManage === '1';
    let treeRows = [...body.querySelectorAll('[data-tree-id]')];
    let rowById = new Map(treeRows.map((row) => [row.dataset.treeId, row]));
    let children = new Map();
    treeRows.forEach((row) => {
        const parentId = row.dataset.parentId;
        if (parentId !== '0' && rowById.has(parentId)) children.set(parentId, [...(children.get(parentId) || []), row]);
    });
    const depthOf = (row) => {
        let depth = 0;
        let parentId = row.dataset.parentId;
        while (parentId !== '0' && rowById.has(parentId)) {
            depth += 1;
            parentId = rowById.get(parentId).dataset.parentId;
        }
        return depth;
    };
    const setVisibleChildren = (row, visible) => {
        (children.get(row?.dataset.treeId) || []).forEach((child) => {
            child.hidden = !visible;
            if (!visible) setVisibleChildren(child, false);
            else if (child.querySelector('[data-tree-toggle]')?.getAttribute('aria-expanded') === 'true') setVisibleChildren(child, true);
        });
    };
    treeRows.forEach((row) => {
        const depth = depthOf(row);
        const indent = row.querySelector('.tree-indent');
        if (indent) indent.style.display = 'inline-block', indent.style.width = `${depth * 22}px`;
        if (depth > 0) row.hidden = true;
    });

    const renderTreeFilter = () => {
        const term = document.getElementById('examinationTreeSearch')?.value.trim().toLocaleLowerCase('id-ID') || '';
        treeRows.forEach((row) => {
            row.hidden = depthOf(row) > 0;
            const toggle = row.querySelector('[data-tree-toggle]');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
                const icon = toggle.querySelector('i');
                if (icon) icon.className = 'bi bi-chevron-right';
            }
        });
        if (!term) return;
        treeRows.forEach((row) => {
            if ((row.dataset.name || '').includes(term) || (row.dataset.code || '').includes(term)) {
                let parentId = row.dataset.parentId;
                while (parentId !== '0' && rowById.has(parentId)) {
                    const parent = rowById.get(parentId);
                    parent.hidden = false;
                    parent.querySelector(`[data-tree-toggle="${parentId}"]`)?.setAttribute('aria-expanded', 'true');
                    const icon = parent.querySelector(`[data-tree-toggle="${parentId}"] i`);
                    if (icon) icon.className = 'bi bi-chevron-down';
                    parentId = parent.dataset.parentId;
                }
                row.hidden = false;
            }
        });
    };
    document.getElementById('examinationTreeSearch')?.addEventListener('input', renderTreeFilter);
    tableElement.addEventListener('click', async (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const toggle = target?.closest('[data-tree-toggle]');
        if (toggle) {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!expanded));
            const icon = toggle.querySelector('i');
            if (icon) icon.className = expanded ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
            setVisibleChildren(rowById.get(toggle.dataset.treeToggle), !expanded);
            return;
        }
    });

    const modalElement = document.getElementById('examinationModal');
    const deleteForm = document.getElementById('deleteExaminationForm');
    const toastElement = document.getElementById('examinationToast');
    const toast = toastElement ? window.bootstrap.Toast.getOrCreateInstance(toastElement) : null;
    const showToast = (message, isError = false) => {
        if (!toastElement || !toast) return;
        toastElement.classList.toggle('is-error', isError);
        toastElement.querySelector('strong').textContent = isError ? 'Gagal' : 'Berhasil';
        toastElement.querySelector('small').textContent = message;
        toast.show();
    };
    const readResponse = async (response) => {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) throw new Error(response.redirected ? 'Sesi login berakhir. Muat ulang halaman lalu login kembali.' : `Server mengirim halaman yang tidak dikenali (HTTP ${response.status}).`);
        const payload = await response.json();
        if (!response.ok) throw payload;
        return payload;
    };
    const getJson = async (url) => readResponse(await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }));
    const postForm = async (form, url = form.action) => readResponse(await fetch(url, { method: 'POST', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(form), credentials: 'same-origin' }));
    const firstError = (error) => Object.values(error.errors || {}).flat()[0] || error.message || 'Terjadi kesalahan.';
    const refreshTree = async () => {
        const url = new URL(tableElement.dataset.source, window.location.href);
        url.searchParams.set('tree', '1');
        const data = await getJson(url.toString());
        body.replaceChildren();
        data.forEach((record) => {
            const row = document.createElement('tr');
            row.className = `examination-tree-row node-${record.node_type}`;
            row.dataset.treeId = record.id;
            row.dataset.parentId = record.parent_id;
            row.dataset.nodeType = record.node_type;
            row.dataset.name = record.name.toLocaleLowerCase('id-ID');
            row.dataset.code = (record.code || '').toLocaleLowerCase('id-ID');
            row.dataset.row = JSON.stringify(record);
            const nameCell = row.insertCell();
            nameCell.className = 'tree-name-cell';
            const childrenPresent = data.some((item) => String(item.parent_id) === String(record.id));
            if (childrenPresent) {
                const toggle = document.createElement('button'); toggle.type = 'button'; toggle.className = 'btn btn-sm btn-light border me-1'; toggle.dataset.treeToggle = record.id; toggle.setAttribute('aria-expanded', 'false');
                const icon = document.createElement('i'); icon.className = 'bi bi-chevron-right'; toggle.append(icon); nameCell.append(toggle);
            }
            if (!childrenPresent) {
                const itemMark = document.createElement('span'); itemMark.className = 'tree-item-mark me-2'; itemMark.innerHTML = '<i class="bi bi-dot"></i>'; nameCell.append(itemMark);
            }
            const strong = document.createElement('strong'); strong.textContent = record.name; nameCell.append(strong);
            [record.code || '—', record.type || '—'].forEach((value) => { row.insertCell().textContent = value; });
            const typeCell = row.insertCell();
            const typeBadge = document.createElement('span');
            typeBadge.className = 'node-type-badge';
            typeBadge.textContent = ({ header: 'Header', sub_header: 'Sub Header', item: 'Item' })[record.node_type] || record.node_type;
            typeCell.append(typeBadge);
            row.insertCell().textContent = record.price === null ? '—' : `Rp${new Intl.NumberFormat('id-ID').format(Number(record.price))}`;
            const status = document.createElement('span'); status.className = `status ${record.active ? 'confirmed' : 'waiting'}`; status.textContent = record.active ? 'Aktif' : 'Nonaktif'; row.insertCell().append(status);
            const actions = row.insertCell(); actions.className = 'text-end text-nowrap';
            if (canManage) {
                const edit = document.createElement('button'); edit.type = 'button'; edit.className = 'btn btn-sm btn-outline-secondary'; edit.dataset.editExamination = record.id; edit.textContent = 'Ubah';
                const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'btn btn-sm btn-outline-danger ms-1'; remove.dataset.deleteExamination = record.id; remove.textContent = 'Hapus';
                actions.append(edit, remove);
            }
            body.append(row);
        });
        const newRows = [...body.querySelectorAll('[data-tree-id]')];
        const byId = new Map(newRows.map((row) => [row.dataset.treeId, row]));
        const childMap = new Map();
        newRows.forEach((row) => { if (row.dataset.parentId !== '0' && byId.has(row.dataset.parentId)) childMap.set(row.dataset.parentId, [...(childMap.get(row.dataset.parentId) || []), row]); });
        const depth = (row) => { let value = 0, parentId = row.dataset.parentId; while (parentId !== '0' && byId.has(parentId)) { value += 1; parentId = byId.get(parentId).dataset.parentId; } return value; };
        newRows.forEach((row) => { const indent = document.createElement('span'); indent.className = 'tree-indent'; indent.style.display = 'inline-block'; indent.style.width = `${depth(row) * 22}px`; row.cells[0].prepend(indent); if (row.dataset.nodeType === 'item') { const mark = row.querySelector('.tree-item-mark'); if (mark) mark.style.width = '32px'; } if (depth(row) > 0) row.hidden = true; });
        treeRows = newRows; children = childMap; rowById = byId;
        renderTreeFilter();
    };

    if (modalElement) {
        const form = document.getElementById('examinationForm');
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
        const deleteModal = window.bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteExaminationModal'));
        const errorsElement = document.getElementById('examinationErrors');
        const normalRows = document.getElementById('normalValueRows');
        const abnormalRows = document.getElementById('abnormalValueRows');
        const resultRows = document.getElementById('resultFieldRows');
        const typeSelect = form.querySelector('[data-exam-field="type_examination_id"]');
        const parentSelect = form.querySelector('[data-exam-field="parent_id"]');
        const classifications = [['male', 'Male'], ['female', 'Female'], ['child', 'Child'], ['4_7yr', '4–7 tahun'], ['1_3yr', '1–3 tahun'], ['6_11mo', '6–11 bulan'], ['3_5mo', '3–5 bulan'], ['1_2mo', '1–2 bulan']];
        const operators = [['>', '>'], ['<', '<'], ['>=', '≥'], ['<=', '≤'], ['==', '='], ['!==', '≠']];
        const addRow = (container, type, values = {}) => {
            const row = document.createElement('div'); row.className = 'row g-2 align-items-center border rounded p-2';
            const templates = {
                normal: `<div class="col-md-3"><select class="form-select form-select-sm" data-key="classification" required><option value="">Pilih klasifikasi</option>${classifications.map(([value, label]) => `<option value="${value}">${label}</option>`).join('')}</select></div><div class="col-md-3"><input class="form-control form-control-sm" data-key="value" placeholder="Nilai"></div><div class="col-md-2"><select class="form-select form-select-sm" data-key="operator"><option value="">Pilih operator</option>${operators.map(([value, label]) => `<option value="${value}">${label}</option>`).join('')}</select></div><div class="col-md-2"><input class="form-control form-control-sm" data-key="unit" placeholder="Satuan"></div>`,
                abnormal: `<div class="col-md-2"><select class="form-select form-select-sm" data-key="classification" required><option value="">Klasifikasi</option>${classifications.map(([value, label]) => `<option value="${value}">${label}</option>`).join('')}</select></div><div class="col-md-2"><select class="form-select form-select-sm" data-key="operator" required><option value="">Operator</option>${operators.map(([value, label]) => `<option value="${value}">${label}</option>`).join('')}</select></div><div class="col-md-2"><input class="form-control form-control-sm" data-key="value" placeholder="Nilai"></div><div class="col-md-2"><input class="form-control form-control-sm" data-key="conclusion" placeholder="Kesimpulan"></div><div class="col-md-2"><input class="form-control form-control-sm" data-key="suggestion" placeholder="Saran"></div><div class="col-md-2"><input class="form-control form-control-sm" data-key="summary" placeholder="Ringkasan"></div>`,
                result: `<div class="col-md-3"><input class="form-control form-control-sm" data-key="field_key" placeholder="Key field" required></div><div class="col-md-3"><input class="form-control form-control-sm" data-key="label" placeholder="Label" required></div><div class="col-md-2"><select class="form-select form-select-sm" data-key="field_type"><option value="text">Teks</option><option value="textarea">Textarea</option><option value="number">Angka</option><option value="image">Gambar</option></select></div><div class="col-md-2"><select class="form-select form-select-sm" data-key="required"><option value="0">Opsional</option><option value="1">Wajib</option></select></div><div class="col-md-2"><input class="form-control form-control-sm" data-key="default_value" placeholder="Nilai awal"></div>`,
            };
            row.innerHTML = `${templates[type]}<div class="col-12 text-end"><button class="btn btn-sm btn-outline-danger" type="button" data-remove>Hapus</button></div>`;
            row.querySelectorAll('[data-key]').forEach((input) => { input.value = values[input.dataset.key] ?? ''; });
            row.querySelector('[data-remove]').addEventListener('click', () => { row.remove(); renumber(container, type); }); container.append(row); renumber(container, type);
        };
        const renumber = (container, type) => [...container.children].forEach((row, index) => row.querySelectorAll('[data-key]').forEach((input) => { input.name = `${type === 'normal' ? 'normal_values' : type === 'abnormal' ? 'abnormal_values' : 'result_fields'}[${index}][${input.dataset.key}]`; }));
        const clearRows = () => { normalRows.replaceChildren(); abnormalRows.replaceChildren(); resultRows.replaceChildren(); };
        const loadSelects = async () => {
            const [types, parents] = await Promise.all([getJson(tableElement.dataset.typesSource), getJson(tableElement.dataset.parentsSource)]);
            [typeSelect, document.querySelector('[data-bulk-type]'), document.querySelector('[data-import-type]')].forEach((select) => { select.replaceChildren(new Option('Pilih jenis', '')); types.forEach((type) => select.add(new Option(type.name, type.id))); });
            [parentSelect, document.querySelector('[data-bulk-parent]'), document.querySelector('[data-import-parent]')].forEach((select) => { select.replaceChildren(new Option('Tanpa induk', '0')); parents.forEach((item) => { const option = new Option(item.name, item.id); option.dataset.typeId = String(item.type_examination_id); select.add(option); }); });
        };
        const syncParentType = (parent, type) => { const parentType = parent.selectedOptions[0]?.dataset.typeId; if (parent.value !== '0' && parentType) { type.value = parentType; type.disabled = true; } else type.disabled = false; };
        const populate = (data = {}) => {
            form.querySelectorAll('[data-exam-field]').forEach((input) => { input.value = data[input.dataset.examField] ?? (input.dataset.examField === 'parent_id' ? '0' : input.dataset.examField === 'active' || input.dataset.examField === 'sort_order' ? '1' : ''); });
            syncParentType(parentSelect, typeSelect);
            (data.normal_values || []).forEach((value) => addRow(normalRows, 'normal', value)); (data.abnormal_values || []).forEach((value) => addRow(abnormalRows, 'abnormal', value)); (data.result_fields || []).forEach((value) => addRow(resultRows, 'result', value));
        };
        const reset = async (record = null) => {
            form.reset(); clearRows(); errorsElement.hidden = true; await loadSelects();
            form.action = record ? `${modalElement.dataset.updateBase}/${record.id}` : modalElement.dataset.storeUrl;
            document.getElementById('examinationMethod').disabled = !record; document.getElementById('examinationModalTitle').textContent = record ? 'Ubah Pemeriksaan' : 'Tambah Pemeriksaan';
            if (record) populate(await getJson(`${modalElement.dataset.updateBase}/${record.id}`)); else populate();
        };
        document.getElementById('addExaminationButton')?.addEventListener('click', async () => { try { await reset(); modal.show(); } catch (error) { showToast(firstError(error), true); } });
        parentSelect.addEventListener('change', () => syncParentType(parentSelect, typeSelect));
        document.querySelector('[data-add-normal]').addEventListener('click', () => addRow(normalRows, 'normal'));
        document.querySelector('[data-add-abnormal]').addEventListener('click', () => addRow(abnormalRows, 'abnormal'));
        document.querySelector('[data-add-result-field]').addEventListener('click', () => addRow(resultRows, 'result'));
        tableElement.addEventListener('click', async (event) => {
            const target = event.target instanceof Element ? event.target : null;
            const edit = target?.closest('[data-edit-examination]'); const remove = target?.closest('[data-delete-examination]');
            const row = (edit || remove)?.closest('tr'); const record = row ? JSON.parse(row.dataset.row || '{}') : null;
            if (!record?.id) return;
            if (edit) { try { await reset(record); modal.show(); } catch (error) { showToast(firstError(error), true); } }
            if (remove) { document.getElementById('deleteExaminationName').textContent = record.name; deleteForm.action = `${deleteForm.dataset.baseUrl}/${record.id}`; deleteModal.show(); }
        });
        form.addEventListener('submit', async (event) => { event.preventDefault(); const button = document.getElementById('saveExaminationButton'); button.disabled = true; try { const result = await postForm(form); modal.hide(); showToast(result.message); await refreshTree(); } catch (error) { errorsElement.textContent = firstError(error); errorsElement.hidden = false; } finally { button.disabled = false; } });
        deleteForm.addEventListener('submit', async (event) => { event.preventDefault(); try { const result = await postForm(deleteForm); deleteModal.hide(); showToast(result.message); await refreshTree(); } catch (error) { deleteModal.hide(); showToast(firstError(error), true); } });

        const bulkModalElement = document.getElementById('bulkExaminationModal');
        const bulkModal = window.bootstrap.Modal.getOrCreateInstance(bulkModalElement);
        const bulkForm = document.getElementById('bulkExaminationForm');
        const bulkRows = document.getElementById('bulkExaminationRows');
        const addBulkRow = () => {
            const index = bulkRows.children.length; const row = document.createElement('tr');
            row.innerHTML = `<td><input class="form-control form-control-sm" name="rows[${index}][name]" required maxlength="255"></td><td><input class="form-control form-control-sm" name="rows[${index}][code_examination]" maxlength="255"></td><td><input class="form-control form-control-sm" name="rows[${index}][barcode_name]" maxlength="255"></td><td><select class="form-select form-select-sm" name="rows[${index}][node_type]"><option value="item">Item</option><option value="header">Header</option><option value="sub_header">Sub Header</option></select></td><td><input class="form-control form-control-sm" type="number" min="0" step="0.01" name="rows[${index}][price]"></td><td><input class="form-control form-control-sm" name="rows[${index}][code_machine]"></td><td><input class="form-control form-control-sm" name="rows[${index}][method_examination]"></td><td><input class="form-control form-control-sm" name="rows[${index}][unit]"></td><td><select class="form-select form-select-sm" name="rows[${index}][active]"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></td><td><button class="btn btn-sm btn-outline-danger" type="button" data-remove-bulk>&times;</button></td>`;
            row.querySelector('[data-remove-bulk]').addEventListener('click', () => { row.remove(); [...bulkRows.children].forEach((item, rowIndex) => item.querySelectorAll('[name]').forEach((input) => { input.name = input.name.replace(/rows\[\d+\]/, `rows[${rowIndex}]`); })); }); bulkRows.append(row);
        };
        document.getElementById('bulkExaminationButton')?.addEventListener('click', async () => { try { await loadSelects(); bulkForm.reset(); bulkRows.replaceChildren(); addBulkRow(); document.querySelector('[data-bulk-errors]').hidden = true; syncParentType(bulkForm.querySelector('[data-bulk-parent]'), bulkForm.querySelector('[data-bulk-type]')); bulkModal.show(); } catch (error) { showToast(firstError(error), true); } });
        document.querySelector('[data-add-bulk-row]').addEventListener('click', addBulkRow);
        bulkForm.querySelector('[data-bulk-parent]').addEventListener('change', () => syncParentType(bulkForm.querySelector('[data-bulk-parent]'), bulkForm.querySelector('[data-bulk-type]')));
        bulkForm.addEventListener('submit', async (event) => { event.preventDefault(); const button = bulkForm.querySelector('[data-bulk-save]'); const errors = bulkForm.querySelector('[data-bulk-errors]'); button.disabled = true; try { const result = await postForm(bulkForm, bulkModalElement.dataset.storeUrl); bulkModal.hide(); showToast(result.message); await refreshTree(); } catch (error) { errors.textContent = firstError(error); errors.hidden = false; } finally { button.disabled = false; } });

        const importModalElement = document.getElementById('importExaminationModal');
        const importModal = window.bootstrap.Modal.getOrCreateInstance(importModalElement);
        const importForm = document.getElementById('importExaminationForm');
        document.getElementById('importExaminationButton')?.addEventListener('click', async () => { try { await loadSelects(); importForm.reset(); importForm.querySelector('[data-import-errors]').hidden = true; syncParentType(importForm.querySelector('[data-import-parent]'), importForm.querySelector('[data-import-type]')); importModal.show(); } catch (error) { showToast(firstError(error), true); } });
        importForm.querySelector('[data-import-parent]').addEventListener('change', () => syncParentType(importForm.querySelector('[data-import-parent]'), importForm.querySelector('[data-import-type]')));
        importForm.addEventListener('submit', async (event) => { event.preventDefault(); const button = importForm.querySelector('[data-import-save]'); const errors = importForm.querySelector('[data-import-errors]'); button.disabled = true; try { const result = await postForm(importForm, importModalElement.dataset.importUrl); importModal.hide(); showToast(result.message); await refreshTree(); } catch (error) { errors.textContent = firstError(error); errors.hidden = false; } finally { button.disabled = false; } });
    }
}
