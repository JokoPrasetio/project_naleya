document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("examinationPageForm");
  if (!form) return;

  const initial = window.examinationFormInitial || {};
  const normalValuesRows = document.getElementById("normalValuesRows");
  const abnormalValuesRows = document.getElementById("abnormalValuesRows");
  const additionalRows = document.getElementById("additionalExaminations");
  const identityTab = document.getElementById("identity-tab");
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

  const setRowValues = (row, values, attribute) => {
    row.querySelectorAll(`[${attribute}]`).forEach((input) => {
      input.value = values[input.getAttribute(attribute)] ?? "";
    });
  };

  const refreshMainEmptyState = () => {
    document.getElementById("normalValuesEmpty").hidden = normalValuesRows.children.length > 0;
    document.getElementById("abnormalValuesEmpty").hidden = abnormalValuesRows.children.length > 0;
  };

  const renumberMatrixRows = (tbody, prefix, attribute) => {
    [...tbody.children].forEach((row, position) => {
      row.querySelectorAll(`[${attribute}]`).forEach((input) => {
        input.name = `${prefix}[${position}][${input.getAttribute(attribute)}]`;
      });
    });
    refreshMainEmptyState();
  };

  const renumberAllRows = () => {
    if (!additionalRows) return;
    [...additionalRows.querySelectorAll("[data-additional-row]")].forEach((row, position) => {
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

    const hasRows = additionalRows.querySelector("[data-additional-row]");
    const emptyRow = additionalRows.querySelector("[data-additional-empty]");
    if (!hasRows && !emptyRow) {
      const row = document.createElement("tr");
      row.dataset.additionalEmpty = "";
      row.innerHTML = '<td class="text-center text-muted py-4" colspan="8">Klik “Tambah baris” untuk menambahkan pemeriksaan.</td>';
      additionalRows.append(row);
    } else if (hasRows && emptyRow) {
      emptyRow.remove();
    }
  };

  const removeMatrixRow = (row, tbody, prefix, attribute) => {
    row.remove();
    if (tbody.closest("[data-matrix-row]")) renumberAllRows();
    else renumberMatrixRows(tbody, prefix, attribute);
  };

  const addNormalValueRow = (tbody, values = {}, prefix = "normal_values") => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td><input class="form-control form-control-sm" data-normal-field="classification" maxlength="255" placeholder="Contoh: 1-2 Bulan" required></td>
      <td><input class="form-control form-control-sm" data-normal-field="normal_value_type" maxlength="255" placeholder="Contoh: Notes" required></td>
      <td><input class="form-control form-control-sm" data-normal-field="value" maxlength="255" placeholder="Nilai normal"></td>
      <td><input class="form-control form-control-sm" data-normal-field="unit" maxlength="255" placeholder="Satuan"></td>
      <td class="text-end"><button class="btn btn-sm btn-outline-danger" type="button" data-remove-matrix>Hapus</button></td>`;
    tbody.append(row);
    setRowValues(row, { normal_value_type: "Notes", ...values }, "data-normal-field");
    row.querySelector("[data-remove-matrix]").addEventListener("click", () => removeMatrixRow(row, tbody, prefix, "data-normal-field"));
    renumberMatrixRows(tbody, prefix, "data-normal-field");
  };

  const addAbnormalValueRow = (tbody, values = {}, prefix = "abnormal_values") => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td><input class="form-control form-control-sm" data-abnormal-field="classification" maxlength="255" placeholder="Contoh: Perempuan" required></td>
      <td><select class="form-select form-select-sm" data-abnormal-field="operator"><option value="">-</option><option value="==">==</option><option value="!=">!=</option><option value=">">&gt;</option><option value=">=">&gt;=</option><option value="<">&lt;</option><option value="<=">&lt;=</option><option value="between">Antara</option></select></td>
      <td><input class="form-control form-control-sm" data-abnormal-field="type" maxlength="255" placeholder="Contoh: Angka" required></td>
      <td><input class="form-control form-control-sm" data-abnormal-field="value" maxlength="255" placeholder="Nilai"></td>
      <td><input class="form-control form-control-sm" data-abnormal-field="unit" maxlength="255" placeholder="Satuan"></td>
      <td><input class="form-control form-control-sm" data-abnormal-field="reference_value" maxlength="255" placeholder="Referensi"></td>
      <td><input class="form-control form-control-sm" data-abnormal-field="conclusion" maxlength="5000" placeholder="Kesimpulan"></td>
      <td><input class="form-control form-control-sm" data-abnormal-field="suggestion" maxlength="5000" placeholder="Saran"></td>
      <td><input class="form-control form-control-sm" data-abnormal-field="summary" maxlength="5000" placeholder="Ringkasan"></td>
      <td class="text-end"><button class="btn btn-sm btn-outline-danger" type="button" data-remove-matrix>Hapus</button></td>`;
    tbody.append(row);
    setRowValues(row, { type: "Angka", ...values }, "data-abnormal-field");
    row.querySelector("[data-remove-matrix]").addEventListener("click", () => removeMatrixRow(row, tbody, prefix, "data-abnormal-field"));
    renumberMatrixRows(tbody, prefix, "data-abnormal-field");
  };

  const toggle = (name, visible) => {
    form.querySelectorAll(`[data-field="${name}"]`).forEach((group) => {
      group.hidden = !visible;
      group.style.display = visible ? "" : "none";
      group.querySelectorAll("input, select, textarea").forEach((input) => { input.disabled = !visible; });
    });
  };

  const refreshFields = () => {
    const isHeader = field("node_type").value === "header";
    const isExamination = field("node_type").value === "examination";
    toggle("header", isHeader);
    toggle("parent", !isHeader);
    toggle("package", isHeader && field("header_type").value === "package");
    toggle("examination", isExamination);
    toggle("price", isExamination && field("is_orderable").value === "1");

    additionalRows?.querySelectorAll("[data-additional-row]").forEach((row) => {
      const priceInput = row.querySelector('[data-additional-field="price"]');
      row.querySelector('[data-additional-field="is_orderable"]').value = priceInput.value.replace(/\D/g, "") === "" ? "0" : "1";
      row.querySelectorAll("[data-examination-only]").forEach((input) => { input.disabled = !isExamination; });
      row.querySelector("[data-matrix-toggle]").hidden = !isExamination;
      row.nextElementSibling.querySelectorAll("input, select, textarea").forEach((input) => { input.disabled = !isExamination; });
    });

    const activeTab = form.querySelector("#examinationFormTabs .nav-link.active");
    if (activeTab?.closest(".nav-item")?.hidden) bootstrap.Tab.getOrCreateInstance(identityTab).show();
  };

  const addAdditionalRow = (values = {}) => {
    if (!additionalRows || additionalRows.querySelectorAll("[data-additional-row]").length >= 20) return;
    additionalRows.querySelector("[data-additional-empty]")?.remove();

    const row = document.createElement("tr");
    row.dataset.additionalRow = "";
    row.innerHTML = `
      <td><div class="d-flex align-items-center gap-2"><span class="badge text-bg-light" data-row-number></span><input class="form-control form-control-sm" data-additional-field="name" maxlength="255" required aria-label="Nama pemeriksaan"></div></td>
      <td><input class="form-control form-control-sm" data-additional-field="code" maxlength="50" aria-label="Kode pemeriksaan"></td>
      <td><select class="form-select form-select-sm" data-additional-field="result_type" data-examination-only aria-label="Tipe hasil"><option value="number">Angka</option><option value="choice">Pilihan</option><option value="text">Teks</option></select></td>
      <td><input type="hidden" data-additional-field="is_orderable" data-examination-only><input class="form-control form-control-sm" type="text" inputmode="numeric" data-currency data-additional-field="price" data-examination-only placeholder="Tanpa harga" aria-label="Harga"></td>
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
        <div class="mb-3"><strong>Matrix untuk baris ini</strong><div class="text-muted small">Tambahkan klasifikasi usia atau jenis kelamin yang berbeda bila diperlukan.</div></div>
        <div class="d-flex justify-content-between align-items-center mb-2"><strong class="small">Nilai normal</strong><button class="btn btn-sm btn-outline-primary" type="button" data-add-normal>Tambah nilai normal</button></div>
        <div class="table-responsive border rounded mb-3"><table class="table table-sm align-middle mb-0"><thead class="table-light"><tr><th>Klasifikasi</th><th>Tipe</th><th>Nilai</th><th>Satuan</th><th>Aksi</th></tr></thead><tbody data-normal-values></tbody></table></div>
        <div class="d-flex justify-content-between align-items-center mb-2"><strong class="small">Nilai abnormal</strong><button class="btn btn-sm btn-outline-primary" type="button" data-add-abnormal>Tambah nilai abnormal</button></div>
        <div class="table-responsive border rounded"><table class="table table-sm align-middle mb-0" style="min-width: 90rem;"><thead class="table-light"><tr><th>Klasifikasi</th><th>Operator</th><th>Tipe</th><th>Nilai</th><th>Satuan</th><th>Referensi</th><th>Kesimpulan</th><th>Saran</th><th>Ringkasan</th><th>Aksi</th></tr></thead><tbody data-abnormal-values></tbody></table></div>
      </td>`;

    additionalRows.append(row, matrixRow);
    const valueFor = (name, fallback = "") => values[name] ?? fallback;
    row.querySelector('[data-additional-field="name"]').value = valueFor("name");
    row.querySelector('[data-additional-field="code"]').value = valueFor("code");
    row.querySelector('[data-additional-field="result_type"]').value = valueFor("result_type", field("result_type").value);
    row.querySelector('[data-additional-field="price"]').value = valueFor("price", "");
    row.querySelector('[data-additional-field="method"]').value = valueFor("method", field("method").value);
    row.querySelector('[data-additional-field="unit"]').value = valueFor("unit", field("unit").value);
    row.querySelector('[data-additional-field="is_active"]').value = String(valueFor("is_active", field("is_active").value));

    const nestedNormalRows = matrixRow.querySelector("[data-normal-values]");
    const nestedAbnormalRows = matrixRow.querySelector("[data-abnormal-values]");
    (values.normal_values || []).forEach((value) => addNormalValueRow(nestedNormalRows, value, ""));
    (values.abnormal_values || []).forEach((value) => addAbnormalValueRow(nestedAbnormalRows, value, ""));
    matrixRow.querySelector("[data-add-normal]").addEventListener("click", () => { addNormalValueRow(nestedNormalRows, {}, ""); renumberAllRows(); });
    matrixRow.querySelector("[data-add-abnormal]").addEventListener("click", () => { addAbnormalValueRow(nestedAbnormalRows, {}, ""); renumberAllRows(); });

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
      renumberAllRows();
    });

    renumberAllRows();
    refreshFields();
  };

  form.querySelectorAll("[data-currency]").forEach(bindCurrency);
  form.addEventListener("submit", () => {
    form.querySelectorAll("[data-currency]").forEach((input) => { input.value = input.value.replace(/\D/g, ""); });
  });

  (initial.normal_values || []).forEach((value) => addNormalValueRow(normalValuesRows, value));
  (initial.abnormal_values || []).forEach((value) => addAbnormalValueRow(abnormalValuesRows, value));
  (initial.additional || []).forEach(addAdditionalRow);
  refreshMainEmptyState();

  document.getElementById("addNormalValue").addEventListener("click", () => addNormalValueRow(normalValuesRows));
  document.getElementById("addAbnormalValue").addEventListener("click", () => addAbnormalValueRow(abnormalValuesRows));
  document.getElementById("addExaminationRow")?.addEventListener("click", () => addAdditionalRow());
  ["node_type", "header_type", "is_orderable"].forEach((name) => field(name).addEventListener("change", refreshFields));
  refreshFields();

  const errorKeys = initial.error_keys || [];
  const errorTab = errorKeys.some((key) => key.startsWith("additional")) ? "bulk-tab"
    : errorKeys.some((key) => key.startsWith("abnormal_values")) ? "abnormal-tab"
      : errorKeys.some((key) => key.startsWith("normal_values")) ? "normal-tab"
        : errorKeys.some((key) => ["result_type", "is_orderable", "price", "unit", "method"].includes(key)) ? "result-tab"
          : null;
  if (errorTab && document.getElementById(errorTab)) bootstrap.Tab.getOrCreateInstance(document.getElementById(errorTab)).show();
});
