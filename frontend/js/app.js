$(function () {

  // ====== HELPERS ======
  function escapeHtml(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  // ====== ADMIN-ONLY UI TOGGLE ======
  function toggleAdminOnlyUI() {
    const admin = AuthService.isAdmin();

    document.querySelectorAll(".admin-only").forEach(el => {
      if (!admin) {
        el.style.setProperty("display", "none", "important");
        return;
      }

      const tag = (el.tagName || "").toUpperCase();

      let d = "block";
      if (tag === "A" || tag === "SPAN" || tag === "BUTTON") d = "inline-flex";
      if (tag === "TR") d = "table-row";
      if (tag === "TD" || tag === "TH") d = "table-cell";
      if (tag === "DIV") d = "block";

      el.style.setProperty("display", d, "important");
    });
  }

  // ====== NAV / ROLE UI ======
  function show(id, visible) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = visible ? "" : "none";
  }

  function updateNavByRole() {
    const logged = AuthService.isLoggedIn();

    // Guest
    show("navLogin", !logged);
    show("navRegister", !logged);

    // Logged
    show("navDashboard", logged);
    show("navExchange", logged);
    show("navWallets", logged);
    show("navOrders", logged);
    show("navTransactions", logged);
    show("navProfile", logged);
    show("navCurrencies", logged);
    show("navLogout", logged);

    // Admin-only nav link
    show("navAdmin", logged && AuthService.isAdmin());

    // Username label
    const user = AuthService.getUser();
    const nameEl = document.getElementById("navUserName");
    if (nameEl) {
      nameEl.textContent = logged && user ? ("Hi, " + user.name) : "";
      nameEl.style.display = logged && user ? "" : "none";
    }

    toggleAdminOnlyUI();
  }

  // ====== ROUTE GUARDS ======
  function guardRoute(routeName) {
    const protectedRoutes = ["dashboard", "exchange", "wallets", "orders", "transactions", "profile", "admin", "currencies"];
    const guestOnlyRoutes = ["login", "register"];
    const adminRoutes = ["admin"];

    if (protectedRoutes.includes(routeName) && !AuthService.isLoggedIn()) {
      window.location.hash = "#login";
      return false;
    }

    if (guestOnlyRoutes.includes(routeName) && AuthService.isLoggedIn()) {
      window.location.hash = "#dashboard";
      return false;
    }

    if (adminRoutes.includes(routeName) && (!AuthService.isLoggedIn() || !AuthService.isAdmin())) {
      window.location.hash = "#dashboard";
      return false;
    }

    return true;
  }

  // ====== AUTH HANDLERS (with client-side validations) ======
  async function handleLoginSubmit(e) {
    e.preventDefault();

    const email = document.getElementById("loginEmail").value.trim();
    const password = document.getElementById("loginPassword").value.trim();

    if (!ValidationService.required(email) || !ValidationService.isEmail(email)) {
      alert("Please enter a valid email.");
      return;
    }
    if (!ValidationService.required(password)) {
      alert("Password is required.");
      return;
    }

    try {
      const result = await ApiService.apiFetch("/auth/login", {
        method: "POST",
        body: JSON.stringify({ email, password })
      });

      AuthService.setAuth(result.token, result.user);
      updateNavByRole();
      window.location.hash = "#dashboard";
    } catch (err) {
      alert(err.message);
    }
  }

  async function handleRegisterSubmit(e) {
    e.preventDefault();

    const name = document.getElementById("regName").value.trim();
    const email = document.getElementById("regEmail").value.trim();
    const password = document.getElementById("regPassword").value.trim();
    const confirm = document.getElementById("regPassword2").value.trim();

    if (!ValidationService.required(name)) {
      alert("Name is required.");
      return;
    }
    if (!ValidationService.required(email) || !ValidationService.isEmail(email)) {
      alert("Please enter a valid email.");
      return;
    }
    if (!ValidationService.minLen(password, 8)) {
      alert("Password must be at least 8 characters.");
      return;
    }
    if (password !== confirm) {
      alert("Passwords do not match.");
      return;
    }

    try {
      await ApiService.apiFetch("/users/register", {
        method: "POST",
        body: JSON.stringify({ name, email, password })
      });

      alert("Registered successfully. Now login.");
      window.location.hash = "#login";
    } catch (err) {
      alert(err.message);
    }
  }

  function handleLogoutClick(e) {
    e.preventDefault();
    AuthService.clearAuth();
    updateNavByRole();
    window.location.hash = "#home";
  }

  // ====== PROFILE (GET /auth/me) ======
  async function loadProfile() {
    const loading = document.getElementById("profileLoading");
    const grid = document.getElementById("profileData");
    if (!loading || !grid) return;

    loading.style.display = "";
    loading.textContent = "Loading...";
    grid.style.display = "none";

    try {
      const me = await ApiService.apiFetch("/auth/me", { method: "GET" });

      const elEmail = document.getElementById("pfEmail");
      const elName = document.getElementById("pfName");
      const elRole = document.getElementById("pfRole");
      const elId = document.getElementById("pfId");
      const elCreated = document.getElementById("pfCreated");

      if (elEmail) elEmail.textContent = me.email || "-";
      if (elName) elName.textContent = me.name || "-";
      if (elId) elId.textContent = (me.id ?? "-");
      if (elCreated) elCreated.textContent = me.created_at || "-";
      if (elRole) elRole.textContent = Number(me.is_admin) === 1 ? "Admin" : "User";

      loading.style.display = "none";
      grid.style.display = "grid";
    } catch (err) {
      loading.textContent = "Error: " + err.message;
    }
  }

  // ====== ADMIN PANEL ======
  async function loadAdminPanel() {
    const info = document.getElementById("adminInfo");
    if (!info) return;

    info.textContent = "Loading...";

    try {
      const me = await ApiService.apiFetch("/auth/me", { method: "GET" });

      const users = ApiService.normalizeArray(await ApiService.apiFetch("/users", { method: "GET" }));
      const wallets = ApiService.normalizeArray(await ApiService.apiFetch("/wallets", { method: "GET" }));
      const orders = ApiService.normalizeArray(await ApiService.apiFetch("/orders", { method: "GET" }));

      const uCount = document.getElementById("adminUsersCount");
      const wCount = document.getElementById("adminWalletsCount");
      const oCount = document.getElementById("adminOrdersCount");

      if (uCount) uCount.textContent = users.length;
      if (wCount) wCount.textContent = wallets.length;
      if (oCount) oCount.textContent = orders.length;

      info.textContent = `Logged in as: ${me.email} (Admin)`;
    } catch (err) {
      info.textContent = "Error: " + err.message;
    }
  }

  // ====== CURRENCIES ======
  async function loadCurrencies() {
    const tbody = document.getElementById("currenciesTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="5" class="muted">Loading...</td></tr>`;

    try {
      const items = ApiService.normalizeArray(await ApiService.apiFetch("/currencies", { method: "GET" }));

      if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="muted">No currencies found.</td></tr>`;
        toggleAdminOnlyUI();
        return;
      }

      const rows = items.map(c => {
        const id = c.id ?? "-";
        const code = c.code ?? "-";
        const name = c.name ?? "-";
        const decimals = c.decimals ?? "-";

        return `
          <tr>
            <td>${escapeHtml(id)}</td>
            <td>${escapeHtml(code)}</td>
            <td>${escapeHtml(name)}</td>
            <td>${escapeHtml(decimals)}</td>
            <td class="admin-only" style="display:none;">
              <button class="btn ghost btnEditCurrency"
                data-id="${escapeHtml(id)}"
                data-code="${escapeHtml(code)}"
                data-name="${escapeHtml(name)}"
                data-decimals="${escapeHtml(decimals)}">Edit</button>

              <button class="btn ghost btnDeleteCurrency"
                data-id="${escapeHtml(id)}"
                style="margin-left:8px;">Delete</button>
            </td>
          </tr>
        `;
      }).join("");

      tbody.innerHTML = rows;
      toggleAdminOnlyUI();
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="5" class="muted">Error: ${escapeHtml(err.message)}</td></tr>`;
      toggleAdminOnlyUI();
    }
  }

  async function createCurrency() {
    try {
      const code = (prompt("code (e.g. BTC):") || "").trim().toUpperCase();
      const name = (prompt("name (e.g. Bitcoin):") || "").trim();
      const decimals = Number(prompt("decimals (integer, e.g. 8):", "8"));

      if (!code || !name || !Number.isFinite(decimals)) {
        alert("Missing/invalid fields.");
        return;
      }

      await ApiService.apiFetch("/currencies", {
        method: "POST",
        body: JSON.stringify({ code, name, decimals })
      });

      alert("Currency created.");
      loadCurrencies();
    } catch (err) {
      alert(err.message);
    }
  }

  async function editCurrency(id, oldCode, oldName, oldDecimals) {
    try {
      const name = (prompt("name:", oldName) || "").trim();
      const decimalsRaw = prompt("decimals:", String(oldDecimals));

      const payload = {};
      if (name) payload.name = name;

      if (decimalsRaw !== null && decimalsRaw !== "") {
        const d = Number(decimalsRaw);
        if (!Number.isFinite(d)) {
          alert("Invalid decimals.");
          return;
        }
        payload.decimals = d;
      }

      if (Object.keys(payload).length === 0) return;

      await ApiService.apiFetch("/currencies/" + id, {
        method: "PUT",
        body: JSON.stringify(payload)
      });

      alert("Currency updated.");
      loadCurrencies();
    } catch (err) {
      alert(err.message);
    }
  }

  async function deleteCurrency(id) {
    if (!confirm("Delete currency #" + id + "?")) return;

    try {
      await ApiService.apiFetch("/currencies/" + id, { method: "DELETE" });
      alert("Deleted.");
      loadCurrencies();
    } catch (err) {
      alert(err.message);
    }
  }

  // ====== EXCHANGE ======
  let _walletsCache = [];
  let _currenciesCache = [];

  async function loadExchange() {
    const selFrom = document.getElementById("exFrom");
    const selTo = document.getElementById("exTo");
    const balFrom = document.getElementById("exFromBal");
    const balTo = document.getElementById("exToBal");
    const msg = document.getElementById("exMsg");
    if (!selFrom || !selTo) return;

    if (msg) msg.textContent = "Loading...";

    try {
      _currenciesCache = ApiService.normalizeArray(await ApiService.apiFetch("/currencies", { method: "GET" }));
      _walletsCache = ApiService.normalizeArray(await ApiService.apiFetch("/wallets", { method: "GET" }));

      if (_currenciesCache.length === 0) {
        if (msg) msg.textContent = "No currencies found (API returned empty).";
        selFrom.innerHTML = "";
        selTo.innerHTML = "";
        return;
      }

      const opts = _currenciesCache.map(c => {
        const id = (c.id != null) ? c.id : "";
        const code = c.code ?? ("CUR-" + id);
        const name = c.name ?? "";
        return `<option value="${escapeHtml(id)}">${escapeHtml(code)}${name ? " - " + escapeHtml(name) : ""}</option>`;
      }).join("");

      selFrom.innerHTML = opts;
      selTo.innerHTML = opts;

      function walletByCurrencyId(cid) {
        return (_walletsCache || []).find(w => Number(w.currency_id) === Number(cid));
      }

      function refreshBalances() {
        const fromId = Number(selFrom.value);
        const toId = Number(selTo.value);
        const wFrom = walletByCurrencyId(fromId);
        const wTo = walletByCurrencyId(toId);

        if (balFrom) balFrom.textContent = wFrom ? `Balance: ${wFrom.balance}` : "Balance: (no wallet)";
        if (balTo) balTo.textContent = wTo ? `Balance: ${wTo.balance}` : "Balance: (no wallet)";
      }

      selFrom.onchange = refreshBalances;
      selTo.onchange = refreshBalances;

      if (_currenciesCache.length > 1) {
        const toCandidate = _currenciesCache.find(x => String(x.id) !== String(selFrom.value));
        if (toCandidate) selTo.value = String(toCandidate.id);
      }

      refreshBalances();
      if (msg) msg.textContent = "";
    } catch (e) {
      if (msg) msg.textContent = "Error: " + e.message;
    }
  }

  async function executeExchange() {
    const msg = document.getElementById("exMsg");

    const fromId = Number(document.getElementById("exFrom")?.value);
    const toId = Number(document.getElementById("exTo")?.value);
    const amount = Number(document.getElementById("exAmount")?.value);
    const rate = Number(document.getElementById("exRate")?.value);

    if (!fromId || !toId || !Number.isFinite(amount) || amount <= 0 || !Number.isFinite(rate) || rate <= 0) {
      alert("Invalid input.");
      return;
    }
    if (fromId === toId) {
      alert("From and To must differ.");
      return;
    }

    if (msg) msg.textContent = "Executing...";

    try {
      const payload = {
        from_currency_id: fromId,
        to_currency_id: toId,
        amount: amount,
        rate: rate,
        fromCurrencyId: fromId,
        toCurrencyId: toId,
        exchange_rate: rate
      };

      const res = await ApiService.apiFetch("/exchange", {
        method: "POST",
        body: JSON.stringify(payload)
      });

      const received = (res && res.received != null) ? res.received : JSON.stringify(res);
      if (msg) msg.textContent = `Done. Received: ${received}`;

      await loadExchange();
    } catch (e) {
      if (msg) msg.textContent = "Error: " + e.message;
    }
  }

  // ====== WALLETS ======
  async function loadWallets() {
    const container = document.getElementById("walletsList");
    if (!container) return;

    container.innerHTML = "<div class='muted'>Loading...</div>";

    try {
      const wallets = ApiService.normalizeArray(await ApiService.apiFetch("/wallets", { method: "GET" }));

      if (wallets.length === 0) {
        container.innerHTML = "<div class='muted'>No wallets found.</div>";
        toggleAdminOnlyUI();
        return;
      }

      const html = wallets.map(w => {
        const id = w.id ?? "-";
        const balance = w.balance ?? 0;
        const currencyLabel =
          w.currency_code || w.code || w.currency || w.currency_name || w.currency_id || "Currency";

        return `
          <div class="card">
            <div class="muted">${escapeHtml(currencyLabel)}</div>
            <div style="font-size:22px;margin-top:6px;">${escapeHtml(balance)}</div>

            <div class="spacer"></div>

            <a class="btn" href="#exchange">Exchange</a>
            <a class="btn ghost" href="#transactions" style="margin-left:8px;">History</a>

            <div class="spacer"></div>

            <div class="admin-only" style="display:none;">
              <button class="btn ghost btnDeleteWallet" data-id="${escapeHtml(id)}">Delete</button>
            </div>
          </div>
        `;
      }).join("");

      container.innerHTML = `<div class="grid cols-3">${html}</div>`;
      toggleAdminOnlyUI();
    } catch (err) {
      container.innerHTML = `<div class="muted">Error: ${escapeHtml(err.message)}</div>`;
      toggleAdminOnlyUI();
    }
  }

  async function createWallet() {
    try {
      const user_id = Number(prompt("user_id (integer):"));
      const currency_id = Number(prompt("currency_id (integer):"));
      const balanceRaw = prompt("balance (number, optional):", "0");
      const balance = Number(balanceRaw);

      if (!user_id || !currency_id) {
        alert("user_id and currency_id are required.");
        return;
      }

      await ApiService.apiFetch("/wallets", {
        method: "POST",
        body: JSON.stringify({ user_id, currency_id, balance: isNaN(balance) ? 0 : balance })
      });

      alert("Wallet created.");
      loadWallets();
    } catch (err) {
      alert(err.message);
    }
  }

  async function deleteWallet(id) {
    if (!confirm("Delete wallet #" + id + "?")) return;

    try {
      await ApiService.apiFetch("/wallets/" + id, { method: "DELETE" });
      alert("Deleted.");
      loadWallets();
    } catch (err) {
      alert(err.message);
    }
  }

  // ====== ORDERS ======
  async function loadOrders() {
    const tbody = document.getElementById("ordersTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" class="muted">Loading...</td></tr>`;

    try {
      const orders = ApiService.normalizeArray(await ApiService.apiFetch("/orders", { method: "GET" }));

      if (orders.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="muted">No orders found.</td></tr>`;
        toggleAdminOnlyUI();
        return;
      }

      const rows = orders.map(o => {
        const id = o.id ?? o.order_id ?? "-";
        const side = o.side ?? o.type ?? "-";
        const amount = o.amount ?? "-";
        const price = o.price ?? "-";
        const status = o.status ?? "-";

        return `
          <tr>
            <td>${escapeHtml(id)}</td>
            <td>${escapeHtml(side)}</td>
            <td>${escapeHtml(amount)}</td>
            <td>${escapeHtml(price)}</td>
            <td><span class="badge">${escapeHtml(status)}</span></td>
            <td class="admin-only" style="display:none;">
              <button class="btn ghost btnDeleteOrder" data-id="${escapeHtml(id)}">Delete</button>
            </td>
          </tr>
        `;
      }).join("");

      tbody.innerHTML = rows;
      toggleAdminOnlyUI();
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="6" class="muted">Error: ${escapeHtml(err.message)}</td></tr>`;
      toggleAdminOnlyUI();
    }
  }

  async function createOrder() {
    try {
      const user_id = Number(prompt("user_id (integer):"));
      const base_currency_id = Number(prompt("base_currency_id (integer):"));
      const quote_currency_id = Number(prompt("quote_currency_id (integer):"));
      const side = (prompt("side (BUY/SELL):", "BUY") || "BUY").toUpperCase();
      const price = Number(prompt("price (number):"));
      const amount = Number(prompt("amount (number):"));

      if (!user_id || !base_currency_id || !quote_currency_id || !side || isNaN(price) || isNaN(amount)) {
        alert("Missing/invalid fields.");
        return;
      }

      await ApiService.apiFetch("/orders", {
        method: "POST",
        body: JSON.stringify({ user_id, base_currency_id, quote_currency_id, side, price, amount })
      });

      alert("Order created.");
      loadOrders();
    } catch (err) {
      alert(err.message);
    }
  }

  async function deleteOrder(id) {
    if (!confirm("Delete order #" + id + "?")) return;

    try {
      await ApiService.apiFetch("/orders/" + id, { method: "DELETE" });
      alert("Deleted.");
      loadOrders();
    } catch (err) {
      alert(err.message);
    }
  }

  // ====== TRANSACTIONS ======
  async function loadTransactions() {
    const tbody = document.getElementById("transactionsTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" class="muted">Loading...</td></tr>`;

    try {
      const txs = ApiService.normalizeArray(await ApiService.apiFetch("/transactions", { method: "GET" }));

      if (txs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="muted">No transactions found.</td></tr>`;
        toggleAdminOnlyUI();
        return;
      }

      const rows = txs.map(t => {
        const id = t.id ?? "-";
        const date = t.created_at ?? t.date ?? "-";
        const type = t.type ?? "-";
        const currency = t.currency_code || t.currency || t.currency_id || "-";
        const amount = t.amount ?? "-";
        const status = t.status ?? "-";

        return `
          <tr>
            <td>${escapeHtml(date)}</td>
            <td>${escapeHtml(type)}</td>
            <td>${escapeHtml(currency)}</td>
            <td>${escapeHtml(amount)}</td>
            <td><span class="badge">${escapeHtml(status)}</span></td>
            <td class="admin-only" style="display:none;">
              <button class="btn ghost btnDeleteTransaction" data-id="${escapeHtml(id)}">Delete</button>
            </td>
          </tr>
        `;
      }).join("");

      tbody.innerHTML = rows;
      toggleAdminOnlyUI();
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="6" class="muted">Error: ${escapeHtml(err.message)}</td></tr>`;
      toggleAdminOnlyUI();
    }
  }

  async function createTransaction() {
    try {
      const wallet_id = Number(prompt("wallet_id (integer):"));
      const type = (prompt("type (DEPOSIT/WITHDRAWAL/TRADE):", "DEPOSIT") || "DEPOSIT").toUpperCase();
      const amount = Number(prompt("amount (number):"));

      if (!wallet_id || !type || isNaN(amount)) {
        alert("Missing/invalid fields.");
        return;
      }

      await ApiService.apiFetch("/transactions", {
        method: "POST",
        body: JSON.stringify({ wallet_id, type, amount })
      });

      alert("Transaction created.");
      loadTransactions();
    } catch (err) {
      alert(err.message);
    }
  }

  async function deleteTransaction(id) {
    if (!confirm("Delete transaction #" + id + "?")) return;

    try {
      await ApiService.apiFetch("/transactions/" + id, { method: "DELETE" });
      alert("Deleted.");
      loadTransactions();
    } catch (err) {
      alert(err.message);
    }
  }

  // ====== SPAPP ======
  var app = $.spapp({
    defaultView: "#home",
    templateDir: "views/",
    pageNotFound: false
  });

  function applyTheme(theme) {
    var t = (theme === "light") ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", t);
    localStorage.setItem("theme", t);
  }

  applyTheme(localStorage.getItem("theme") || "dark");

  app.route({
    view: "profile",
    onReady: function () {
      if (!guardRoute("profile")) return;
      updateNavByRole();
      loadProfile();

      var sel = document.getElementById("theme-select");
      var btn = document.getElementById("save-theme");
      if (!sel || !btn) return;

      var current = localStorage.getItem("theme") || "dark";
      sel.value = current;

      btn.onclick = function () {
        applyTheme(sel.value);
      };
    }
  });

  app.route({
    view: "admin",
    onReady: function () {
      if (!guardRoute("admin")) return;
      updateNavByRole();
      loadAdminPanel();
    }
  });

  app.route({
    view: "currencies",
    onReady: function () {
      if (!guardRoute("currencies")) return;
      updateNavByRole();
      loadCurrencies();
    }
  });

  function loadTradingViewScript(cb) {
    if (window.TradingView && typeof window.TradingView.widget === "function") { cb && cb(); return; }
    var s = document.createElement("script");
    s.src = "https://s3.tradingview.com/tv.js";
    s.onload = function () { cb && cb(); };
    document.head.appendChild(s);
  }

  function renderTVChart() {
    var el = document.getElementById("tv-chart");
    if (!el) return;
    el.innerHTML = "";
    new TradingView.widget({
      container_id: "tv-chart",
      autosize: true,
      symbol: "BINANCE:BTCUSDT",
      interval: "60",
      timezone: "Etc/UTC",
      theme: (localStorage.getItem("theme") === "light") ? "light" : "dark",
      style: "1",
      locale: "en",
      withdateranges: true,
      hide_top_toolbar: false,
      hide_legend: false,
      allow_symbol_change: true
    });
  }

  app.route({
    view: "home",
    onReady: function () {
      updateNavByRole();
      loadTradingViewScript(renderTVChart);
    }
  });

  app.route({
    view: "login",
    onReady: function () {
      if (!guardRoute("login")) return;
      updateNavByRole();
    }
  });

  app.route({
    view: "register",
    onReady: function () {
      if (!guardRoute("register")) return;
      updateNavByRole();
    }
  });

  ["dashboard", "exchange", "wallets", "orders", "transactions", "currencies"].forEach(function (v) {
    app.route({
      view: v,
      onReady: function () {
        if (!guardRoute(v)) return;
        updateNavByRole();
        toggleAdminOnlyUI();

        if (v === "exchange") loadExchange();
        if (v === "wallets") loadWallets();
        if (v === "orders") loadOrders();
        if (v === "transactions") loadTransactions();
        if (v === "currencies") loadCurrencies();
      }
    });
  });

  // ====== GLOBAL LISTENERS ======
  $(document).on("submit", "#loginForm", handleLoginSubmit);
  $(document).on("click", "#loginBtn", handleLoginSubmit);

  $(document).on("submit", "#registerForm", handleRegisterSubmit);
  $(document).on("click", "#registerBtn", handleRegisterSubmit);

  $(document).on("click", "#navLogout", handleLogoutClick);

  $(document).on("click", "#btnExchange", executeExchange);

  $(document).on("click", "#btnCreateWallet", createWallet);
  $(document).on("click", ".btnDeleteWallet", function () {
    deleteWallet($(this).data("id"));
  });

  $(document).on("click", "#btnCreateOrder", createOrder);
  $(document).on("click", ".btnDeleteOrder", function () {
    deleteOrder($(this).data("id"));
  });

  $(document).on("click", "#btnCreateTransaction", createTransaction);
  $(document).on("click", ".btnDeleteTransaction", function () {
    deleteTransaction($(this).data("id"));
  });

  $(document).on("click", "#btnCreateCurrency", createCurrency);
  $(document).on("click", ".btnDeleteCurrency", function () {
    deleteCurrency($(this).data("id"));
  });
  $(document).on("click", ".btnEditCurrency", function () {
    editCurrency(
      $(this).data("id"),
      $(this).data("code"),
      $(this).data("name"),
      $(this).data("decimals")
    );
  });

  $(window).on("hashchange", function () {
    const route = window.location.hash.replace("#", "") || "home";
    guardRoute(route);
    updateNavByRole();
  });

  // Initial
  updateNavByRole();

  app.run();
});
