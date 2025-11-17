$(function () {
  var app = $.spapp({
    defaultView : "#home",
    templateDir : "views/",
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

  function loadTradingViewScript(cb) {
    if (window.TradingView && typeof window.TradingView.widget === "function") { cb && cb(); return; }
    var s = document.createElement("script");
    s.src = "https://s3.tradingview.com/tv.js";
    s.onload = function(){ cb && cb(); };
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
    onReady: function(){ loadTradingViewScript(renderTVChart); }
  });

  app.run();
});
