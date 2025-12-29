// frontend/js/services/auth.service.js
window.AuthService = (function () {
  function setAuth(token, user) {
    localStorage.setItem("token", token);
    localStorage.setItem("user", JSON.stringify(user));
  }

  function clearAuth() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
  }

  function getToken() {
    return localStorage.getItem("token");
  }

  function getUser() {
    try {
      return JSON.parse(localStorage.getItem("user") || "null");
    } catch (e) {
      return null;
    }
  }

  function isLoggedIn() {
    return !!getToken();
  }

  function isAdmin() {
    const u = getUser();
    return !!u && Number(u.is_admin) === 1;
  }

  return { setAuth, clearAuth, getToken, getUser, isLoggedIn, isAdmin };
})();
