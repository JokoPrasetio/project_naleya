document.addEventListener("DOMContentLoaded", () => {
  const overlay = document.getElementById("pageLoading");
  const main = document.querySelector("main");

  if (!overlay) return;

  const showLoading = () => {
    overlay.hidden = false;
    main?.setAttribute("aria-busy", "true");
  };

  const hideLoading = () => {
    overlay.hidden = true;
    main?.removeAttribute("aria-busy");
  };

  window.addEventListener("pageshow", hideLoading);

  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) return;

    const link = event.target.closest("a[href]");

    if (
      !link ||
      event.defaultPrevented ||
      event.button !== 0 ||
      event.metaKey ||
      event.ctrlKey ||
      event.shiftKey ||
      event.altKey ||
      link.hasAttribute("download") ||
      link.hasAttribute("data-bs-toggle") ||
      (link.target && link.target !== "_self")
    ) {
      return;
    }

    const destination = new URL(link.href, window.location.href);
    const current = new URL(window.location.href);

    if (
      !["http:", "https:"].includes(destination.protocol) ||
      destination.origin !== current.origin ||
      (destination.pathname === current.pathname &&
        destination.search === current.search &&
        destination.hash)
    ) {
      return;
    }

    showLoading();
  });

  document.addEventListener("submit", (event) => {
    if (
      event.defaultPrevented ||
      event.target.hasAttribute("data-no-page-loading") ||
      (event.target.target && event.target.target !== "_self")
    ) {
      return;
    }

    showLoading();
  });
});
