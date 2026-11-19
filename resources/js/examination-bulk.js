const form = document.getElementById('bulkExaminationForm');

if (form) {
    const rowsElement = document.getElementById('bulkExaminationRows');
    const errorsElement = document.getElementById('bulkExaminationErrors');
    const types = JSON.parse(form.dataset.types || '[]');
    const parents = JSON.parse(form.dataset.parents || '[]');
    const nodeLabels = { header: 'Header', sub_header: 'Sub Header', item: 'Item Pemeriksaan' };
    const typeOptions = () => `<option value="">Pilih jenis</option>${types.map((type) => `<option value="${type.id}">${type.name}</option>`).join('')}`;
    const rows = () => [...rowsElement.querySelectorAll('tr[data-bulk-row]')];

    const refreshParentOptions = () => {
        rows().forEach((row, index) => {
            const select = row.querySelector('[data-parent-reference]');
            const currentValue = select.value;
            const currentNodeType = row.querySelector('[data-node-type]').value;
            const options = [];
            if (currentNodeType === 'header') options.push(new Option('Tanpa induk (root)', '0'));
            parents.forEach((parent) => {
                if (currentNodeType === 'sub_header' && parent.node_type !== 'header') return;
                const option = new Option(`${parent.node_type === 'header' ? 'Header' : 'Sub Header'}: ${parent.name}`, `existing:${parent.id}`);
                option.dataset.typeId = String(parent.type_id);
                options.push(option);
            });
            rows().forEach((candidate, candidateIndex) => {
                if (candidate === row) return;
                const nodeType = candidate.querySelector('[data-node-type]').value;
                const name = candidate.querySelector('[data-name]').value.trim() || `Baris ${candidateIndex + 1}`;
                const canContain = currentNodeType === 'sub_header' ? nodeType === 'header' : currentNodeType === 'item' ? ['header', 'sub_header'].includes(nodeType) : false;
                if (canContain) {
                    const option = new Option(`Baris ${candidateIndex + 1} — ${nodeLabels[nodeType]}: ${name}`, `row:${candidateIndex}`);
                    option.dataset.rowIndex = String(candidateIndex);
                    options.push(option);
                }
            });
            select.replaceChildren(...options);
            if ([...select.options].some((option) => option.value === currentValue)) select.value = currentValue;
            select.dataset.currentNodeType = currentNodeType;
            syncRowType(row);
            row.querySelector('[data-row-number]').textContent = String(index + 1);
        });
        document.getElementById('bulkStructureSummary').textContent = `${rows().length} baris disiapkan. Pastikan setiap Sub Header dan Item memilih induk yang sesuai.`;
    };

    const syncRowType = (row) => {
        const parentSelect = row.querySelector('[data-parent-reference]');
        const typeSelect = row.querySelector('[data-row-type]');
        const parentOption = parentSelect.selectedOptions[0];
        const parentTypeId = parentOption?.dataset.typeId || (parentOption?.dataset.rowIndex !== undefined
            ? rows()[Number(parentOption.dataset.rowIndex)]?.querySelector('[data-row-type]')?.value
            : null);
        if (parentSelect.value !== '0' && parentTypeId) {
            typeSelect.value = String(parentTypeId);
            typeSelect.disabled = true;
        } else {
            typeSelect.disabled = false;
        }
    };

    const addRow = () => {
        const row = document.createElement('tr');
        row.dataset.bulkRow = '1';
        row.className = 'bulk-row-item';
        row.innerHTML = `
            <td><div class="d-flex align-items-center gap-2"><span class="badge text-bg-light" data-row-number></span><select class="form-select form-select-sm" name="rows[0][node_type]" data-node-type required><option value="header">Header</option><option value="sub_header">Sub Header</option><option value="item" selected>Item Pemeriksaan</option></select></div></td>
            <td><input class="form-control form-control-sm" name="rows[0][name]" data-name maxlength="255" required placeholder="Nama pemeriksaan"></td>
            <td><select class="form-select form-select-sm" name="rows[0][parent_reference]" data-parent-reference required></select><div class="small text-muted mt-1" data-parent-hint>Pilih bagian tempat data ini masuk.</div></td>
            <td><select class="form-select form-select-sm" name="rows[0][type_examination_id]" data-row-type required>${typeOptions()}</select></td>
            <td><input class="form-control form-control-sm" name="rows[0][code_examination]" maxlength="255"></td>
            <td><input class="form-control form-control-sm" type="number" min="0" step="0.01" name="rows[0][price]"></td>
            <td><input class="form-control form-control-sm" name="rows[0][code_machine]"></td>
            <td><input class="form-control form-control-sm" name="rows[0][method_examination]"></td>
            <td><input class="form-control form-control-sm" name="rows[0][unit]"></td>
            <td><select class="form-select form-select-sm" name="rows[0][active]"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></td>
            <td><button class="btn btn-sm btn-outline-danger" type="button" data-remove-row aria-label="Hapus baris"><i class="bi bi-trash"></i></button></td>`;
        rowsElement.append(row);
        row.querySelector('[data-node-type]').addEventListener('change', () => {
            row.className = `bulk-row-${row.querySelector('[data-node-type]').value}`;
            refreshParentOptions();
        });
        row.querySelector('[data-row-type]').addEventListener('change', () => rows().forEach(syncRowType));
        row.querySelector('[data-name]').addEventListener('input', refreshParentOptions);
        row.querySelector('[data-parent-reference]').addEventListener('change', () => syncRowType(row));
        row.querySelector('[data-remove-row]').addEventListener('click', () => {
            const previousRows = rows();
            const parentTargets = new Map(previousRows.map((item) => [item, item.querySelector('[data-parent-reference]').value]));
            row.remove();
            const currentRows = rows();
            currentRows.forEach((item) => {
                const reference = parentTargets.get(item);
                if (!reference?.startsWith('row:')) return;
                const previousParent = previousRows[Number(reference.slice(4))];
                item.querySelector('[data-parent-reference]').value = previousParent && currentRows.includes(previousParent)
                    ? `row:${currentRows.indexOf(previousParent)}`
                    : (item.querySelector('[data-node-type]').value === 'header' ? '0' : '');
            });
            renumberRows();
            refreshParentOptions();
        });
        renumberRows();
        refreshParentOptions();
    };

    const renumberRows = () => rows().forEach((row, rowIndex) => row.querySelectorAll('[name]').forEach((input) => {
        input.name = input.name.replace(/rows\[\d+\]/, `rows[${rowIndex}]`);
    }));

    document.querySelector('[data-add-bulk-row]').addEventListener('click', addRow);
    addRow();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorsElement.hidden = true;
        const button = form.querySelector('[data-bulk-save]');
        button.disabled = true;
        try {
            rows().forEach((row) => { row.querySelector('[data-row-type]').disabled = false; });
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
                credentials: 'same-origin',
            });
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) throw new Error('Server tidak mengembalikan respons yang dikenali. Muat ulang halaman dan coba lagi.');
            const result = await response.json();
            if (!response.ok) throw result;
            const destination = new URL(form.dataset.indexUrl, window.location.href);
            destination.searchParams.set('bulk_saved', String(rows().length));
            window.location.assign(destination.toString());
        } catch (error) {
            errorsElement.textContent = Object.values(error.errors || {}).flat()[0] || error.message || 'Gagal menyimpan pemeriksaan.';
            errorsElement.hidden = false;
        } finally {
            button.disabled = false;
        }
    });
}
