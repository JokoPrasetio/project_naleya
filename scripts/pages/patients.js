document.addEventListener("DOMContentLoaded", () => {
  const tabs = [...document.querySelectorAll("[data-patient-tab]")];
  const panels = [...document.querySelectorAll("[data-patient-panel]")];
  const formFooter = document.querySelector("[data-patient-panel-footer]");

  tabs.forEach((tab) =>
    tab.addEventListener("click", () => {
      tabs.forEach((item) => item.classList.remove("active"));
      panels.forEach((panel) => panel.classList.remove("active"));
      tab.classList.add("active");
      document
        .querySelector(`[data-patient-panel="${tab.dataset.patientTab}"]`)
        ?.classList.add("active");
      formFooter?.classList.toggle("d-none", tab.dataset.patientTab !== "form");
    }),
  );

  const form = document.getElementById("patientForm");
  const savingOverlay = document.getElementById("patientSavingOverlay");
  const successToast = bootstrap.Toast.getOrCreateInstance(
    document.getElementById("patientSuccessToast"),
  );

  form?.addEventListener("submit", (event) => {
    event.preventDefault();
    event.stopPropagation();
    form.classList.add("was-validated");
    if (!form.checkValidity()) return;
    savingOverlay.classList.remove("d-none");
    window.setTimeout(() => {
      savingOverlay.classList.add("d-none");
      successToast.show();
    }, 800);
  });

  document.getElementById("resetPatientForm")?.addEventListener("click", () => {
    form.reset();
    form.classList.remove("was-validated");
  });

  document
    .getElementById("confirmPatientDelete")
    ?.addEventListener("click", (event) => {
      const button = event.currentTarget;
      button.disabled = true;
      button.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Menghapus...';
      window.setTimeout(() => {
        bootstrap.Modal.getInstance(
          document.getElementById("deletePatientModal"),
        )?.hide();
        button.disabled = false;
        button.innerHTML = "Ya, hapus";
        const toast = document.getElementById("actionToast");
        toast.querySelector("strong").textContent = "Data berhasil dihapus";
        toast.querySelector("small").textContent =
          "Contoh proses hapus sudah selesai.";
        bootstrap.Toast.getOrCreateInstance(toast).show();
      }, 700);
    });
});
