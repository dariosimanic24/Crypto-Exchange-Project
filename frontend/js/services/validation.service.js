// frontend/js/services/validation.service.js
window.ValidationService = (function () {
  function isEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || "").trim());
  }

  function required(value) {
    return String(value || "").trim().length > 0;
  }

  function minLen(value, n) {
    return String(value || "").length >= n;
  }

  return { isEmail, required, minLen };
})();
