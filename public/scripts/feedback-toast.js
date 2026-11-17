document.addEventListener('DOMContentLoaded', () => {
  const toast = document.getElementById('feedbackToast');
  if (toast) {
    bootstrap.Toast.getOrCreateInstance(toast).show();
  }
});
