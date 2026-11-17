document.addEventListener("DOMContentLoaded", () => {
  const toastElement = document.getElementById("registrationToast");
  const toast = bootstrap.Toast.getOrCreateInstance(toastElement);
  const toastTitle = toastElement.querySelector("strong");
  const toastMessage = toastElement.querySelector("small");

  const showToast = (title, message) => {
    toastTitle.textContent = title;
    toastMessage.textContent = message;
    toast.show();
  };

  document
    .getElementById("printRegistration")
    ?.addEventListener("click", () => window.print());

  document
    .getElementById("confirmCheckin")
    ?.addEventListener("click", (event) => {
      const button = event.currentTarget;
      button.disabled = true;
      button.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
      window.setTimeout(() => {
        button.className = "btn btn-light registration-checkin";
        button.innerHTML =
          '<i class="bi bi-check-circle-fill me-2"></i>Pasien sudah hadir';
        document.getElementById("registrationStatus").textContent =
          "Pasien hadir";
        document.getElementById("registrationStatus").className =
          "status confirmed";
        showToast(
          "Kehadiran dikonfirmasi",
          "Status pasien berhasil diperbarui menjadi hadir.",
        );
      }, 650);
    });

  document
    .getElementById("rescheduleForm")
    ?.addEventListener("submit", (event) => {
      event.preventDefault();
      const form = event.currentTarget;
      form.classList.add("was-validated");
      if (!form.checkValidity()) return;
      const button = form.querySelector('[type="submit"]');
      button.disabled = true;
      button.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
      window.setTimeout(() => {
        bootstrap.Modal.getInstance(
          document.getElementById("rescheduleModal"),
        )?.hide();
        button.disabled = false;
        button.textContent = "Simpan jadwal";
        showToast(
          "Jadwal berhasil diubah",
          "Notifikasi perubahan jadwal siap dikirim kepada pasien.",
        );
      }, 700);
    });

  document
    .getElementById("cancelRegistrationForm")
    ?.addEventListener("submit", (event) => {
      event.preventDefault();
      const form = event.currentTarget;
      form.classList.add("was-validated");
      if (!form.checkValidity()) return;
      const button = form.querySelector('[type="submit"]');
      button.disabled = true;
      button.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Membatalkan...';
      window.setTimeout(() => {
        bootstrap.Modal.getInstance(
          document.getElementById("cancelRegistrationModal"),
        )?.hide();
        button.disabled = false;
        button.textContent = "Batalkan pendaftaran";
        const status = document.getElementById("registrationStatus");
        status.textContent = "Dibatalkan";
        status.className = "status waiting";
        showToast(
          "Pendaftaran dibatalkan",
          "Status dan riwayat aktivitas telah diperbarui.",
        );
      }, 700);
    });
});
