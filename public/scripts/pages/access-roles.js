document.addEventListener("DOMContentLoaded", () => {
  const modalElement = document.getElementById("roleModal");
  if (!modalElement) return;

  const form = document.getElementById("roleForm");
  const method = document.getElementById("roleFormMethod");
  const editingId = document.getElementById("editingRoleId");
  const title = document.getElementById("roleModalTitle");
  const name = document.getElementById("roleName");
  const slug = document.getElementById("roleSlug");
  const slugGroup = document.getElementById("roleSlugGroup");
  const permissions = [...form.querySelectorAll('input[name="permissions[]"]')];
  const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

  const setMode = (role = null, preserveValues = false) => {
    const isEdit = role !== null;

    if (!preserveValues) {
      form.reset();
      name.value = role?.name ?? "";
      slug.value = "";
      const selected = role?.permissions ?? [];
      permissions.forEach((checkbox) => {
        checkbox.checked = selected.includes(checkbox.value);
      });
    }

    method.disabled = !isEdit;
    editingId.value = isEdit ? String(role.id) : "";
    form.action = isEdit
      ? `${form.dataset.updateBase}/${Number(role.id)}`
      : form.dataset.storeUrl;
    title.textContent = isEdit ? "Ubah Peran" : "Tambah Peran";
    slugGroup.hidden = isEdit;
    slug.disabled = isEdit;
    slug.required = !isEdit;
  };

  document.getElementById("addRoleButton")?.addEventListener("click", () => {
    setMode();
    modal.show();
  });

  document.querySelectorAll("[data-edit-role]").forEach((button) => {
    button.addEventListener("click", () => {
      setMode({
        id: button.dataset.roleId,
        name: button.dataset.roleName,
        permissions: button.dataset.permissions
          ? button.dataset.permissions.split(",")
          : [],
      });
      modal.show();
    });
  });

  const deleteModalElement = document.getElementById('deleteRoleModal');
  const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalElement);
  const deleteForm = document.getElementById('deleteRoleForm');
  const deleteName = document.getElementById('deleteRoleName');
  const deleteMessage = document.getElementById('deleteRoleMessage');
  const confirmDelete = document.getElementById('confirmDeleteRole');

  document.querySelectorAll('[data-delete-role]').forEach((button) => {
    button.addEventListener('click', () => {
      const usersCount = Number(button.dataset.usersCount);
      deleteName.textContent = button.dataset.roleName;
      deleteForm.action = button.dataset.deleteUrl;
      confirmDelete.disabled = usersCount > 0;
      deleteMessage.textContent = usersCount > 0
        ? `Peran ini masih dipakai oleh ${usersCount} pengguna. Pindahkan mereka ke peran lain sebelum menghapus.`
        : 'Tindakan ini tidak dapat dibatalkan.';
      deleteModal.show();
    });
  });

  if (modalElement.dataset.hasErrors === "1") {
    const failedId = Number(modalElement.dataset.editingId);
    setMode(failedId ? { id: failedId } : null, true);
    modal.show();
  }
});
