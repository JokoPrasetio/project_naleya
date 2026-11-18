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
});
