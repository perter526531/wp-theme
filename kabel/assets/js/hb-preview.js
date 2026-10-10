"use strict";
(() => {
  // src/header-builder/preview.js
  (function() {
    "use strict";
    var DATA = window.KabelHBPreview || {};
    var DELAY_MS = 400;
    var timer = null;
    var lastRaw = null;
    function parseModel(raw) {
      if (typeof raw !== "string" || "" === raw) {
        return null;
      }
      try {
        var parsed = JSON.parse(raw);
        return parsed && typeof parsed === "object" ? parsed : null;
      } catch (err) {
        return null;
      }
    }
    function hasRows(model) {
      return !!(model && Array.isArray(model.rows) && model.rows.length);
    }
    function currentDomHash() {
      var marker = document.querySelector("header[data-kb-header] [data-kbhb-hash]");
      return marker ? marker.getAttribute("data-kbhb-hash") : "";
    }
    function applyInlineCss(css) {
      if (!css) {
        return;
      }
      var style = document.getElementById("kabel-hb-inline-css");
      if (!style) {
        style = document.createElement("style");
        style.id = "kabel-hb-inline-css";
        var link = document.getElementById("kabel-hb-css");
        if (link && link.parentNode) {
          link.parentNode.insertBefore(style, link.nextSibling);
        } else {
          document.head.appendChild(style);
        }
      }
      style.textContent = css;
    }
    function requestRefresh() {
      if (window.wp && window.wp.customize && window.wp.customize.preview) {
        window.wp.customize.preview.send("refresh");
      } else {
        window.location.reload();
      }
    }
    function renderModel(model) {
      window.fetch(DATA.rest, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": DATA.restNonce || ""
        },
        body: JSON.stringify({ model })
      }).then(function(res) {
        if (!res.ok) {
          throw new Error("HTTP " + res.status);
        }
        return res.json();
      }).then(function(data) {
        if (!data || data.ok !== true || !data.html) {
          return;
        }
        if (data.hash && data.hash === currentDomHash()) {
          applyInlineCss(data.inlineCss);
          return;
        }
        var headerEl = document.querySelector("header[data-kb-header]");
        if (!headerEl) {
          return;
        }
        headerEl.outerHTML = data.html;
        applyInlineCss(data.inlineCss);
        if (window.kabelHBInit) {
          window.kabelHBInit();
        }
      }).catch(function(err) {
        if (window.console && window.console.warn) {
          window.console.warn("[kabel-hb] preview render failed:", err);
        }
      });
    }
    function apply(raw) {
      lastRaw = raw;
      var model = parseModel(raw);
      var live = !!document.querySelector("header[data-kb-header] [data-kbhb]");
      if (!hasRows(model)) {
        if (live) {
          requestRefresh();
        }
        return;
      }
      renderModel(model);
    }
    if (!(window.wp && window.wp.customize)) {
      return;
    }
    window.wp.customize("kabel_header_builder", function(value) {
      value.bind(function(raw) {
        if (raw === lastRaw) {
          return;
        }
        if (timer) {
          window.clearTimeout(timer);
        }
        timer = window.setTimeout(function() {
          apply(raw);
        }, DELAY_MS);
      });
    });
  })();
})();
