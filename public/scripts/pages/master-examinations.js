document.addEventListener("DOMContentLoaded", () => {
  const table = document.getElementById("masterExaminationTable");
  const search = document.getElementById("masterTableSearch");
  if (!table || !search) return;

  const rows = [...table.querySelectorAll("tbody tr[data-tree-id]")];
  const rowsById = new Map(rows.map((row) => [row.dataset.treeId, row]));
  const emptyRow = table.querySelector("[data-search-empty]");

  const ancestorsAreOpen = (row) => {
    let parentId = row.dataset.treeParent;
    while (parentId) {
      const parent = rowsById.get(parentId);
      if (!parent) break;
      if (parent.dataset.treeExpanded === "false") return false;
      parentId = parent.dataset.treeParent;
    }
    return true;
  };

  const refresh = () => {
    const query = search.value.trim().toLocaleLowerCase("id");
    const visibleSearchIds = new Set();

    if (query) {
      rows.forEach((row) => {
        const searchableText = [...row.cells]
          .slice(0, 7)
          .map((cell) => cell.textContent)
          .join(" ")
          .toLocaleLowerCase("id");
        if (!searchableText.includes(query)) return;

        visibleSearchIds.add(row.dataset.treeId);
        let parentId = row.dataset.treeParent;
        while (parentId) {
          visibleSearchIds.add(parentId);
          parentId = rowsById.get(parentId)?.dataset.treeParent;
        }
      });
    }

    rows.forEach((row) => {
      row.hidden = query
        ? !visibleSearchIds.has(row.dataset.treeId)
        : !ancestorsAreOpen(row);

      const toggle = row.querySelector("[data-tree-toggle]");
      if (toggle) {
        toggle.disabled = Boolean(query);
        toggle.setAttribute("aria-expanded", String(row.dataset.treeExpanded !== "false"));
        toggle.setAttribute("aria-label", `${row.dataset.treeExpanded === "false" ? "Buka" : "Tutup"} turunan ${row.cells[0].textContent.trim()}`);
      }
    });

    if (emptyRow) emptyRow.hidden = !query || visibleSearchIds.size > 0;
  };

  table.addEventListener("click", (event) => {
    const toggle = event.target.closest("[data-tree-toggle]");
    if (!toggle || toggle.disabled) return;

    const row = toggle.closest("[data-tree-id]");
    row.dataset.treeExpanded = String(row.dataset.treeExpanded === "false");
    refresh();
  });

  search.addEventListener("input", refresh);
  refresh();

  const modalElement = document.getElementById("examinationModal");
  if (!modalElement) return;

  const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
  const form = document.getElementById("examinationForm");
  const method = form.querySelector('[name="_method"]');
  const modalId = form.querySelector('[name="_modal_id"]');
  const extraSection = document.getElementById("additionalExaminationsSection");
  const extraRows = document.getElementById("additionalExaminations");
  const identityTab = document.getElementById("identity-tab");
  const bulkTabItem = document.getElementById("bulkTabItem");
  const normalValuesRows = document.getElementById("normalValuesRows");
  const abnormalValuesRows = document.getElementById("abnormalValuesRows");
  const nodes = window.examinationModalNodes || {};
  const old = window.examinationModalOld || {};
  const field = (name) => form.elements.namedItem(name);
  const currencyFormatter = new Intl.NumberFormat("id-ID");

  const formatCurrency = (input) => {
    const digits = input.value.replace(/\D/g, "").replace(/^0+(?=\d)/, "");
    input.value = digits ? currencyFormatter.format(Number(digits)) : "";
  };

  const bindCurrency = (input) => {
    if (input.dataset.currencyBound === "true") return;
    input.dataset.currencyBound = "true";
    input.addEventListener("input", () => formatCurrency(input));
    formatCurrency(input);
  };

  form.querySelectorAll("[data-currency]").forEach(bindCurrency);
  form.addEventListener("submit", () => {
    form.querySelectorAll("[data-currency]").forEach((input) => {
      input.value = input.value.replace(/\D/g, "");
    });
  });

  const setRowValues = (row, values, attribute) => {
    row.querySelectorAll(`[${attribute}]`).forEach((input) => {
      const name = input.getAttribute(attribute);
      input.value = values[name] ?? "";
    });
  };

  const refreshMatrixEmptyState = (tbody) => {
    if (tbody === normalValuesRows) {
      document.getElementById("normalValuesEmpty").hidden = tbody.children.length > 0;
    } else if (tbody === abnormalValuesRows) {
      document.getElementById("abnormalValuesEmpty").hidden = tbody.children.length > 0;
    }
  };

  const renumberMatrixRows = (tbody, prefix, attribute) => {
    [...tbody.children].forEach((row, position) => {
      row.querySelectorAll(`[${attribute}]`).forEach((input) => {
        input.name = `${prefix}[${position}][${input.getAttribute(attribute)}]`;
      });
    });
    refreshMatrixEmptyState(tbody);
  };

  const addNormalValueRow = (tbody, values = {}, prefix = "normal_values", attribute = "data-normal-field") => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td><input class="form-control form-control-sm" ${attribute}="classification" maxlength="255" placeholder="Contoh: 1-2 Bulan" required></td>
      <td><input class="form-control form-control-sm" ${attribute}="normal_value_type" maxlength="255" placeholder="Contoh: Notes"></td>
      <td><input class="form-control form-control-sm" ${attribute}="value" maxlength="255" placeholder="Nilai normal"></td>
      <td><input class="form-control form-control-sm" ${attribute}="unit" maxlength="255" placeholder="Satuan"></td>
      <td class="text-end"><button class="btn btn-sm btn-outline-danger" type="button" data-remove-matrix>Hapus</button></td>`;
    tbody.append(row);
    setRowValues(row, { normal_value_type: "Notes", ...values }, attribute);
    row.querySelector("[data-remove-matrix]").addEventListener("click", () => {
      row.remove();
      if (tbody.closest("[data-matrix-row]")) renumberRows();
      else renumberMatrixRows(tbody, prefix, attribute);
    });
    renumberMatrixRows(tbody, prefix, attribute);
  };

  const addAbnormalValueRow = (tbody, values = {}, prefix = "abnormal_values", attribute = "data-abnormal-field") => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td><input class="form-control form-control-sm" ${attribute}="classification" maxlength="255" placeholder="Contoh: 1-2 Bulan" required></td>
      <td><select class="form-select form-select-sm" ${attribute}="operator"><option value="">-</option><option value="==">==</option><option value="!=">!=</option><option value=">">&gt;</option><option value=">=">&gt;=</option><option value="<">&lt;</option><option value="<=">&lt;=</option><option value="between">Antara</option></select></td>
      <td><input class="form-control form-control-sm" ${attribute}="type" maxlength="255" placeholder="Contoh: Reactive / Non Reactive"></td>
      <td><input class="form-control form-control-sm" ${attribute}="value" maxlength="255" placeholder="Nilai"></td>
      <td><input class="form-control form-control-sm" ${attribute}="unit" maxlength="255" placeholder="Satuan"></td>
      <td><input class="form-control form-control-sm" ${attribute}="reference_value" maxlength="255" placeholder="Referensi"></td>
      <td><input class="form-control form-control-sm" ${attribute}="conclusion" maxlength="5000" placeholder="Kesimpulan"></td>
      <td><input class="form-control form-control-sm" ${attribute}="suggestion" maxlength="5000" placeholder="Saran"></td>
      <td><input class="form-control form-control-sm" ${attribute}="summary" maxlength="5000" placeholder="Ringkasan"></td>
      <td class="text-end"><button class="btn btn-sm btn-outline-danger" type="button" data-remove-matrix>Hapus</button></td>`;
    tbody.append(row);
    setRowValues(row, { type: "Angka", ...values }, attribute);
    row.querySelector("[data-remove-matrix]").addEventListener("click", () => {
      row.remove();
      if (tbody.closest("[data-matrix-row]")) renumberRows();
      else renumberMatrixRows(tbody, prefix, attribute);
    });
    renumberMatrixRows(tbody, prefix, attribute);
  };

  const resetForm = () => {
    form.reset();
    ["name", "code", "parent_id", "price", "unit", "method"].forEach((name) => { field(name).value = ""; });
    field("node_type").value = "header";
    field("header_type").value = "service";
    field("result_type").value = "number";
    field("is_orderable").value = "0";
    field("sort_order").value = "0";
    field("is_active").value = "1";
    form.querySelectorAll('[name="service_ids[]"]').forEach((checkbox) => { checkbox.checked = false; });
    normalValuesRows.replaceChildren();
    abnormalValuesRows.replaceChildren();
    refreshMatrixEmptyState(normalValuesRows);
    refreshMatrixEmptyState(abnormalValuesRows);
    form.querySelectorAll(".is-invalid").forEach((input) => input.classList.remove("is-invalid"));
    form.querySelectorAll(".invalid-feedback, .text-danger.small, .alert-danger").forEach((message) => message.remove());
  };

  const toggle = (name, visible) => {
    form.querySelectorAll(`[data-field="${name}"]`).forEach((group) => {
      group.hidden = !visible;
      group.style.display = visible ? "" : "none";
      group.querySelectorAll("input, select, textarea").forEach((input) => { input.disabled = !visible; });
    });
  };

  const refreshFields = () => {
    const nodeType = field("node_type").value;
    const isHeader = nodeType === "header";
    const isExamination = nodeType === "examination";
    toggle("header", isHeader);
    toggle("parent", !isHeader);
    toggle("package", isHeader && field("header_type").value === "package");
    toggle("examination", isExamination);
    toggle("price", isExamination && field("is_orderable").value === "1");
    extraRows.querySelectorAll("[data-additional-row]").forEach((row) => {
      const priceInput = row.querySelector('[data-additional-field="price"]');
      const orderableInput = row.querySelector('[data-additional-field="is_orderable"]');
      const matrixButton = row.querySelector("[data-matrix-toggle]");
      row.querySelectorAll("[data-examination-only]").forEach((input) => { input.disabled = !isExamination; });
      matrixButton.hidden = !isExamination;
      orderableInput.value = priceInput.value.replace(/\D/g, "") === "" ? "0" : "1";
      const matrixRow = row.nextElementSibling;
      matrixRow.querySelectorAll("input, select, textarea").forEach((input) => { input.disabled = !isExamination; });
    });
    const activeTab = form.querySelector("#examinationFormTabs .nav-link.active");
    if (activeTab?.closest(".nav-item")?.hidden) {
      bootstrap.Tab.getOrCreateInstance(identityTab).show();
    }
  };

  const renderEmptyAdditionalRows = () => {
    const hasRows = extraRows.querySelector("[data-additional-row]");
    const emptyRow = extraRows.querySelector("[data-additional-empty]");
    if (!hasRows && !emptyRow) {
      const row = document.createElement("tr");
      row.dataset.additionalEmpty = "";
      row.innerHTML = '<td class="text-center text-muted py-4" colspan="8">Klik “Tambah baris” untuk menambahkan pemeriksaan.</td>';
      extraRows.append(row);
    } else if (hasRows && emptyRow) {
      emptyRow.remove();
    }
  };

  const renumberRows = () => {
    [...extraRows.querySelectorAll("[data-additional-row]")].forEach((row, position) => {
      const matrixRow = row.nextElementSibling;
      [row, matrixRow].forEach((container) => {
        container.querySelectorAll("[data-additional-field]").forEach((input) => {
          input.name = `additional[${position}][${input.dataset.additionalField}]`;
        });
      });
      renumberMatrixRows(matrixRow.querySelector("[data-normal-values]"), `additional[${position}][normal_values]`, "data-normal-field");
      renumberMatrixRows(matrixRow.querySelector("[data-abnormal-values]"), `additional[${position}][abnormal_values]`, "data-abnormal-field");
      row.querySelector("[data-row-number]").textContent = String(position + 1);
    });
    renderEmptyAdditionalRows();
  };

  const addRow = (values = {}) => {
    if (extraRows.querySelectorAll("[data-additional-row]").length >= 20) return;
    extraRows.querySelector("[data-additional-empty]")?.remove();
    const index = extraRows.querySelectorAll("[data-additional-row]").length;
    const row = document.createElement("tr");
    row.dataset.additionalRow = "";
    row.innerHTML = `
      <td><span class="badge text-bg-light me-1" data-row-number></span><input class="form-control form-control-sm d-inline-block" style="width: 10rem" data-additional-field="name" maxlength="255" required aria-label="Nama pemeriksaan"></td>
      <td><input class="form-control form-control-sm" data-additional-field="code" maxlength="50" aria-label="Kode pemeriksaan"></td>
      <td><select class="form-select form-select-sm" data-additional-field="result_type" data-examination-only aria-label="Tipe hasil"><option value="number">Angka</option><option value="choice">Pilihan</option><option value="text">Teks</option></select></td>
      <td><input type="hidden" data-additional-field="is_orderable" data-examination-only><input class="form-control form-control-sm" type="text" inputmode="numeric" data-currency data-additional-field="price" data-examination-only placeholder="Kosong = tanpa harga" aria-label="Harga"></td>
      <td><input class="form-control form-control-sm" data-additional-field="method" data-examination-only maxlength="255" aria-label="Metode"></td>
      <td><input class="form-control form-control-sm" data-additional-field="unit" data-examination-only maxlength="50" aria-label="Satuan"></td>
      <td><select class="form-select form-select-sm" data-additional-field="is_active" aria-label="Status"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></td>
      <td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" type="button" data-matrix-toggle aria-expanded="false">Matrix</button> <button class="btn btn-sm btn-outline-danger" type="button" data-remove-additional>Hapus</button></td>`;

    const matrixRow = document.createElement("tr");
    matrixRow.dataset.matrixRow = "";
    matrixRow.hidden = true;
    matrixRow.innerHTML = `
      <td colspan="8" class="bg-light-subtle p-3">
        <input type="hidden" data-additional-field="matrix_values_present" value="1">
        <div class="d-flex justify-content-between align-items-center mb-3"><div><strong>Matrix nilai normal dan abnormal</strong><div class="text-muted small">Setiap klasifikasi usia atau jenis kelamin dapat memiliki nilainya sendiri.</div></div></div>
        <div class="d-flex justify-content-between align-items-center mb-2"><strong class="small">Nilai normal</strong><button class="btn btn-sm btn-outline-primary" type="button" data-add-normal>Tambah nilai normal</button></div>
        <div class="table-responsive border rounded mb-3"><table class="table table-sm align-middle mb-0"><thead><tr><th>Klasifikasi</th><th>Tipe</th><th>Nilai</th><th>Satuan</th><th>Aksi</th></tr></thead><tbody data-normal-values></tbody></table></div>
        <div class="d-flex justify-content-between align-items-center mb-2"><strong class="small">Nilai abnormal</strong><button class="btn btn-sm btn-outline-primary" type="button" data-add-abnormal>Tambah nilai abnormal</button></div>
        <div class="table-responsive border rounded"><table class="table table-sm align-middle mb-0" style="min-width: 90rem;"><thead><tr><th>Klasifikasi</th><th>Operator</th><th>Tipe</th><th>Nilai</th><th>Satuan</th><th>Referensi</th><th>Kesimpulan</th><th>Saran</th><th>Ringkasan</th><th>Aksi</th></tr></thead><tbody data-abnormal-values></tbody></table></div>
      </td>`;

    extraRows.append(row, matrixRow);
    const valueFor = (name, fallback = "") => values[name] ?? fallback;
    row.querySelector('[data-additional-field="name"]').value = valueFor("name");
    row.querySelector('[data-additional-field="code"]').value = valueFor("code");
    row.querySelector('[data-additional-field="result_type"]').value = valueFor("result_type", field("result_type").value);
    row.querySelector('[data-additional-field="price"]').value = valueFor("price", field("is_orderable").value === "1" ? field("price").value : "");
    row.querySelector('[data-additional-field="method"]').value = valueFor("method", field("method").value);
    row.querySelector('[data-additional-field="unit"]').value = valueFor("unit", field("unit").value);
    row.querySelector('[data-additional-field="is_active"]').value = String(valueFor("is_active", field("is_active").value));
    const additionalNormalRows = matrixRow.querySelector("[data-normal-values]");
    const additionalAbnormalRows = matrixRow.querySelector("[data-abnormal-values]");
    (values.normal_values || []).forEach((value) => addNormalValueRow(additionalNormalRows, value, "", "data-normal-field"));
    (values.abnormal_values || []).forEach((value) => addAbnormalValueRow(additionalAbnormalRows, value, "", "data-abnormal-field"));
    matrixRow.querySelector("[data-add-normal]").addEventListener("click", () => {
      addNormalValueRow(additionalNormalRows, {}, "", "data-normal-field");
      renumberRows();
    });
    matrixRow.querySelector("[data-add-abnormal]").addEventListener("click", () => {
      addAbnormalValueRow(additionalAbnormalRows, {}, "", "data-abnormal-field");
      renumberRows();
    });
    const priceInput = row.querySelector('[data-additional-field="price"]');
    bindCurrency(priceInput);
    priceInput.addEventListener("input", refreshFields);
    row.querySelector("[data-matrix-toggle]").addEventListener("click", (event) => {
      matrixRow.hidden = !matrixRow.hidden;
      event.currentTarget.setAttribute("aria-expanded", String(!matrixRow.hidden));
      event.currentTarget.textContent = matrixRow.hidden ? "Matrix" : "Tutup Matrix";
    });
    row.querySelector("[data-remove-additional]").addEventListener("click", () => {
      matrixRow.remove();
      row.remove();
      renumberRows();
    });
    renumberRows();
    refreshFields();
  };

  const setMode = (id = "") => {
    const node = nodes[id];
    modalId.value = id || "create";
    method.disabled = !node;
    form.action = node?.updateUrl || form.dataset.storeUrl;
    document.getElementById("examinationModalTitle").textContent = node ? "Ubah Master Pemeriksaan" : "Tambah Master Pemeriksaan";
    extraSection.hidden = Boolean(node);
    bulkTabItem.hidden = Boolean(node);
    extraSection.querySelectorAll("input, select, textarea").forEach((input) => { input.disabled = Boolean(node); });
    if (node && document.getElementById("bulk-tab").classList.contains("active")) {
      bootstrap.Tab.getOrCreateInstance(identityTab).show();
    }
  };

  document.querySelector("[data-create-examination]")?.addEventListener("click", () => {
    resetForm();
    extraRows.replaceChildren();
    renderEmptyAdditionalRows();
    setMode();
    bootstrap.Tab.getOrCreateInstance(identityTab).show();
    form.querySelectorAll("#examinationParent option").forEach((option) => { option.disabled = false; });
    refreshFields();
    modal.show();
  });

  table.addEventListener("click", (event) => {
    const button = event.target.closest("[data-edit-examination]");
    if (!button) return;
    const id = button.dataset.editExamination;
    const node = nodes[id];
    if (!node) return;
    resetForm();
    extraRows.replaceChildren();
    renderEmptyAdditionalRows();
    setMode(id);
    bootstrap.Tab.getOrCreateInstance(identityTab).show();
    ["name", "code", "node_type", "header_type", "parent_id", "sort_order", "is_active", "is_orderable", "result_type", "price", "unit", "method"].forEach((name) => {
      field(name).value = typeof node[name] === "boolean" ? String(Number(node[name])) : (node[name] ?? "");
    });
    (node.normal_values || []).forEach((value) => addNormalValueRow(normalValuesRows, value));
    (node.abnormal_values || []).forEach((value) => addAbnormalValueRow(abnormalValuesRows, value));
    formatCurrency(field("price"));
    form.querySelectorAll('[name="service_ids[]"]').forEach((checkbox) => {
      checkbox.checked = node.service_ids.includes(Number(checkbox.value));
    });
    form.querySelectorAll("#examinationParent option").forEach((option) => {
      option.disabled = option.value === id;
    });
    refreshFields();
    modal.show();
  });

  document.getElementById("addExaminationRow").addEventListener("click", () => addRow());
  document.getElementById("addNormalValue").addEventListener("click", () => addNormalValueRow(normalValuesRows));
  document.getElementById("addAbnormalValue").addEventListener("click", () => addAbnormalValueRow(abnormalValuesRows));
  ["node_type", "header_type", "result_type", "is_orderable"].forEach((name) => field(name).addEventListener("change", refreshFields));
  if (old.hasErrors) {
    setMode(old.id || "");
    normalValuesRows.replaceChildren();
    abnormalValuesRows.replaceChildren();
    (old.normal_values || []).forEach((value) => addNormalValueRow(normalValuesRows, value));
    (old.abnormal_values || []).forEach((value) => addAbnormalValueRow(abnormalValuesRows, value));
    (old.additional || []).forEach((values) => addRow(values));
    refreshFields();
    modal.show();
  } else {
    refreshFields();
  }
});
