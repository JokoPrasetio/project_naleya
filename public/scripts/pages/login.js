document.addEventListener("DOMContentLoaded", () => {
  const passwordInput = document.getElementById("password");
  const toggleButton = document.getElementById("passwordToggle");
  const icon = toggleButton?.querySelector("i");

  toggleButton?.addEventListener("click", () => {
    if (!passwordInput) return;
    const isVisible = passwordInput.type === "password";
    passwordInput.type = isVisible ? "text" : "password";
    icon?.classList.toggle("bi-eye", !isVisible);
    icon?.classList.toggle("bi-eye-slash", isVisible);
    passwordInput.focus({ preventScroll: true });
  });
});
