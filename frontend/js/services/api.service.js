// frontend/js/services/api.service.js
window.ApiService = (function () {
  async function apiFetch(path, options = {}) {
    const token = window.AuthService?.getToken?.();

    const headers = Object.assign(
      { "Content-Type": "application/json" },
      options.headers || {}
    );

    if (token) headers["Authorization"] = "Bearer " + token;

    const res = await fetch(window.API_BASE + path, {
      ...options,
      headers
    });

    const text = await res.text();
    let data = null;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (_) {
      data = text;
    }

    if (!res.ok) {
      let msg = "HTTP " + res.status;
      if (data && typeof data === "object") {
        msg = data.error || data.message || JSON.stringify(data);
      } else if (typeof data === "string" && data.trim()) {
        msg = data;
      }
      throw new Error(msg);
    }

    return data;
  }

  function normalizeArray(res) {
    if (Array.isArray(res)) return res;
    if (res && Array.isArray(res.data)) return res.data;
    if (res && Array.isArray(res.items)) return res.items;
    if (res && Array.isArray(res.rows)) return res.rows;
    if (res && Array.isArray(res.result)) return res.result;
    return [];
  }

  // for console debugging (optional)
  window.apiFetch = apiFetch;
  window.normalizeArray = normalizeArray;

  return { apiFetch, normalizeArray };
})();
