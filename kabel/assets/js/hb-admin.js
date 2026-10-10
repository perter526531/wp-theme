"use strict";
(() => {
  // src/header-builder/shims/element.js
  var wpEl = window.wp.element;
  var el = wpEl.createElement;
  var Fr = wpEl.Fragment;
  var RawHTML = wpEl.RawHTML;
  var useState = wpEl.useState;
  var useEffect = wpEl.useEffect;
  var useMemo = wpEl.useMemo;
  var useCallback = wpEl.useCallback;
  var useRef = wpEl.useRef;
  var render = wpEl.render;
  var createRoot = wpEl.createRoot;
  var cloneElement = wpEl.cloneElement;
  var isValidElement = wpEl.isValidElement;

  // src/header-builder/zh.js
  var ZH = {
    /* ---- Shell / top bar ---- */
    "Header Builder": "\u9875\u7709\u6784\u5EFA\u5668",
    "Builder active": "\u6784\u5EFA\u5668\u751F\u6548",
    "Blocks header active": "\u5757\u9875\u7709\u751F\u6548",
    "Classic header active": "\u5185\u7F6E\u9875\u7709\u751F\u6548",
    "Which header source is live on the site": "\u5F53\u524D\u7AD9\u70B9\u6B63\u5728\u4F7F\u7528\u7684\u9875\u7709\u6765\u6E90",
    "History": "\u64A4\u9500/\u91CD\u505A",
    "Undo": "\u64A4\u9500",
    "Redo": "\u91CD\u505A",
    "Preview device": "\u9884\u89C8\u8BBE\u5907",
    "Desktop": "\u684C\u9762",
    "Mobile": "\u624B\u673A",
    "Save": "\u4FDD\u5B58",
    "Saving\u2026": "\u4FDD\u5B58\u4E2D\u2026",
    "Saved": "\u5DF2\u4FDD\u5B58",
    "Save failed.": "\u4FDD\u5B58\u5931\u8D25",
    "Unsaved changes": "\u6709\u672A\u4FDD\u5B58\u7684\u4FEE\u6539",
    "Open Front End": "\u6253\u5F00\u524D\u53F0",
    "More actions": "\u66F4\u591A\u64CD\u4F5C",
    "Clear Builder Data": "\u6E05\u7A7A\u6784\u5EFA\u5668\u6570\u636E\uFF08\u56DE\u9000\u5230\u5757\u9875\u7709/\u5185\u7F6E\u9875\u7709\uFF09",
    "Edit Header Blocks (Legacy)": "\u7F16\u8F91\u5757\u9875\u7709\uFF08\u65E7\u65B9\u6848\uFF09",
    "Clear builder data?": "\u6E05\u7A7A\u6784\u5EFA\u5668\u6570\u636E\uFF1F",
    "This removes the saved header model. The site falls back to the blocks header or the classic header. This cannot be undone.": "\u5C06\u5220\u9664\u5DF2\u4FDD\u5B58\u7684\u9875\u7709\u6A21\u578B\uFF0C\u7AD9\u70B9\u4F1A\u56DE\u9000\u5230\u5757\u9875\u7709\u6216\u5185\u7F6E\u9875\u7709\u3002\u6B64\u64CD\u4F5C\u4E0D\u53EF\u64A4\u9500\u3002",
    "Clear": "\u6E05\u7A7A",
    "Delete failed.": "\u5220\u9664\u5931\u8D25",
    "Setup error: KabelHBData is missing, the Header Builder cannot start.": "\u914D\u7F6E\u9519\u8BEF\uFF1A\u7F3A\u5C11 KabelHBData\uFF0C\u9875\u7709\u6784\u5EFA\u5668\u65E0\u6CD5\u542F\u52A8\u3002",
    /* ---- Sidebar / element library ---- */
    "Elements": "\u5143\u7D20",
    "Settings": "\u8BBE\u7F6E",
    "Drag an element onto the structure bar below, or click to add it.": "\u628A\u5143\u7D20\u62D6\u5230\u5E95\u90E8\u7ED3\u6784\u6761\uFF0C\u6216\u76F4\u63A5\u70B9\u51FB\u6DFB\u52A0\u3002",
    "Requires WooCommerce": "\u9700\u8981 WooCommerce",
    "Select an element, a row, or mobile settings to edit.": "\u9009\u62E9\u4E00\u4E2A\u5143\u7D20\u3001\u884C\u6216\u79FB\u52A8\u7AEF\u8BBE\u7F6E\u8FDB\u884C\u7F16\u8F91\u3002",
    /* ---- Element names (registry) ---- */
    "Site Logo": "\u7AD9\u70B9\u6807\u5FD7",
    "Menu": "\u83DC\u5355",
    "Button": "\u6309\u94AE",
    "Search": "\u641C\u7D22",
    "Social Icons": "\u793E\u4EA4\u56FE\u6807",
    "Custom HTML": "\u81EA\u5B9A\u4E49 HTML",
    "Contact Info": "\u8054\u7CFB\u4FE1\u606F",
    "Text": "\u6587\u672C",
    "Language Switcher": "\u8BED\u8A00\u5207\u6362",
    "Account": "\u8D26\u6237",
    "Cart": "\u8D2D\u7269\u8F66",
    "Unknown element": "\u672A\u77E5\u5143\u7D20",
    /* ---- Structure bar ---- */
    "Structure": "\u7ED3\u6784",
    "Row {n}": "\u7B2C {n} \u884C",
    "Row settings": "\u884C\u8BBE\u7F6E",
    "Delete row": "\u5220\u9664\u884C",
    "Delete this row?": "\u5220\u9664\u8FD9\u884C\uFF1F",
    "The row and its element placements will be removed. Elements used elsewhere stay.": "\u8BE5\u884C\u53CA\u5176\u5143\u7D20\u5E03\u5C40\u5C06\u88AB\u79FB\u9664\uFF0C\u5176\u4ED6\u4F4D\u7F6E\u4ECD\u5728\u4F7F\u7528\u7684\u5143\u7D20\u4F1A\u4FDD\u7559\u3002",
    "Delete": "\u5220\u9664",
    "Add Row": "\u6DFB\u52A0\u884C",
    "Maximum of 3 rows reached.": "\u6700\u591A 3 \u884C\u3002",
    "Left": "\u5DE6",
    "Center": "\u5C45\u4E2D",
    "Right": "\u53F3",
    "Browse elements": "\u6D4F\u89C8\u5143\u7D20",
    "Remove from this zone": "\u4ECE\u8BE5\u533A\u79FB\u9664",
    "Move up": "\u4E0A\u79FB",
    "Move down": "\u4E0B\u79FB",
    "Remove item": "\u79FB\u9664\u6761\u76EE",
    "Mobile Header": "\u79FB\u52A8\u7AEF\u9875\u7709",
    "Mobile settings": "\u79FB\u52A8\u7AEF\u8BBE\u7F6E",
    "Drawer panel (opens via hamburger)": "\u62BD\u5C49\u9762\u677F\uFF08\u70B9\u6C49\u5821\u5C55\u5F00\uFF09",
    "Drawer elements": "\u62BD\u5C49\u5143\u7D20",
    "Drawer is empty. Drag elements here.": "\u62BD\u5C49\u662F\u7A7A\u7684\uFF0C\u62D6\u52A8\u5143\u7D20\u5230\u8FD9\u91CC\u3002",
    "Hamburger side": "\u6C49\u5821\u4F4D\u7F6E",
    "Drawer side": "\u62BD\u5C49\u6ED1\u51FA\u65B9\u5411",
    "Breakpoint": "\u65AD\u70B9",
    "Below this viewport width the mobile header is shown.": "\u89C6\u53E3\u7A84\u4E8E\u8BE5\u5BBD\u5EA6\u65F6\u663E\u793A\u79FB\u52A8\u7AEF\u9875\u7709\u3002",
    /* ---- Preview ---- */
    "Live preview": "\u5B9E\u65F6\u9884\u89C8",
    "Loading preview\u2026": "\u9884\u89C8\u52A0\u8F7D\u4E2D\u2026",
    "Preview updated": "\u9884\u89C8\u5DF2\u66F4\u65B0",
    "Preview update failed.": "\u9884\u89C8\u66F4\u65B0\u5931\u8D25",
    /* ---- Logo ---- */
    "Source": "\u6765\u6E90",
    "Site identity": "\u7AD9\u70B9\u6807\u5FD7",
    "Image": "\u56FE\u7247",
    "Logo text": "\u6807\u5FD7\u6587\u672C",
    "Tagline": "\u526F\u6807\u9898",
    "Show tagline": "\u663E\u793A\u526F\u6807\u9898",
    "Choose Image": "\u9009\u62E9\u56FE\u7247",
    "Replace Image": "\u66F4\u6362\u56FE\u7247",
    "Remove Image": "\u79FB\u9664\u56FE\u7247",
    "Logo width": "\u6807\u5FD7\u5BBD\u5EA6",
    "Uses the site logo from Appearance \u2192 Settings.": "\u4F7F\u7528\u300C\u5916\u89C2 \u2192 \u8BBE\u7F6E\u300D\u4E2D\u7684\u7AD9\u70B9\u6807\u5FD7\u3002",
    "Site name": "\u7AD9\u70B9\u540D\u79F0",
    "Media library is not available.": "\u5A92\u4F53\u5E93\u4E0D\u53EF\u7528\u3002",
    /* ---- Menu ---- */
    "Auto (first menu)": "\u81EA\u52A8\uFF08\u7B2C\u4E00\u4E2A\u83DC\u5355\uFF09",
    "Font size": "\u5B57\u53F7",
    "Gap": "\u95F4\u8DDD",
    "Alignment": "\u5BF9\u9F50",
    "Link color": "\u94FE\u63A5\u989C\u8272",
    "Hover color": "\u60AC\u505C\u989C\u8272",
    "Menu style": "\u83DC\u5355\u6837\u5F0F",
    "Plain": "\u6734\u7D20",
    "Underline": "\u4E0B\u5212\u7EBF",
    "Pill": "\u80F6\u56CA",
    "Submenu indicator": "\u5B50\u83DC\u5355\u6307\u793A\u7BAD\u5934",
    /* ---- Button ---- */
    "Button text": "\u6309\u94AE\u6587\u5B57",
    "URL": "\u94FE\u63A5\u5730\u5740",
    "Variant": "\u6837\u5F0F\u53D8\u4F53",
    "Gold": "\u91D1\u8272",
    "Outline": "\u63CF\u8FB9",
    "Ghost": "\u5E7D\u7075",
    "Dark": "\u6DF1\u8272",
    "Light": "\u6D45\u8272",
    "Size": "\u5C3A\u5BF8",
    "Small": "\u5C0F",
    "Medium": "\u4E2D",
    "Open in new tab": "\u65B0\u7A97\u53E3\u6253\u5F00",
    /* ---- Search ---- */
    "Search style": "\u641C\u7D22\u6837\u5F0F",
    "Icon": "\u56FE\u6807",
    "Field": "\u8F93\u5165\u6846",
    "Placeholder": "\u5360\u4F4D\u63D0\u793A\u6587\u5B57",
    "Field width": "\u8F93\u5165\u6846\u5BBD\u5EA6",
    "Applies to the field style.": "\u4EC5\u5BF9\u8F93\u5165\u6846\u6837\u5F0F\u751F\u6548\u3002",
    /* ---- Social ---- */
    "Social links": "\u793E\u4EA4\u94FE\u63A5",
    "No icons yet \u2014 click \u201CAdd Icon\u201D to create one.": "\u8FD8\u6CA1\u6709\u56FE\u6807\uFF0C\u70B9\u51FB\u300C\u6DFB\u52A0\u56FE\u6807\u300D\u521B\u5EFA\u4E00\u4E2A\u3002",
    "Add Icon": "\u6DFB\u52A0\u56FE\u6807",
    "Icon size": "\u56FE\u6807\u5927\u5C0F",
    "Icon gap": "\u56FE\u6807\u95F4\u8DDD",
    "WhatsApp": "WhatsApp",
    "Facebook": "Facebook",
    "Instagram": "Instagram",
    "LinkedIn": "LinkedIn",
    "YouTube": "YouTube",
    "X (Twitter)": "X\uFF08Twitter\uFF09",
    "TikTok": "TikTok",
    "Email": "\u90AE\u7BB1",
    "Phone": "\u7535\u8BDD",
    /* ---- HTML / contact / text / lang / account / cart ---- */
    "HTML content": "HTML \u5185\u5BB9",
    "Will be filtered in safe mode because your account lacks the unfiltered_html capability.": "\u5F53\u524D\u8D26\u53F7\u6CA1\u6709 unfiltered_html \u6743\u9650\uFF0C\u5185\u5BB9\u5C06\u4EE5\u5B89\u5168\u6A21\u5F0F\u8FC7\u6EE4\u3002",
    "Phone number": "\u7535\u8BDD\u53F7\u7801",
    "Email address": "\u90AE\u7BB1\u5730\u5740",
    "Address": "\u5730\u5740",
    "Show phone": "\u663E\u793A\u7535\u8BDD",
    "Show email": "\u663E\u793A\u90AE\u7BB1",
    "Show address": "\u663E\u793A\u5730\u5740",
    "Content": "\u5185\u5BB9",
    "Color": "\u989C\u8272",
    "Language style": "\u8BED\u8A00\u6837\u5F0F",
    "Code": "\u4EE3\u7801",
    "Name": "\u540D\u79F0",
    "Login text": "\u767B\u5F55\u6587\u5B57",
    "Login URL": "\u767B\u5F55\u94FE\u63A5",
    "Show item count": "\u663E\u793A\u6570\u91CF\u89D2\u6807",
    /* ---- Row settings ---- */
    "Background color": "\u80CC\u666F\u989C\u8272",
    "Text color": "\u6587\u5B57\u989C\u8272",
    "Vertical padding": "\u4E0A\u4E0B\u5185\u8FB9\u8DDD",
    "Bottom border": "\u5E95\u90E8\u8FB9\u6846",
    "Sticky": "\u5438\u9644",
    "When any row is sticky, the whole header stays pinned while scrolling.": "\u5F00\u542F\u4EFB\u4E00\u884C\u7684\u5438\u9644\u540E\uFF0C\u6574\u4E2A\u9875\u7709\u968F\u6EDA\u52A8\u60AC\u505C\u3002",
    "Transparent": "\u900F\u660E",
    "Transparent rows overlay the page content \u2014 great with hero sections.": "\u900F\u660E\u884C\u60AC\u6D6E\u5728\u9875\u9762\u5185\u5BB9\u4E4B\u4E0A\uFF0C\u9002\u5408\u914D\u5408\u9996\u5C4F\u5927\u56FE\u3002",
    /* ---- Shared controls / dialogs ---- */
    "Default": "\u9ED8\u8BA4",
    "Cancel": "\u53D6\u6D88",
    "Confirm": "\u786E\u8BA4"
  };
  var PHP_L10N = typeof window !== "undefined" && window.KabelHBData && window.KabelHBData.l10n || {};
  function t(key) {
    if (Object.prototype.hasOwnProperty.call(ZH, key)) {
      return ZH[key];
    }
    if (Object.prototype.hasOwnProperty.call(PHP_L10N, key)) {
      return PHP_L10N[key];
    }
    return key;
  }

  // src/header-builder/ui.jsx
  var ICONS = {
    logo: ["M12 3l7 4v6c0 4-3 6.6-7 8-4-1.4-7-4-7-8V7z"],
    menu: ["M4 7h16", "M4 12h16", "M4 17h16"],
    button: ["M4 9h16v6H4z", "M9.5 12h5"],
    search: ["M18 11a7 7 0 11-14 0 7 7 0 0114 0z", "M21 21l-4.3-4.3"],
    social: [
      "M21 5a3 3 0 11-6 0 3 3 0 016 0z",
      "M9 12a3 3 0 11-6 0 3 3 0 016 0z",
      "M21 19a3 3 0 11-6 0 3 3 0 016 0z",
      "M8.6 13.5l6.8 4",
      "M15.4 6.5l-6.8 4"
    ],
    code: ["M8 6l-5 6 5 6", "M16 6l5 6-5 6"],
    contact: [
      "M5 4h4l2 5-2.5 1.5a12 12 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"
    ],
    text: ["M4 7V4h16v3", "M9 20h6", "M12 4v16"],
    globe: [
      "M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
      "M3 12h18",
      "M12 3c2.6 2.6 2.6 15.4 0 18",
      "M12 3c-2.6 2.6-2.6 15.4 0 18"
    ],
    user: ["M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2", "M16 7a4 4 0 11-8 0 4 4 0 018 0z"],
    cart: [
      "M9 22a1 1 0 11-2 0 1 1 0 012 0z",
      "M20 22a1 1 0 11-2 0 1 1 0 012 0z",
      "M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6"
    ],
    desktop: ["M3 5h18v11H3z", "M8 20h8", "M12 16v4"],
    mobile: ["M7 3h10v18H7z", "M10.5 18.5h3"],
    undo: ["M3 4v6h6", "M21 12a9 9 0 11-17.5-2.2"],
    redo: ["M21 4v6h-6", "M3 12a9 9 0 1017.5-2.2"],
    chevronUp: ["M6 14l6-6 6 6"],
    chevronDown: ["M6 10l6 6 6-6"],
    close: ["M6 6l12 12", "M18 6L6 18"],
    plus: ["M12 5v14", "M5 12h14"],
    more: [
      "M6.6 12a1.6 1.6 0 11-3.2 0 1.6 1.6 0 013.2 0z",
      "M13.6 12a1.6 1.6 0 11-3.2 0 1.6 1.6 0 013.2 0z",
      "M20.6 12a1.6 1.6 0 11-3.2 0 1.6 1.6 0 013.2 0z"
    ],
    external: ["M14 4h6v6", "M20 4L11 13", "M20 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1h5"],
    gear: [
      "M15 12a3 3 0 11-6 0 3 3 0 016 0z",
      "M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09a1.65 1.65 0 00-1.08-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09a1.65 1.65 0 001.51-1.08 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33h.08a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51h.08a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82v.08a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"
    ],
    trash: ["M4 7h16", "M9 7V4h6v3", "M6 7l1 14h10l1-14", "M10 11v6", "M14 11v6"],
    image: [
      "M3 5h18v14H3z",
      "M3 16l5-5 4 4 3-3 6 6",
      "M8.6 9a1.3 1.3 0 11-2.6 0 1.3 1.3 0 012.6 0z"
    ],
    alert: ["M12 4L2.5 20h19z", "M12 10v4", "M12 17v.5"]
  };
  function Icon({ name, size }) {
    const paths = ICONS[name];
    if (!paths) {
      return null;
    }
    const s = size || 20;
    return el(
      "svg",
      {
        className: "khb-icon",
        width: s,
        height: s,
        viewBox: "0 0 24 24",
        fill: "none",
        stroke: "currentColor",
        strokeWidth: 1.8,
        strokeLinecap: "round",
        strokeLinejoin: "round",
        "aria-hidden": true,
        focusable: "false"
      },
      paths.map((d, i) => el("path", { key: i, d }))
    );
  }
  function Toast({ message, tone }) {
    if (!message) {
      return null;
    }
    return el(
      "div",
      {
        className: "khb-toast" + (tone === "error" ? " is-error" : ""),
        role: "status",
        "aria-live": "polite"
      },
      message
    );
  }
  function ConfirmDialog({
    title,
    message,
    confirmLabel,
    danger,
    onConfirm,
    onCancel
  }) {
    const ref = useRef(null);
    useEffect(() => {
      const onKey = (e) => {
        if (e.key === "Escape") {
          onCancel();
        }
      };
      document.addEventListener("keydown", onKey);
      if (ref.current) {
        ref.current.focus();
      }
      return () => document.removeEventListener("keydown", onKey);
    }, [onCancel]);
    return el(
      "div",
      {
        className: "khb-overlay",
        onMouseDown: (e) => {
          if (e.target === e.currentTarget) {
            onCancel();
          }
        }
      },
      el(
        "div",
        { className: "khb-dialog", role: "dialog", "aria-modal": "true", "aria-label": title },
        el("h3", { className: "khb-dialog__title" }, title),
        el("p", { className: "khb-dialog__msg" }, message),
        el(
          "div",
          { className: "khb-dialog__actions" },
          el("button", { type: "button", className: "khb-btn", onClick: onCancel }, t("Cancel")),
          el(
            "button",
            {
              type: "button",
              ref,
              className: "khb-btn " + (danger ? "khb-btn--danger" : "khb-btn--primary"),
              onClick: onConfirm
            },
            confirmLabel || t("Confirm")
          )
        )
      )
    );
  }
  function IconButton({ icon, label, onClick, disabled, tone }) {
    return el(
      "button",
      {
        type: "button",
        className: "khb-iconbtn" + (tone === "dark" ? " khb-iconbtn--dark" : "") + (tone === "danger" ? " is-danger" : ""),
        "aria-label": label,
        title: label,
        onClick,
        disabled: !!disabled
      },
      el(Icon, { name: icon })
    );
  }

  // src/header-builder/controls.jsx
  function Field({ label, help, children }) {
    return el(
      "div",
      { className: "khb-field" },
      label ? el("label", { className: "khb-field__label" }, label) : null,
      children,
      help ? el("p", { className: "khb-help" }, help) : null
    );
  }
  function TextRow({ label, value, onChange, placeholder, type }) {
    return el(
      Field,
      { label },
      el("input", {
        className: "khb-input",
        type: type || "text",
        value: value == null ? "" : value,
        placeholder: placeholder || "",
        onChange: (e) => onChange(e.target.value)
      })
    );
  }
  function UrlRow(props) {
    return el(TextRow, Object.assign({ type: "url" }, props));
  }
  function TextareaRow({ label, value, onChange, placeholder, rows, mono }) {
    return el(
      Field,
      { label },
      el("textarea", {
        className: "khb-textarea" + (mono ? " is-mono" : ""),
        rows: rows || 4,
        value: value == null ? "" : value,
        placeholder: placeholder || "",
        spellCheck: false,
        onChange: (e) => onChange(e.target.value)
      })
    );
  }
  function SelectRow({ label, help, value, options, onChange }) {
    return el(
      Field,
      { label, help },
      el(
        "select",
        {
          className: "khb-select",
          value: String(value == null ? "" : value),
          onChange: (e) => onChange(e.target.value)
        },
        options.map(
          (o) => el("option", { key: String(o.value), value: String(o.value) }, o.label)
        )
      )
    );
  }
  function Segmented({ value, options, onChange, ariaLabel, small }) {
    return el(
      "div",
      { className: "khb-seg" + (small ? " is-small" : ""), role: "group", "aria-label": ariaLabel },
      options.map(
        (o) => el(
          "button",
          {
            key: String(o.value),
            type: "button",
            className: o.value === value ? "is-active" : "",
            "aria-pressed": o.value === value,
            onClick: () => onChange(o.value)
          },
          o.icon ? el(Icon, { name: o.icon, size: 15 }) : null,
          o.label ? el("span", { className: "khb-seg__label" }, o.label) : null
        )
      )
    );
  }
  function SegRow({ label, help, value, options, onChange }) {
    return el(Field, { label, help }, el(Segmented, { value, options, onChange, ariaLabel: label }));
  }
  function NumberInput({ value, min, max, step, onCommit, unit, ariaLabel }) {
    const [text, setText] = useState(String(value));
    const [focused, setFocused] = useState(false);
    useEffect(() => {
      if (!focused) {
        setText(String(value));
      }
    }, [value, focused]);
    const commit = (raw) => {
      const n = parseFloat(raw);
      if (Number.isNaN(n)) {
        return;
      }
      onCommit(Math.min(max, Math.max(min, n)));
    };
    return el(
      Fr,
      null,
      el("input", {
        className: "khb-num",
        type: "number",
        value: focused ? text : String(value),
        min,
        max,
        step: step || 1,
        "aria-label": ariaLabel,
        onFocus: () => {
          setFocused(true);
          setText(String(value));
        },
        onBlur: () => {
          setFocused(false);
          commit(text);
        },
        onChange: (e) => {
          setText(e.target.value);
          commit(e.target.value);
        }
      }),
      unit ? el("span", { className: "khb-unit" }, unit) : null
    );
  }
  function RangeRow({ label, help, value, min, max, step, unit, onChange }) {
    const v = Number.isFinite(Number(value)) ? Number(value) : min;
    return el(
      Field,
      { label, help },
      el(
        "div",
        { className: "khb-rangerow" },
        el("input", {
          className: "khb-range",
          type: "range",
          min,
          max,
          step: step || 1,
          value: v,
          "aria-label": label,
          onChange: (e) => onChange(Number(e.target.value))
        }),
        el(NumberInput, {
          value: v,
          min,
          max,
          step,
          unit,
          ariaLabel: label,
          onCommit: onChange
        })
      )
    );
  }
  function ColorRow({ label, value, onChange }) {
    const hex = value || "";
    return el(
      Field,
      { label },
      el(
        "div",
        { className: "khb-colorrow" },
        el("input", {
          className: "khb-swatch",
          type: "color",
          value: hex || "#B39A74",
          "aria-label": label,
          title: hex || t("Default"),
          onChange: (e) => onChange(e.target.value)
        }),
        el("input", {
          className: "khb-hex",
          type: "text",
          readOnly: true,
          value: hex ? hex.toUpperCase() : "",
          placeholder: t("Default"),
          "aria-hidden": true,
          tabIndex: -1
        }),
        el(
          "button",
          {
            type: "button",
            className: "khb-btn khb-btn--tiny" + (hex ? "" : " is-disabled"),
            onClick: () => onChange(""),
            disabled: !hex
          },
          t("Default")
        )
      )
    );
  }
  function ToggleRow({ label, help, checked, onChange }) {
    return el(
      "div",
      { className: "khb-field khb-toggle" },
      el(
        "label",
        { className: "khb-toggle__row" },
        el("span", { className: "khb-toggle__label" }, label),
        el("input", {
          type: "checkbox",
          className: "khb-switch",
          checked: !!checked,
          onChange: (e) => onChange(e.target.checked)
        })
      ),
      help ? el("p", { className: "khb-help" }, help) : null
    );
  }
  function Note({ children }) {
    return el("div", { className: "khb-note", role: "note" }, children);
  }

  // src/header-builder/TopBar.jsx
  function TopBar({
    liveSource,
    canUndo,
    canRedo,
    onUndo,
    onRedo,
    device,
    onDevice,
    dirty,
    saving,
    onSave,
    onClearRequest,
    home,
    headerBlocksEdit
  }) {
    const [open, setOpen] = useState(false);
    const menuRef = useRef(null);
    useEffect(() => {
      if (!open) {
        return;
      }
      const onKey = (e) => {
        if (e.key === "Escape") {
          setOpen(false);
        }
      };
      const onDown = (e) => {
        if (menuRef.current && !menuRef.current.contains(e.target)) {
          setOpen(false);
        }
      };
      document.addEventListener("keydown", onKey);
      document.addEventListener("mousedown", onDown);
      return () => {
        document.removeEventListener("keydown", onKey);
        document.removeEventListener("mousedown", onDown);
      };
    }, [open]);
    const badgeLabel = liveSource === "builder" ? t("Builder active") : liveSource === "blocks" ? t("Blocks header active") : t("Classic header active");
    const badgeClass = liveSource === "builder" ? "khb-badge khb-badge--builder" : "khb-badge khb-badge--plain";
    return el(
      "header",
      { className: "khb-topbar" },
      el("h1", { className: "khb-topbar__title" }, t("Header Builder")),
      el("span", { className: badgeClass, title: t("Which header source is live on the site") }, badgeLabel),
      el(
        "div",
        { className: "khb-topbar__group", role: "group", "aria-label": t("History") },
        el("button", {
          type: "button",
          className: "khb-iconbtn",
          "aria-label": t("Undo"),
          title: t("Undo"),
          disabled: !canUndo,
          onClick: onUndo
        }, el(Icon, { name: "undo" })),
        el("button", {
          type: "button",
          className: "khb-iconbtn",
          "aria-label": t("Redo"),
          title: t("Redo"),
          disabled: !canRedo,
          onClick: onRedo
        }, el(Icon, { name: "redo" }))
      ),
      el(Segmented, {
        value: device,
        ariaLabel: t("Preview device"),
        onChange: onDevice,
        options: [
          { value: "desktop", label: t("Desktop"), icon: "desktop" },
          { value: "mobile", label: t("Mobile"), icon: "mobile" }
        ]
      }),
      el("span", { className: "khb-topbar__spacer" }),
      home ? el(
        "a",
        {
          className: "khb-btn",
          href: home,
          target: "_blank",
          rel: "noopener noreferrer"
        },
        el(Icon, { name: "external", size: 15 }),
        el("span", null, t("Open Front End"))
      ) : null,
      el(
        "button",
        {
          type: "button",
          className: "khb-btn khb-btn--primary" + (saving ? " is-busy" : ""),
          onClick: onSave,
          disabled: saving,
          "aria-label": t("Save")
        },
        saving ? el("span", { className: "khb-spin", "aria-hidden": true }) : dirty ? el("span", { className: "khb-save-dot", "aria-hidden": true }) : null,
        el("span", null, saving ? t("Saving\u2026") : t("Save"))
      ),
      el(
        "div",
        { className: "khb-menu", ref: menuRef },
        el(
          "button",
          {
            type: "button",
            className: "khb-iconbtn" + (open ? " is-open" : ""),
            "aria-label": t("More actions"),
            title: t("More actions"),
            "aria-expanded": open,
            "aria-haspopup": "menu",
            onClick: () => setOpen(!open)
          },
          el(Icon, { name: "more" })
        ),
        open ? el(
          "div",
          { className: "khb-menu__pop", role: "menu" },
          el(
            "button",
            {
              type: "button",
              className: "khb-menu__item is-danger",
              role: "menuitem",
              onClick: () => {
                setOpen(false);
                onClearRequest();
              }
            },
            el(Icon, { name: "trash", size: 15 }),
            el("span", null, t("Clear Builder Data"))
          ),
          headerBlocksEdit ? el(
            "a",
            {
              className: "khb-menu__item",
              role: "menuitem",
              href: headerBlocksEdit
            },
            el(Icon, { name: "code", size: 15 }),
            el("span", null, t("Edit Header Blocks (Legacy)"))
          ) : null
        ) : null
      )
    );
  }

  // src/header-builder/model.js
  var ELEMENT_TYPES = [
    "logo",
    "menu",
    "button",
    "search",
    "social",
    "html",
    "contact",
    "text",
    "lang",
    "account",
    "cart"
  ];
  var ELEMENTS = {
    logo: { label: "Site Logo", icon: "logo" },
    menu: { label: "Menu", icon: "menu" },
    button: { label: "Button", icon: "button" },
    search: { label: "Search", icon: "search" },
    social: { label: "Social Icons", icon: "social" },
    html: { label: "Custom HTML", icon: "code" },
    contact: { label: "Contact Info", icon: "contact" },
    text: { label: "Text", icon: "text" },
    lang: { label: "Language Switcher", icon: "globe" },
    account: { label: "Account", icon: "user" },
    cart: { label: "Cart", icon: "cart" }
  };
  var SOCIAL_ICONS = [
    ["whatsapp", "WhatsApp"],
    ["facebook", "Facebook"],
    ["instagram", "Instagram"],
    ["linkedin", "LinkedIn"],
    ["youtube", "YouTube"],
    ["x", "X (Twitter)"],
    ["tiktok", "TikTok"],
    ["email", "Email"],
    ["phone", "Phone"]
  ];
  var MAX_ROWS = 3;
  var uidCounter = 0;
  function uid(prefix) {
    uidCounter += 1;
    const rand = Math.random().toString(36).slice(2, 8);
    return prefix + "-" + rand + uidCounter.toString(36);
  }
  function clampNum(value, min, max, fallback) {
    const n = Number(value);
    if (!Number.isFinite(n)) {
      return fallback;
    }
    return Math.min(max, Math.max(min, n));
  }
  function colorValue(value) {
    return typeof value === "string" && /^#[0-9a-fA-F]{3,8}$/.test(value) ? value : "";
  }
  function boolValue(value, fallback) {
    return typeof value === "boolean" ? value : fallback;
  }
  function idList(value) {
    if (!Array.isArray(value)) {
      return [];
    }
    const out = [];
    for (const item of value) {
      if (typeof item === "string" && item && out.indexOf(item) === -1) {
        out.push(item);
      }
    }
    return out;
  }
  function defaultsFor(type, home) {
    const base = (home || "").replace(/\/+$/, "") || "";
    switch (type) {
      case "logo":
        return { source: "site", text: "", tagline: "", taglineShow: true, imgId: 0, imgUrl: "", imgWidth: 140 };
      case "menu":
        return { menuId: 0, fontSize: 15, gap: 28, align: "left", color: "", hoverColor: "", style: "plain", subIndicator: true };
      case "button":
        return { text: "Contact", url: base ? base + "/contact/" : "", variant: "gold", size: "s", target: false };
      case "search":
        return { style: "icon", placeholder: "Search\u2026", fieldWidth: 180 };
      case "social":
        return { items: [], size: 18, gap: 10 };
      case "html":
        return { content: "" };
      case "contact":
        return { phone: "", email: "", address: "", showPhone: true, showEmail: true, showAddress: false };
      case "text":
        return { content: "", size: 14, color: "" };
      case "lang":
        return { style: "code" };
      case "account":
        return { loginText: "Login", url: "" };
      case "cart":
        return { showCount: true };
      default:
        return {};
    }
  }
  function rowSettingsDefaults() {
    return { bg: "", textColor: "", padY: 16, borderBottom: false, sticky: false, transparent: false };
  }
  var NUMERIC_FIELDS = {
    logo: { imgWidth: [40, 400] },
    menu: { fontSize: [10, 32], gap: [0, 96] },
    search: { fieldWidth: [120, 420] },
    social: { size: [12, 32], gap: [4, 40] },
    text: { size: [10, 40] }
  };
  function clampSettings(type, settings) {
    const ranges = NUMERIC_FIELDS[type];
    if (!ranges) {
      return settings;
    }
    const out = Object.assign({}, settings);
    for (const field of Object.keys(ranges)) {
      const n = Number(out[field]);
      if (!Number.isFinite(n)) {
        out[field] = defaultsFor(type)[field];
      } else {
        out[field] = Math.min(ranges[field][1], Math.max(ranges[field][0], n));
      }
    }
    return out;
  }
  function rowSettings(input) {
    const s = input && typeof input === "object" ? input : {};
    return {
      bg: colorValue(s.bg),
      textColor: colorValue(s.textColor),
      padY: clampNum(s.padY, 0, 96, 16),
      borderBottom: boolValue(s.borderBottom, false),
      sticky: boolValue(s.sticky, false),
      transparent: boolValue(s.transparent, false)
    };
  }
  function zonesOf(input) {
    const z = Array.isArray(input) ? input : [];
    return [0, 1, 2].map((i) => idList(z[i]));
  }
  function defaultModel(data) {
    const home = (data && data.home || "").replace(/\/+$/, "");
    return {
      version: 1,
      rows: [
        {
          id: "r-1",
          settings: rowSettingsDefaults(),
          zones: [["e-logo"], [], ["e-menu", "e-cta"]]
        }
      ],
      mobile: {
        zones: [["e-logo"], [], []],
        offcanvas: ["e-menu", "e-cta"],
        offcanvasDir: "right",
        trigger: "right",
        breakpoint: 780
      },
      elements: {
        "e-logo": { type: "logo", settings: defaultsFor("logo", home) },
        "e-menu": { type: "menu", settings: defaultsFor("menu", home) },
        "e-cta": {
          type: "button",
          settings: Object.assign(defaultsFor("button", home), {
            text: "Contact",
            url: home ? home + "/contact/" : "",
            variant: "gold"
          })
        }
      }
    };
  }
  function normalizeModel(input, data) {
    if (!input || typeof input !== "object") {
      return defaultModel(data);
    }
    const home = (data && data.home || "").replace(/\/+$/, "");
    const rows = [];
    const srcRows = Array.isArray(input.rows) ? input.rows.slice(0, MAX_ROWS) : [];
    for (const row of srcRows) {
      if (!row || typeof row !== "object") {
        continue;
      }
      rows.push({
        id: typeof row.id === "string" && row.id ? row.id : uid("r"),
        settings: rowSettings(row.settings),
        zones: zonesOf(row.zones)
      });
    }
    const m = input.mobile && typeof input.mobile === "object" ? input.mobile : {};
    const elements = {};
    const srcElements = input.elements && typeof input.elements === "object" ? input.elements : {};
    for (const id of Object.keys(srcElements)) {
      const def = srcElements[id];
      if (!def || !ELEMENTS[def.type]) {
        continue;
      }
      const settings = clampSettings(
        def.type,
        Object.assign(
          defaultsFor(def.type, home),
          def.settings && typeof def.settings === "object" ? def.settings : {}
        )
      );
      elements[id] = { type: def.type, settings };
    }
    return {
      version: 1,
      rows,
      mobile: {
        zones: zonesOf(m.zones),
        offcanvas: idList(m.offcanvas),
        offcanvasDir: m.offcanvasDir === "left" ? "left" : "right",
        trigger: m.trigger === "left" ? "left" : "right",
        breakpoint: clampNum(m.breakpoint, 480, 1280, 780)
      },
      elements
    };
  }
  function mapZone(zones, index, fn) {
    return zones.map((list, i) => i === index ? fn(list) : list);
  }
  function insertInList(list, id, beforeId) {
    const next = list.filter((x) => x !== id);
    let at = beforeId != null ? next.indexOf(beforeId) : -1;
    if (at < 0) {
      at = next.length;
    }
    next.splice(at, 0, id);
    return next;
  }
  function insertAt(model, loc, id, beforeId) {
    if (!loc) {
      return model;
    }
    if (loc.area === "drawer") {
      return Object.assign({}, model, {
        mobile: Object.assign({}, model.mobile, {
          offcanvas: insertInList(model.mobile.offcanvas, id, beforeId)
        })
      });
    }
    if (loc.area === "mobile") {
      return Object.assign({}, model, {
        mobile: Object.assign({}, model.mobile, {
          zones: mapZone(model.mobile.zones, loc.zone, (list) => insertInList(list, id, beforeId))
        })
      });
    }
    return Object.assign({}, model, {
      rows: model.rows.map(
        (row) => row.id === loc.rowId ? Object.assign({}, row, {
          zones: mapZone(row.zones, loc.zone, (list) => insertInList(list, id, beforeId))
        }) : row
      )
    });
  }
  function removeOccurrence(model, from, id) {
    if (!from) {
      return model;
    }
    if (from.area === "drawer") {
      return Object.assign({}, model, {
        mobile: Object.assign({}, model.mobile, {
          offcanvas: model.mobile.offcanvas.filter((x) => x !== id)
        })
      });
    }
    if (from.area === "mobile") {
      return Object.assign({}, model, {
        mobile: Object.assign({}, model.mobile, {
          zones: mapZone(model.mobile.zones, from.zone, (list) => list.filter((x) => x !== id))
        })
      });
    }
    return Object.assign({}, model, {
      rows: model.rows.map(
        (row) => row.id === from.rowId ? Object.assign({}, row, {
          zones: mapZone(row.zones, from.zone, (list) => list.filter((x) => x !== id))
        }) : row
      )
    });
  }
  function isReferenced(model, id) {
    for (const row of model.rows) {
      for (const zone of row.zones) {
        if (zone.indexOf(id) > -1) {
          return true;
        }
      }
    }
    for (const zone of model.mobile.zones) {
      if (zone.indexOf(id) > -1) {
        return true;
      }
    }
    return model.mobile.offcanvas.indexOf(id) > -1;
  }
  function pruneOrphans(model) {
    const used = {};
    for (const row of model.rows) {
      for (const zone of row.zones) {
        for (const id of zone) {
          used[id] = true;
        }
      }
    }
    for (const zone of model.mobile.zones) {
      for (const id of zone) {
        used[id] = true;
      }
    }
    for (const id of model.mobile.offcanvas) {
      used[id] = true;
    }
    const elements = {};
    for (const id of Object.keys(model.elements)) {
      if (used[id]) {
        elements[id] = model.elements[id];
      }
    }
    return Object.assign({}, model, { elements });
  }
  function applyDrop(model, payload, loc, beforeId, presetId) {
    if (!payload) {
      return model;
    }
    let next = model;
    let id;
    if (payload.kind === "new") {
      if (!ELEMENTS[payload.type]) {
        return model;
      }
      id = presetId || uid("e");
      next = Object.assign({}, next, {
        elements: Object.assign({}, next.elements, {
          [id]: { type: payload.type, settings: defaultsFor(payload.type) }
        })
      });
    } else if (payload.kind === "move") {
      id = payload.id;
      if (!id || !next.elements[id]) {
        return model;
      }
      next = removeOccurrence(next, payload.from, id);
    } else {
      return model;
    }
    next = insertAt(next, loc, id, beforeId);
    return pruneOrphans(next);
  }
  function addElementWithId(model, type, id, device) {
    if (!ELEMENTS[type]) {
      return model;
    }
    let next = Object.assign({}, model, {
      elements: Object.assign({}, model.elements, {
        [id]: { type, settings: defaultsFor(type) }
      })
    });
    if (device === "mobile") {
      next = insertAt(next, { area: "drawer" }, id, null);
    } else {
      if (!next.rows.length) {
        next = addRow(next);
      }
      next = insertAt(next, { area: "desktop", rowId: next.rows[0].id, zone: 2 }, id, null);
    }
    return pruneOrphans(next);
  }
  function addRow(model) {
    if (model.rows.length >= MAX_ROWS) {
      return model;
    }
    return Object.assign({}, model, {
      rows: model.rows.concat([
        { id: uid("r"), settings: rowSettingsDefaults(), zones: [[], [], []] }
      ])
    });
  }
  function deleteRow(model, rowId) {
    return pruneOrphans(
      Object.assign({}, model, {
        rows: model.rows.filter((row) => row.id !== rowId)
      })
    );
  }
  function moveRow(model, rowId, dir) {
    const rows = model.rows.slice();
    const at = rows.findIndex((row) => row.id === rowId);
    const to = at + dir;
    if (at < 0 || to < 0 || to >= rows.length) {
      return model;
    }
    const tmp = rows[at];
    rows[at] = rows[to];
    rows[to] = tmp;
    return Object.assign({}, model, { rows });
  }
  function updateElementSettings(model, id, patch) {
    const def = model.elements[id];
    if (!def) {
      return model;
    }
    return Object.assign({}, model, {
      elements: Object.assign({}, model.elements, {
        [id]: Object.assign({}, def, {
          settings: Object.assign({}, def.settings, patch)
        })
      })
    });
  }
  function updateRowSettings(model, rowId, patch) {
    return Object.assign({}, model, {
      rows: model.rows.map(
        (row) => row.id === rowId ? Object.assign({}, row, { settings: Object.assign({}, row.settings, patch) }) : row
      )
    });
  }
  function updateMobile(model, patch) {
    return Object.assign({}, model, {
      mobile: Object.assign({}, model.mobile, patch)
    });
  }
  function moveInList(list, id, dir) {
    const at = list.indexOf(id);
    const to = at + dir;
    if (at < 0 || to < 0 || to >= list.length) {
      return list;
    }
    const next = list.slice();
    const tmp = next[at];
    next[at] = next[to];
    next[to] = tmp;
    return next;
  }

  // src/header-builder/SettingsPanel.jsx
  function SettingsPanel(props) {
    const { selectedId, model } = props;
    if (selectedId === "mobile") {
      return el(MobileForm, props);
    }
    if (typeof selectedId === "string" && selectedId.indexOf("row:") === 0) {
      const rowId = selectedId.slice(4);
      const row = model.rows.find((r) => r.id === rowId);
      if (row) {
        return el(RowForm, Object.assign({ row, rowId }, props));
      }
    }
    if (selectedId && model.elements[selectedId]) {
      return el(ElementForm, props);
    }
    return el(
      "div",
      { className: "khb-empty" },
      el(Icon, { name: "logo", size: 26 }),
      el("p", null, t("Select an element, a row, or mobile settings to edit."))
    );
  }
  function PanelHeader({ icon, label, meta }) {
    return el(
      "div",
      { className: "khb-panelhead" },
      el(Icon, { name: icon, size: 18 }),
      el("span", { className: "khb-panelhead__label" }, label),
      meta ? el("span", { className: "khb-panelhead__meta" }, meta) : null
    );
  }
  function ElementForm(props) {
    const { selectedId, model, setModel } = props;
    const def = model.elements[selectedId];
    const meta = ELEMENTS[def.type];
    const id = selectedId;
    const update = (patch, group) => setModel((m) => updateElementSettings(m, id, patch), group);
    const upd = (field) => (value) => update({ [field]: value }, "set:" + id + ":" + field);
    const shared = { def, id, settings: def.settings, upd, update, props };
    return el(
      "div",
      null,
      el(PanelHeader, { icon: meta.icon, label: t(meta.label) }),
      el(
        "div",
        { className: "khb-form" },
        def.type === "logo" ? el(LogoForm, shared) : def.type === "menu" ? el(MenuForm, shared) : def.type === "button" ? el(ButtonForm, shared) : def.type === "search" ? el(SearchForm, shared) : def.type === "social" ? el(SocialForm, shared) : def.type === "html" ? el(HtmlForm, shared) : def.type === "contact" ? el(ContactForm, shared) : def.type === "text" ? el(TextForm, shared) : def.type === "lang" ? el(LangForm, shared) : def.type === "account" ? el(AccountForm, shared) : def.type === "cart" ? el(CartForm, shared) : null
      )
    );
  }
  function LogoForm({ settings, upd, props }) {
    const { siteName, logoUrl } = props;
    const openMedia = () => {
      if (!(window.wp && window.wp.media)) {
        props.showToast(t("Media library is not available."), "error");
        return;
      }
      const frame = window.wp.media({
        title: t("Choose Image"),
        multiple: false,
        library: { type: "image" }
      });
      frame.on("select", () => {
        const att = frame.state().get("selection").first().toJSON();
        upd("imgUrl")(att.url);
        upd("imgId")(att.id || 0);
      });
      frame.open();
    };
    const preview = settings.source === "image" && settings.imgUrl ? settings.imgUrl : settings.source === "site" ? logoUrl || "" : "";
    return el(
      "div",
      null,
      el(SegRow, {
        label: t("Source"),
        value: settings.source,
        onChange: upd("source"),
        options: [
          { value: "site", label: t("Site identity") },
          { value: "text", label: t("Text") },
          { value: "image", label: t("Image") }
        ]
      }),
      settings.source === "text" ? el(
        "div",
        null,
        el(TextRow, {
          label: t("Logo text"),
          value: settings.text,
          placeholder: siteName || "",
          onChange: upd("text")
        }),
        el(TextRow, {
          label: t("Tagline"),
          value: settings.tagline,
          onChange: upd("tagline")
        }),
        el(ToggleRow, {
          label: t("Show tagline"),
          checked: settings.taglineShow,
          onChange: upd("taglineShow")
        })
      ) : null,
      settings.source === "image" ? el(
        "div",
        null,
        el(
          "div",
          { className: "khb-media" },
          preview ? el("img", {
            className: "khb-media__thumb",
            src: preview,
            alt: ""
          }) : null,
          el(
            "div",
            { className: "khb-media__actions" },
            el(
              "button",
              { type: "button", className: "khb-btn", onClick: openMedia },
              el(Icon, { name: "image", size: 15 }),
              el("span", null, preview ? t("Replace Image") : t("Choose Image"))
            ),
            settings.imgUrl ? el(
              "button",
              {
                type: "button",
                className: "khb-btn",
                onClick: () => {
                  upd("imgUrl")("");
                  upd("imgId")(0);
                }
              },
              t("Remove Image")
            ) : null
          )
        ),
        el(RangeRow, {
          label: t("Logo width"),
          value: settings.imgWidth,
          min: 40,
          max: 400,
          step: 2,
          unit: "px",
          onChange: upd("imgWidth")
        })
      ) : null,
      settings.source === "site" ? el(
        Note,
        null,
        t("Uses the site logo from Appearance \u2192 Settings.") + (siteName ? " " + t("Site name") + ": " + siteName : "")
      ) : null
    );
  }
  function MenuForm({ settings, upd, props }) {
    const menuOptions = [{ value: "0", label: t("Auto (first menu)") }].concat(
      (props.menus || []).map((m) => ({ value: String(m.id), label: m.name }))
    );
    return el(
      "div",
      null,
      el(SelectRow, {
        label: t("Menu"),
        value: settings.menuId,
        options: menuOptions,
        onChange: (v) => upd("menuId")(Number(v) || 0)
      }),
      el(RangeRow, {
        label: t("Font size"),
        value: settings.fontSize,
        min: 10,
        max: 32,
        unit: "px",
        onChange: upd("fontSize")
      }),
      el(RangeRow, {
        label: t("Gap"),
        value: settings.gap,
        min: 0,
        max: 96,
        unit: "px",
        onChange: upd("gap")
      }),
      el(SegRow, {
        label: t("Alignment"),
        value: settings.align,
        onChange: upd("align"),
        options: [
          { value: "left", label: t("Left") },
          { value: "center", label: t("Center") },
          { value: "right", label: t("Right") }
        ]
      }),
      el(ColorRow, { label: t("Link color"), value: settings.color, onChange: upd("color") }),
      el(ColorRow, {
        label: t("Hover color"),
        value: settings.hoverColor,
        onChange: upd("hoverColor")
      }),
      el(SegRow, {
        label: t("Menu style"),
        value: settings.style,
        onChange: upd("style"),
        options: [
          { value: "plain", label: t("Plain") },
          { value: "underline", label: t("Underline") },
          { value: "pill", label: t("Pill") }
        ]
      }),
      el(ToggleRow, {
        label: t("Submenu indicator"),
        checked: settings.subIndicator,
        onChange: upd("subIndicator")
      })
    );
  }
  function ButtonForm({ settings, upd }) {
    return el(
      "div",
      null,
      el(TextRow, { label: t("Button text"), value: settings.text, onChange: upd("text") }),
      el(UrlRow, { label: t("URL"), value: settings.url, onChange: upd("url") }),
      el(SegRow, {
        label: t("Variant"),
        value: settings.variant,
        onChange: upd("variant"),
        options: [
          { value: "gold", label: t("Gold") },
          { value: "outline", label: t("Outline") },
          { value: "ghost", label: t("Ghost") },
          { value: "dark", label: t("Dark") },
          { value: "light", label: t("Light") }
        ]
      }),
      el(SegRow, {
        label: t("Size"),
        value: settings.size,
        onChange: upd("size"),
        options: [
          { value: "s", label: t("Small") },
          { value: "m", label: t("Medium") }
        ]
      }),
      el(ToggleRow, {
        label: t("Open in new tab"),
        checked: settings.target,
        onChange: upd("target")
      })
    );
  }
  function SearchForm({ settings, upd }) {
    return el(
      "div",
      null,
      el(SegRow, {
        label: t("Search style"),
        value: settings.style,
        onChange: upd("style"),
        options: [
          { value: "icon", label: t("Icon") },
          { value: "field", label: t("Field") }
        ]
      }),
      el(TextRow, {
        label: t("Placeholder"),
        value: settings.placeholder,
        onChange: upd("placeholder")
      }),
      el(RangeRow, {
        label: t("Field width"),
        help: settings.style === "icon" ? t("Applies to the field style.") : void 0,
        value: settings.fieldWidth,
        min: 120,
        max: 420,
        step: 2,
        unit: "px",
        onChange: upd("fieldWidth")
      })
    );
  }
  function SocialForm({ settings, update, id }) {
    const items = Array.isArray(settings.items) ? settings.items : [];
    const setItems = (next, group) => update({ items: next }, group);
    const iconOptions = SOCIAL_ICONS.map(([value, label]) => ({
      value,
      label: t(label)
    }));
    const moveAt = (i, dir) => {
      const to = i + dir;
      if (to < 0 || to >= items.length) {
        return;
      }
      const next = items.slice();
      const tmp = next[i];
      next[i] = next[to];
      next[to] = tmp;
      setItems(next);
    };
    return el(
      "div",
      null,
      el("div", { className: "khb-field" }, el("label", { className: "khb-field__label" }, t("Social links"))),
      items.length === 0 ? el("p", { className: "khb-help" }, t("No icons yet \u2014 click \u201CAdd Icon\u201D to create one.")) : null,
      items.map(
        (item, i) => el(
          "div",
          { className: "khb-item", key: i },
          el(
            "div",
            { className: "khb-item__tools" },
            el(IconButton, {
              icon: "chevronUp",
              label: t("Move up"),
              disabled: i === 0,
              onClick: () => moveAt(i, -1)
            }),
            el(IconButton, {
              icon: "chevronDown",
              label: t("Move down"),
              disabled: i === items.length - 1,
              onClick: () => moveAt(i, 1)
            }),
            el(IconButton, {
              icon: "close",
              label: t("Remove item"),
              tone: "danger",
              onClick: () => setItems(items.filter((x, j) => j !== i))
            })
          ),
          el(
            "select",
            {
              className: "khb-select",
              value: item.icon,
              "aria-label": t("Icon"),
              onChange: (e) => setItems(
                items.map((x, j) => j === i ? Object.assign({}, x, { icon: e.target.value }) : x)
              )
            },
            iconOptions.map(
              (o) => el("option", { key: o.value, value: o.value }, o.label)
            )
          ),
          el("input", {
            className: "khb-input",
            type: "url",
            value: item.url || "",
            placeholder: "https://",
            "aria-label": t("URL"),
            onChange: (e) => setItems(
              items.map((x, j) => j === i ? Object.assign({}, x, { url: e.target.value }) : x),
              "set:" + id + ":items:" + i + ":url"
            )
          })
        )
      ),
      el(
        "button",
        {
          type: "button",
          className: "khb-btn khb-btn--block",
          onClick: () => setItems(items.concat([{ icon: "whatsapp", url: "" }]))
        },
        el(Icon, { name: "plus", size: 15 }),
        el("span", null, t("Add Icon"))
      ),
      el(RangeRow, {
        label: t("Icon size"),
        value: settings.size,
        min: 12,
        max: 32,
        unit: "px",
        onChange: (v) => update({ size: v }, "set:" + id + ":size")
      }),
      el(RangeRow, {
        label: t("Icon gap"),
        value: settings.gap,
        min: 4,
        max: 40,
        unit: "px",
        onChange: (v) => update({ gap: v }, "set:" + id + ":gap")
      })
    );
  }
  function HtmlForm({ settings, upd, props }) {
    return el(
      "div",
      null,
      el(TextareaRow, {
        label: t("HTML content"),
        value: settings.content,
        rows: 8,
        mono: true,
        placeholder: "<span>\u2026</span>",
        onChange: upd("content")
      }),
      props.canUnfiltered ? null : el(Note, null, t("Will be filtered in safe mode because your account lacks the unfiltered_html capability."))
    );
  }
  function ContactForm({ settings, upd }) {
    return el(
      "div",
      null,
      el(TextRow, { label: t("Phone number"), value: settings.phone, onChange: upd("phone") }),
      el(TextRow, { label: t("Email address"), value: settings.email, onChange: upd("email") }),
      el(TextRow, { label: t("Address"), value: settings.address, onChange: upd("address") }),
      el(ToggleRow, { label: t("Show phone"), checked: settings.showPhone, onChange: upd("showPhone") }),
      el(ToggleRow, { label: t("Show email"), checked: settings.showEmail, onChange: upd("showEmail") }),
      el(ToggleRow, { label: t("Show address"), checked: settings.showAddress, onChange: upd("showAddress") })
    );
  }
  function TextForm({ settings, upd }) {
    return el(
      "div",
      null,
      el(TextareaRow, {
        label: t("Content"),
        value: settings.content,
        onChange: upd("content")
      }),
      el(RangeRow, {
        label: t("Font size"),
        value: settings.size,
        min: 10,
        max: 40,
        unit: "px",
        onChange: upd("size")
      }),
      el(ColorRow, { label: t("Color"), value: settings.color, onChange: upd("color") })
    );
  }
  function LangForm({ settings, upd }) {
    return el(SegRow, {
      label: t("Language style"),
      value: settings.style,
      onChange: upd("style"),
      options: [
        { value: "code", label: t("Code") },
        { value: "name", label: t("Name") }
      ]
    });
  }
  function AccountForm({ settings, upd }) {
    return el(
      "div",
      null,
      el(TextRow, { label: t("Login text"), value: settings.loginText, onChange: upd("loginText") }),
      el(UrlRow, { label: t("Login URL"), value: settings.url, onChange: upd("url") })
    );
  }
  function CartForm({ settings, upd, props }) {
    return el(
      "div",
      null,
      props.woocommerce ? null : el(Note, null, t("Requires WooCommerce")),
      el(ToggleRow, {
        label: t("Show item count"),
        checked: settings.showCount,
        onChange: upd("showCount")
      })
    );
  }
  function RowForm({ row, rowId, setModel }) {
    const s = row.settings;
    const upd = (field) => (value) => setModel(
      (m) => updateRowSettings(m, rowId, { [field]: value }),
      "set:row:" + rowId + ":" + field
    );
    return el(
      "div",
      null,
      el(PanelHeader, { icon: "menu", label: t("Row settings") }),
      el(
        "div",
        { className: "khb-form" },
        el(ColorRow, { label: t("Background color"), value: s.bg, onChange: upd("bg") }),
        el(ColorRow, { label: t("Text color"), value: s.textColor, onChange: upd("textColor") }),
        el(RangeRow, {
          label: t("Vertical padding"),
          value: s.padY,
          min: 0,
          max: 96,
          unit: "px",
          onChange: upd("padY")
        }),
        el(ToggleRow, {
          label: t("Bottom border"),
          checked: s.borderBottom,
          onChange: upd("borderBottom")
        }),
        el(ToggleRow, {
          label: t("Sticky"),
          help: t("When any row is sticky, the whole header stays pinned while scrolling."),
          checked: s.sticky,
          onChange: upd("sticky")
        }),
        el(ToggleRow, {
          label: t("Transparent"),
          help: t("Transparent rows overlay the page content \u2014 great with hero sections."),
          checked: s.transparent,
          onChange: upd("transparent")
        })
      )
    );
  }
  function MobileForm({ model, setModel }) {
    const m = model.mobile;
    const upd = (field) => (value) => setModel((mm) => updateMobile(mm, { [field]: value }), "set:mobile:" + field);
    const drawerRemove = (id) => setModel((mm) => pruneOrphans(removeOccurrence(mm, { area: "drawer" }, id)));
    const drawerMove = (id, dir) => setModel((mm) => updateMobile(mm, { offcanvas: moveInList(mm.mobile.offcanvas, id, dir) }));
    return el(
      "div",
      null,
      el(PanelHeader, { icon: "mobile", label: t("Mobile settings") }),
      el(
        "div",
        { className: "khb-form" },
        el(SegRow, {
          label: t("Hamburger side"),
          value: m.trigger,
          onChange: upd("trigger"),
          options: [
            { value: "left", label: t("Left") },
            { value: "right", label: t("Right") }
          ]
        }),
        el(SegRow, {
          label: t("Drawer side"),
          value: m.offcanvasDir,
          onChange: upd("offcanvasDir"),
          options: [
            { value: "left", label: t("Left") },
            { value: "right", label: t("Right") }
          ]
        }),
        el(RangeRow, {
          label: t("Breakpoint"),
          help: t("Below this viewport width the mobile header is shown."),
          value: m.breakpoint,
          min: 480,
          max: 1280,
          step: 10,
          unit: "px",
          onChange: upd("breakpoint")
        }),
        el("div", { className: "khb-field" }, el("label", { className: "khb-field__label" }, t("Drawer elements"))),
        m.offcanvas.length === 0 ? el("p", { className: "khb-help" }, t("Drawer is empty. Drag elements here.")) : null,
        el(
          "ul",
          { className: "khb-drawerlist", role: "list" },
          m.offcanvas.map((id, i) => {
            const def = model.elements[id];
            const meta = def ? ELEMENTS[def.type] : null;
            return el(
              "li",
              { className: "khb-drawerlist__item", key: id },
              el(Icon, { name: meta ? meta.icon : "alert", size: 16 }),
              el("span", { className: "khb-drawerlist__name" }, meta ? t(meta.label) : t("Unknown element")),
              el(
                "span",
                { className: "khb-drawerlist__tools" },
                el(IconButton, {
                  icon: "chevronUp",
                  label: t("Move up"),
                  disabled: i === 0,
                  onClick: () => drawerMove(id, -1)
                }),
                el(IconButton, {
                  icon: "chevronDown",
                  label: t("Move down"),
                  disabled: i === m.offcanvas.length - 1,
                  onClick: () => drawerMove(id, 1)
                }),
                el(IconButton, {
                  icon: "close",
                  label: t("Remove item"),
                  tone: "danger",
                  onClick: () => drawerRemove(id)
                })
              )
            );
          })
        )
      )
    );
  }

  // src/header-builder/Palette.jsx
  function Palette({
    tab,
    onTab,
    onAdd,
    onDragStart,
    onDragEnd,
    woocommerce,
    settings
  }) {
    return el(
      "aside",
      { className: "khb-side" },
      el(
        "div",
        { className: "khb-tabs", role: "tablist" },
        el(
          "button",
          {
            type: "button",
            role: "tab",
            "aria-selected": tab === "elements",
            className: "khb-tab" + (tab === "elements" ? " is-active" : ""),
            onClick: () => onTab("elements")
          },
          t("Elements")
        ),
        el(
          "button",
          {
            type: "button",
            role: "tab",
            "aria-selected": tab === "settings",
            className: "khb-tab" + (tab === "settings" ? " is-active" : ""),
            onClick: () => onTab("settings")
          },
          t("Settings")
        )
      ),
      el(
        "div",
        { className: "khb-side__body" },
        tab === "elements" ? el(ElementLibrary, { onAdd, onDragStart, onDragEnd, woocommerce }) : el(SettingsPanel, settings)
      )
    );
  }
  function ElementLibrary({ onAdd, onDragStart, onDragEnd, woocommerce }) {
    return el(
      "div",
      null,
      el("p", { className: "khb-side__hint" }, t("Drag an element onto the structure bar below, or click to add it.")),
      el(
        "div",
        { className: "khb-cards" },
        ELEMENT_TYPES.map((type) => {
          const meta = ELEMENTS[type];
          const disabled = type === "cart" && !woocommerce;
          return el(
            "div",
            {
              key: type,
              className: "khb-card" + (disabled ? " is-disabled" : ""),
              draggable: !disabled,
              role: "button",
              tabIndex: disabled ? -1 : 0,
              "aria-disabled": disabled || void 0,
              "aria-label": t(meta.label),
              onClick: () => {
                if (!disabled) {
                  onAdd(type);
                }
              },
              onKeyDown: (e) => {
                if (!disabled && (e.key === "Enter" || e.key === " ")) {
                  e.preventDefault();
                  onAdd(type);
                }
              },
              onDragStart: (e) => {
                if (disabled) {
                  e.preventDefault();
                  return;
                }
                onDragStart(e, type);
              },
              onDragEnd
            },
            el(Icon, { name: meta.icon, size: 22 }),
            el("span", { className: "khb-card__name" }, t(meta.label)),
            disabled ? el("span", { className: "khb-card__note" }, t("Requires WooCommerce")) : null
          );
        })
      )
    );
  }

  // src/header-builder/api.js
  var DATA = typeof window !== "undefined" && window.KabelHBData || {};
  async function request(method, body) {
    if (!DATA.rest) {
      throw new Error("REST endpoint is not configured.");
    }
    const res = await fetch(DATA.rest, {
      method,
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": DATA.restNonce || ""
      },
      body: body ? JSON.stringify(body) : void 0
    });
    if (!res.ok) {
      throw new Error("HTTP " + res.status);
    }
    const data = await res.json().catch(() => null);
    if (!data || data.ok !== true) {
      throw new Error("Unexpected response.");
    }
    return data;
  }
  function saveModel(model) {
    return request("POST", { model, draft: false });
  }
  function saveDraft(model) {
    return request("POST", { model, draft: true });
  }
  function clearModel() {
    return request("DELETE");
  }

  // src/header-builder/PreviewFrame.jsx
  function buildSrc(home, previewNonce) {
    const base = home || "/";
    const join = base.indexOf("?") > -1 ? "&" : "?";
    return base + join + "kabel-hb-preview=" + encodeURIComponent(previewNonce || "") + "&t=" + Date.now();
  }
  function PreviewFrame({ model, home, previewNonce, device }) {
    const [src, setSrc] = useState(null);
    const [error, setError] = useState("");
    const [flash, setFlash] = useState(0);
    const frameRef = useRef(null);
    const jobRef = useRef(0);
    const srcRef = useRef(null);
    const firstRef = useRef(true);
    const navigate = (url) => {
      if (!srcRef.current) {
        srcRef.current = url;
        setSrc(url);
        return;
      }
      const frame = frameRef.current;
      if (frame && frame.contentWindow) {
        try {
          frame.contentWindow.location.replace(url);
          return;
        } catch (err) {
        }
      }
      srcRef.current = url;
      setSrc(url);
    };
    useEffect(() => {
      const run = () => {
        const job = ++jobRef.current;
        saveDraft(model).then(() => {
          if (job !== jobRef.current) {
            return;
          }
          setError("");
          navigate(buildSrc(home, previewNonce));
          setFlash(Date.now());
        }).catch(() => {
          if (job !== jobRef.current) {
            return;
          }
          setError(t("Preview update failed."));
          if (!srcRef.current) {
            navigate(buildSrc(home, previewNonce));
          }
        });
      };
      const delay = firstRef.current ? 60 : 700;
      firstRef.current = false;
      const timer = setTimeout(run, delay);
      return () => clearTimeout(timer);
    }, [model]);
    useEffect(() => {
      if (!flash) {
        return;
      }
      const timer = setTimeout(() => setFlash(0), 1600);
      return () => clearTimeout(timer);
    }, [flash]);
    return el(
      "div",
      {
        className: "khb-preview" + (device === "mobile" ? " is-mobile" : "")
      },
      error ? el("div", { className: "khb-pverror", role: "alert" }, error) : null,
      flash ? el("div", { className: "khb-flash", "aria-live": "polite" }, t("Preview updated")) : null,
      src ? el(
        "div",
        { className: "khb-preview__holder" },
        el("iframe", {
          ref: frameRef,
          className: "khb-preview__frame",
          src,
          title: t("Live preview")
        })
      ) : el(
        "div",
        { className: "khb-preview__loading" },
        el("span", { className: "khb-spin is-dark", "aria-hidden": true }),
        el("span", null, t("Loading preview\u2026"))
      )
    );
  }

  // src/header-builder/StructureBar.jsx
  var ZONE_LABELS = ["Left", "Center", "Right"];
  function StructureBar({
    model,
    setModel,
    device,
    selectedId,
    onSelect,
    onOpenElements,
    onDropPayload,
    onRequestRowDelete,
    onRemoveChip,
    onMobileSettings,
    drag,
    setDrag
  }) {
    const [over, setOver] = useState(null);
    const setOverIfChanged = (next) => {
      setOver(
        (prev) => prev && prev.key === next.key && prev.beforeId === next.beforeId ? prev : next
      );
    };
    const chipFromEvent = (e) => {
      if (!e.target || !e.target.closest) {
        return null;
      }
      const chipEl = e.target.closest("[data-khb-chip]");
      return chipEl ? chipEl.getAttribute("data-khb-chip") : null;
    };
    const onZoneDragOver = (e, key) => {
      e.preventDefault();
      if (e.dataTransfer) {
        e.dataTransfer.dropEffect = drag && drag.kind === "new" ? "copy" : "move";
      }
      setOverIfChanged({ key, beforeId: chipFromEvent(e) });
    };
    const onZoneDragLeave = (e, key) => {
      if (!e.currentTarget.contains(e.relatedTarget)) {
        setOver((prev) => prev && prev.key === key ? null : prev);
      }
    };
    const onZoneDrop = (e, loc, key) => {
      e.preventDefault();
      setOver(null);
      const raw = e.dataTransfer.getData("application/x-kabel-hb");
      if (!raw) {
        return;
      }
      let payload;
      try {
        payload = JSON.parse(raw);
      } catch (err) {
        return;
      }
      onDropPayload(payload, loc, chipFromEvent(e));
    };
    const zoneProps = (loc, key) => ({
      onDragOver: (e) => onZoneDragOver(e, key),
      onDragLeave: (e) => onZoneDragLeave(e, key),
      onDrop: (e) => onZoneDrop(e, loc, key)
    });
    const chipProps = {
      model,
      selectedId,
      onSelect,
      setDrag
    };
    const removeChip = onRemoveChip;
    return el(
      "section",
      {
        className: "khb-structure",
        "aria-label": t("Structure")
      },
      device === "desktop" ? el(DesktopRows, {
        model,
        zoneProps,
        chipProps,
        over,
        onOpenElements,
        onRequestRowDelete,
        onSelect,
        removeChip,
        setModel
      }) : el(MobileBar, {
        model,
        zoneProps,
        chipProps,
        over,
        onOpenElements,
        onMobileSettings,
        removeChip,
        setModel
      })
    );
  }
  function Zone({ label, list, loc, keyName, over, zoneProps, chipProps, onOpenElements, removeChip, vertical }) {
    const isOver = over && over.key === keyName;
    return el(
      "div",
      Object.assign(
        {
          className: "khb-zone" + (isOver ? " is-over" : "") + (vertical ? " is-vertical" : ""),
          "data-zone": keyName,
          role: "list",
          "aria-label": label
        },
        zoneProps(loc, keyName)
      ),
      el("span", { className: "khb-zone__label" }, label),
      list.map(
        (id) => el(Chip, Object.assign({ key: id, id, from: loc, onRemove: removeChip }, chipProps))
      ),
      el(
        "button",
        {
          type: "button",
          className: "khb-zone__add",
          "aria-label": t("Browse elements"),
          title: t("Browse elements"),
          onClick: onOpenElements
        },
        el(Icon, { name: "plus", size: 13 })
      )
    );
  }
  function Chip({ id, model, from, selectedId, onSelect, onRemove, setDrag }) {
    const def = model.elements[id];
    const meta = def ? ELEMENTS[def.type] : null;
    const label = meta ? t(meta.label) : t("Unknown element");
    return el(
      "div",
      {
        className: "khb-chip" + (selectedId === id ? " is-selected" : ""),
        draggable: true,
        "data-khb-chip": id,
        role: "button",
        tabIndex: 0,
        title: label,
        onClick: () => onSelect(id),
        onKeyDown: (e) => {
          if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            onSelect(id);
          }
        },
        onDragStart: (e) => {
          e.dataTransfer.setData(
            "application/x-kabel-hb",
            JSON.stringify({ kind: "move", id, from })
          );
          e.dataTransfer.effectAllowed = "copyMove";
          setDrag({ kind: "move", id, from });
        },
        onDragEnd: () => setDrag(null)
      },
      el(Icon, { name: meta ? meta.icon : "alert", size: 15 }),
      el("span", { className: "khb-chip__name" }, label),
      el(
        "button",
        {
          type: "button",
          className: "khb-chip__x",
          "aria-label": t("Remove from this zone"),
          title: t("Remove from this zone"),
          onClick: (e) => {
            e.stopPropagation();
            onRemove(id, from);
          }
        },
        el(Icon, { name: "close", size: 11 })
      )
    );
  }
  function DesktopRows({
    model,
    zoneProps,
    chipProps,
    over,
    onOpenElements,
    onRequestRowDelete,
    onSelect,
    removeChip,
    setModel
  }) {
    return el(
      "div",
      { className: "khb-rows" },
      model.rows.map(
        (row, i) => el(
          "div",
          { className: "khb-row", key: row.id },
          el(
            "div",
            { className: "khb-row__head" },
            el("span", { className: "khb-row__label" }, t("Row {n}").replace("{n}", String(i + 1))),
            el(
              "span",
              { className: "khb-row__tools" },
              el(IconButton, {
                icon: "gear",
                label: t("Row settings"),
                tone: "dark",
                onClick: () => onSelect("row:" + row.id)
              }),
              el(IconButton, {
                icon: "chevronUp",
                label: t("Move up"),
                tone: "dark",
                disabled: i === 0,
                onClick: () => setModel((m) => moveRow(m, row.id, -1))
              }),
              el(IconButton, {
                icon: "chevronDown",
                label: t("Move down"),
                tone: "dark",
                disabled: i === model.rows.length - 1,
                onClick: () => setModel((m) => moveRow(m, row.id, 1))
              }),
              el(IconButton, {
                icon: "trash",
                label: t("Delete row"),
                tone: "danger",
                onClick: () => onRequestRowDelete(row.id)
              })
            )
          ),
          el(
            "div",
            { className: "khb-zones" },
            [0, 1, 2].map(
              (z) => el(Zone, {
                key: row.id + ":" + z,
                keyName: "desktop:" + row.id + ":" + z,
                label: t(ZONE_LABELS[z]),
                list: row.zones[z],
                loc: { area: "desktop", rowId: row.id, zone: z },
                over,
                zoneProps,
                chipProps,
                onOpenElements,
                removeChip
              })
            )
          )
        )
      ),
      el(
        "button",
        {
          type: "button",
          className: "khb-addrow",
          disabled: model.rows.length >= MAX_ROWS,
          title: model.rows.length >= MAX_ROWS ? t("Maximum of 3 rows reached.") : "",
          onClick: () => setModel((m) => addRow(m))
        },
        el(Icon, { name: "plus", size: 14 }),
        el("span", null, t("Add Row"))
      )
    );
  }
  function MobileBar({
    model,
    zoneProps,
    chipProps,
    over,
    onOpenElements,
    onMobileSettings,
    removeChip,
    setModel
  }) {
    const m = model.mobile;
    const upd = (field) => (value) => setModel((mm) => updateMobile(mm, { [field]: value }), "set:mobile:" + field);
    return el(
      "div",
      { className: "khb-mob" },
      el(
        "div",
        { className: "khb-mob__col" },
        el(
          "div",
          { className: "khb-mob__head" },
          el("span", null, t("Mobile Header")),
          el(IconButton, {
            icon: "gear",
            label: t("Mobile settings"),
            tone: "dark",
            onClick: onMobileSettings
          })
        ),
        el(
          "div",
          { className: "khb-zones" },
          [0, 1, 2].map(
            (z) => el(Zone, {
              key: "mobile:" + z,
              keyName: "mobile:" + z,
              label: t(ZONE_LABELS[z]),
              list: m.zones[z],
              loc: { area: "mobile", zone: z },
              over,
              zoneProps,
              chipProps,
              onOpenElements,
              removeChip
            })
          )
        )
      ),
      el(
        "div",
        { className: "khb-mob__col" },
        el("div", { className: "khb-mob__head" }, el("span", null, t("Drawer panel (opens via hamburger)"))),
        el(
          Zone,
          {
            keyName: "drawer",
            label: t("Drawer elements"),
            list: m.offcanvas,
            loc: { area: "drawer" },
            over,
            zoneProps,
            chipProps,
            onOpenElements,
            removeChip,
            vertical: true
          }
        )
      ),
      el(
        "div",
        { className: "khb-mob__col khb-mob__col--opts" },
        el(
          "div",
          { className: "khb-mini" },
          el("span", { className: "khb-mini__label" }, t("Hamburger side")),
          el(Segmented, {
            small: true,
            value: m.trigger,
            onChange: upd("trigger"),
            ariaLabel: t("Hamburger side"),
            options: [
              { value: "left", label: t("Left") },
              { value: "right", label: t("Right") }
            ]
          })
        ),
        el(
          "div",
          { className: "khb-mini" },
          el("span", { className: "khb-mini__label" }, t("Drawer side")),
          el(Segmented, {
            small: true,
            value: m.offcanvasDir,
            onChange: upd("offcanvasDir"),
            ariaLabel: t("Drawer side"),
            options: [
              { value: "left", label: t("Left") },
              { value: "right", label: t("Right") }
            ]
          })
        ),
        el(
          "div",
          { className: "khb-mini" },
          el("span", { className: "khb-mini__label" }, t("Breakpoint")),
          el(
            "div",
            { className: "khb-rangerow" },
            el("input", {
              className: "khb-range",
              type: "range",
              min: 480,
              max: 1280,
              step: 10,
              value: m.breakpoint,
              "aria-label": t("Breakpoint"),
              onChange: (e) => upd("breakpoint")(Number(e.target.value))
            }),
            el(NumberInput, {
              value: m.breakpoint,
              min: 480,
              max: 1280,
              step: 10,
              unit: "px",
              ariaLabel: t("Breakpoint"),
              onCommit: upd("breakpoint")
            })
          )
        )
      )
    );
  }

  // src/header-builder/history.js
  var MAX_HISTORY = 60;
  var COALESCE_MS = 900;
  function useHistory(initial) {
    const [hist, setHist] = useState({
      current: initial,
      past: [],
      future: [],
      last: null
    });
    const set = useCallback((updater, group) => {
      setHist((prev) => {
        const next = typeof updater === "function" ? updater(prev.current) : updater;
        if (next === prev.current) {
          return prev;
        }
        const now = Date.now();
        const coalesce = !!group && !!prev.last && prev.last.group === group && now - prev.last.at < COALESCE_MS;
        return {
          current: next,
          past: coalesce ? prev.past : prev.past.concat([prev.current]).slice(-MAX_HISTORY),
          future: [],
          last: { group: group || null, at: now }
        };
      });
    }, []);
    const undo = useCallback(() => {
      setHist((prev) => {
        if (!prev.past.length) {
          return prev;
        }
        return {
          current: prev.past[prev.past.length - 1],
          past: prev.past.slice(0, -1),
          future: [prev.current].concat(prev.future).slice(0, MAX_HISTORY),
          last: null
        };
      });
    }, []);
    const redo = useCallback(() => {
      setHist((prev) => {
        if (!prev.future.length) {
          return prev;
        }
        return {
          current: prev.future[0],
          past: prev.past.concat([prev.current]).slice(-MAX_HISTORY),
          future: prev.future.slice(1),
          last: null
        };
      });
    }, []);
    const replace = useCallback((next) => {
      setHist({ current: next, past: [], future: [], last: null });
    }, []);
    return {
      model: hist.current,
      set,
      undo,
      redo,
      replace,
      canUndo: hist.past.length > 0,
      canRedo: hist.future.length > 0
    };
  }

  // src/header-builder/App.jsx
  function App({ data }) {
    const initialModel = useMemo(
      () => normalizeModel(data.model, data),
      []
      // eslint-disable-line react-hooks/exhaustive-deps
    );
    const hist = useHistory(initialModel);
    const model = hist.model;
    const [selectedId, setSelectedId] = useState(null);
    const [device, setDevice] = useState("desktop");
    const [tab, setTab] = useState("elements");
    const [saving, setSaving] = useState(false);
    const [liveSource, setLiveSource] = useState(data.liveSource || "classic");
    const [toast, setToast] = useState(null);
    const [confirmState, setConfirmState] = useState(null);
    const [drag, setDrag] = useState(null);
    const savedRef = useRef(JSON.stringify(initialModel));
    const dirty = useMemo(() => JSON.stringify(model) !== savedRef.current, [model]);
    const showToast = useCallback((message, tone) => {
      setToast({ message, tone, key: Date.now() });
    }, []);
    useEffect(() => {
      if (!toast) {
        return;
      }
      const timer = setTimeout(() => setToast(null), 2600);
      return () => clearTimeout(timer);
    }, [toast]);
    useEffect(() => {
      if (!dirty) {
        return;
      }
      const onBeforeUnload = (e) => {
        e.preventDefault();
        e.returnValue = t("Unsaved changes");
        return e.returnValue;
      };
      window.addEventListener("beforeunload", onBeforeUnload);
      return () => window.removeEventListener("beforeunload", onBeforeUnload);
    }, [dirty]);
    useEffect(() => {
      const onKey = (e) => {
        if (!(e.ctrlKey || e.metaKey) || e.key.toLowerCase() !== "z") {
          return;
        }
        const target = e.target;
        const tag = target && target.tagName ? target.tagName : "";
        if (tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT" || target && target.isContentEditable) {
          return;
        }
        e.preventDefault();
        if (e.shiftKey) {
          hist.redo();
        } else {
          hist.undo();
        }
      };
      window.addEventListener("keydown", onKey);
      return () => window.removeEventListener("keydown", onKey);
    }, [hist]);
    useEffect(() => {
      if (!drag) {
        return;
      }
      const clear = () => setDrag(null);
      window.addEventListener("dragend", clear);
      window.addEventListener("drop", clear);
      return () => {
        window.removeEventListener("dragend", clear);
        window.removeEventListener("drop", clear);
      };
    }, [drag]);
    const select = useCallback((id) => {
      setSelectedId(id);
      setTab("settings");
    }, []);
    const openElements = useCallback(() => setTab("elements"), []);
    const switchDevice = useCallback((d) => {
      setDevice(d);
      setSelectedId((cur) => cur == null && d === "mobile" ? "mobile" : cur);
    }, []);
    const addFromPalette = useCallback(
      (type) => {
        const id = uid("e");
        hist.set((m) => addElementWithId(m, type, id, device));
        setSelectedId(id);
        setTab("settings");
      },
      [hist, device]
    );
    const onPaletteDragStart = useCallback((e, type) => {
      e.dataTransfer.setData("application/x-kabel-hb", JSON.stringify({ kind: "new", type }));
      e.dataTransfer.effectAllowed = "copy";
      setDrag({ kind: "new", type });
    }, []);
    const onDropPayload = useCallback(
      (payload, loc, beforeId) => {
        const newId = payload && payload.kind === "new" ? uid("e") : null;
        hist.set((m) => applyDrop(m, payload, loc, beforeId, newId));
        if (newId) {
          setSelectedId(newId);
        }
      },
      [hist]
    );
    const removeChip = useCallback(
      (id, from) => {
        const next = pruneOrphans(removeOccurrence(model, from, id));
        hist.set(next);
        setSelectedId((cur) => cur === id && !isReferenced(next, id) ? null : cur);
      },
      [hist, model]
    );
    const requestRowDelete = useCallback(
      (rowId) => {
        setConfirmState({
          title: t("Delete this row?"),
          message: t("The row and its element placements will be removed. Elements used elsewhere stay."),
          confirmLabel: t("Delete"),
          danger: true,
          action: () => {
            hist.set((m) => deleteRow(m, rowId));
            setSelectedId((cur) => cur === "row:" + rowId ? null : cur);
          }
        });
      },
      [hist]
    );
    const requestClear = useCallback(() => {
      setConfirmState({
        title: t("Clear builder data?"),
        message: t("This removes the saved header model. The site falls back to the blocks header or the classic header. This cannot be undone."),
        confirmLabel: t("Clear"),
        danger: true,
        action: async () => {
          try {
            await clearModel();
            window.location.reload();
          } catch (err) {
            showToast(t("Delete failed."), "error");
          }
        }
      });
    }, [showToast]);
    const doSave = useCallback(async () => {
      if (saving) {
        return;
      }
      setSaving(true);
      try {
        const res = await saveModel(model);
        const clean = normalizeModel(res.model, data);
        hist.replace(clean);
        savedRef.current = JSON.stringify(clean);
        if (res.liveSource) {
          setLiveSource(res.liveSource);
        }
        showToast(t("Saved"));
      } catch (err) {
        showToast(t("Save failed."), "error");
      }
      setSaving(false);
    }, [saving, model, hist, data, showToast]);
    const settingsProps = {
      selectedId,
      model,
      setModel: hist.set,
      menus: data.menus || [],
      canUnfiltered: !!data.canUnfiltered,
      siteName: data.siteName || "",
      logoUrl: data.logoUrl || "",
      woocommerce: !!data.woocommerce,
      home: data.home || "",
      showToast
    };
    return el(
      "div",
      { className: "khb-app" + (drag ? " is-dragging" : "") },
      el(TopBar, {
        liveSource,
        canUndo: hist.canUndo,
        canRedo: hist.canRedo,
        onUndo: hist.undo,
        onRedo: hist.redo,
        device,
        onDevice: switchDevice,
        dirty,
        saving,
        onSave: doSave,
        onClearRequest: requestClear,
        home: data.home || "",
        headerBlocksEdit: data.headerBlocksEdit || ""
      }),
      el(
        "div",
        { className: "khb-main" },
        el(Palette, {
          tab,
          onTab: setTab,
          onAdd: addFromPalette,
          onDragStart: onPaletteDragStart,
          onDragEnd: () => setDrag(null),
          woocommerce: !!data.woocommerce,
          settings: settingsProps
        }),
        el(
          "div",
          { className: "khb-stage" },
          el(PreviewFrame, {
            model,
            home: data.home || "",
            previewNonce: data.previewNonce || "",
            device
          }),
          el(StructureBar, {
            model,
            setModel: hist.set,
            device,
            selectedId,
            onSelect: select,
            onOpenElements: openElements,
            onDropPayload,
            onRequestRowDelete: requestRowDelete,
            onRemoveChip: removeChip,
            onMobileSettings: () => select("mobile"),
            drag,
            setDrag
          })
        )
      ),
      toast ? el(Toast, { key: toast.key, message: toast.message, tone: toast.tone }) : null,
      confirmState ? el(ConfirmDialog, {
        key: "confirm",
        title: confirmState.title,
        message: confirmState.message,
        confirmLabel: confirmState.confirmLabel,
        danger: confirmState.danger,
        onCancel: () => setConfirmState(null),
        onConfirm: () => {
          const action = confirmState.action;
          setConfirmState(null);
          if (action) {
            action();
          }
        }
      }) : null
    );
  }

  // src/header-builder/index.jsx
  function mount() {
    const root = document.getElementById("kabel-hb-root");
    if (!root) {
      return;
    }
    if (!window.KabelHBData) {
      const notice = document.createElement("div");
      notice.className = "khb-boot-error";
      notice.textContent = t("Setup error: KabelHBData is missing, the Header Builder cannot start.");
      root.appendChild(notice);
      return;
    }
    const app = el(App, { data: window.KabelHBData });
    if (createRoot) {
      createRoot(root).render(app);
    } else if (render) {
      render(app, root);
    }
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mount, { once: true });
  } else {
    mount();
  }
})();
