document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("appSidebar");
  const sidebarToggle = document.getElementById("sidebarToggle");

  const setSidebarState = (isCollapsed) => {
    document.body.classList.toggle("sidebar-collapsed", isCollapsed);
    sidebarToggle?.setAttribute("aria-expanded", String(!isCollapsed));
    sidebarToggle?.setAttribute(
      "aria-label",
      isCollapsed ? "Buka menu" : "Tutup menu",
    );
    localStorage.setItem("sidebar-collapsed", String(isCollapsed));
  };

  if (window.innerWidth >= 992) {
    setSidebarState(localStorage.getItem("sidebar-collapsed") === "true");
  }

  sidebarToggle?.addEventListener("click", () => {
    if (!sidebar) return;
    if (window.innerWidth < 992) {
      sidebar.classList.toggle("show");
      sidebarToggle.setAttribute(
        "aria-expanded",
        String(sidebar.classList.contains("show")),
      );
      return;
    }
    setSidebarState(!document.body.classList.contains("sidebar-collapsed"));
  });
  document.addEventListener("click", (event) => {
    if (
      window.innerWidth < 992 &&
      sidebar?.classList.contains("show") &&
      !sidebar.contains(event.target) &&
      !sidebarToggle?.contains(event.target)
    ) {
      sidebar.classList.remove("show");
      sidebarToggle?.setAttribute("aria-expanded", "false");
    }
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth >= 992) {
      sidebar?.classList.remove("show");
      setSidebarState(localStorage.getItem("sidebar-collapsed") === "true");
    } else {
      document.body.classList.remove("sidebar-collapsed");
    }
  });

  const sectionLinks = document.querySelectorAll('.app-nav a[href^="#"]');
  sectionLinks.forEach((link) => {
    link.addEventListener("click", () => {
      document
        .querySelectorAll(".app-nav a")
        .forEach((item) => item.classList.remove("active"));
      link.classList.add("active");
      if (window.innerWidth < 992) sidebar?.classList.remove("show");
    });
  });

  document.querySelectorAll(".app-nav a").forEach((link) => {
    const label = link.textContent.trim();
    if (label && !link.hasAttribute("data-tooltip")) {
      link.setAttribute("data-tooltip", label);
    }
    if (!link.hasAttribute("aria-label"))
      link.setAttribute("aria-label", label);
    if (!link.hasAttribute("title")) link.setAttribute("title", label);
  });

  // Reusable table controls: pencarian, filter status, dan bulk selection.
  const tableSearch = document.getElementById("tableSearch");
  const statusFilter = document.getElementById("statusFilter");
  const patientRows = [...document.querySelectorAll("#patientTable tbody tr")];
  const emptyState = document.getElementById("emptyState");
  const visibleRows = document.getElementById("visibleRows");

  const filterTable = () => {
    const keyword = tableSearch?.value.toLowerCase().trim() || "";
    const status = statusFilter?.value || "all";
    let visible = 0;
    patientRows.forEach((row) => {
      const matchesText = row.textContent.toLowerCase().includes(keyword);
      const matchesStatus = status === "all" || row.dataset.status === status;
      row.classList.toggle("d-none", !(matchesText && matchesStatus));
      if (matchesText && matchesStatus) visible += 1;
    });
    visibleRows && (visibleRows.textContent = visible);
    emptyState?.classList.toggle("d-none", visible !== 0);
  };
  tableSearch?.addEventListener("input", filterTable);
  statusFilter?.addEventListener("change", filterTable);

  const selectAll = document.getElementById("selectAll");
  const rowChecks = [...document.querySelectorAll(".row-check")];
  const bulkBar = document.getElementById("bulkBar");
  const selectedCount = document.getElementById("selectedCount");
  const updateSelection = () => {
    const count = rowChecks.filter((checkbox) => checkbox.checked).length;
    selectedCount && (selectedCount.textContent = count);
    bulkBar?.classList.toggle("d-none", count === 0);
    if (selectAll)
      selectAll.indeterminate = count > 0 && count < rowChecks.length;
  };
  selectAll?.addEventListener("change", () => {
    rowChecks.forEach((checkbox) => {
      checkbox.checked = selectAll.checked;
    });
    updateSelection();
  });
  rowChecks.forEach((checkbox) =>
    checkbox.addEventListener("change", updateSelection),
  );

  document.getElementById("exportTable")?.addEventListener("click", () => {
    const table = document.getElementById("patientTable");
    if (!table) return;
    const lines = [...table.querySelectorAll("tr")]
      .map((row) =>
        [...row.querySelectorAll("th, td")]
          .slice(1, -1)
          .map((cell) => `"${cell.innerText.trim().replaceAll('"', '""')}"`)
          .join(","),
      )
      .filter(Boolean);
    const blob = new Blob(["\uFEFF" + lines.join("\n")], {
      type: "text/csv;charset=utf-8;",
    });
    const url = URL.createObjectURL(blob);
    const download = document.createElement("a");
    download.href = url;
    download.download = "data-pendaftaran.csv";
    download.click();
    URL.revokeObjectURL(url);
  });

  document.querySelectorAll("[data-page-number]").forEach((button) => {
    button.addEventListener("click", () => {
      button
        .closest(".pagination")
        ?.querySelectorAll(".page-item")
        .forEach((item) => item.classList.remove("active"));
      button.closest(".page-item")?.classList.add("active");
      button
        .closest(".pagination")
        ?.querySelectorAll("[data-page-number]")
        .forEach((item) => item.removeAttribute("aria-current"));
      button.setAttribute("aria-current", "page");
    });
  });

  const form = document.getElementById("registrationForm");
  const formContent = document.getElementById("formContent");
  const successPanel = document.getElementById("successPanel");
  const submitButton = document.getElementById("submitRegistration");

  form?.addEventListener("submit", (event) => {
    event.preventDefault();
    event.stopPropagation();
    form.classList.add("was-validated");
    if (!form.checkValidity()) return;

    submitButton.disabled = true;
    submitButton.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
    window.setTimeout(() => {
      formContent.classList.add("d-none");
      successPanel.classList.remove("d-none");
    }, 650);
  });
});
