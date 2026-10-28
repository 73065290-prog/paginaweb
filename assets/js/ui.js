(() => {
  // Solo UI: carrito drawer, countdown, qty +/- y toast flash
  const openCart = document.getElementById("openCart");
  const closeCart = document.getElementById("closeCart");
  const overlay = document.getElementById("overlay");
  const drawer = document.getElementById("cartDrawer");
  const toast = document.getElementById("toast");

  function setCart(open) {
    overlay?.classList.toggle("open", open);
    drawer?.classList.toggle("open", open);
    document.body.style.overflow = open ? "hidden" : "";
  }

  openCart?.addEventListener("click", () => setCart(true));
  closeCart?.addEventListener("click", () => setCart(false));
  overlay?.addEventListener("click", () => setCart(false));

  // Desplegable Mi Cuenta (cliente / admin en storefront)
  const cuentaWrap = document.querySelector(".cuenta-wrap");
  const cuentaBtn = document.getElementById("cuentaBtn");
  const cuentaMenu = document.getElementById("cuentaMenu");
  const setCuentaOpen = (open) => {
    if (!cuentaWrap || !cuentaBtn || !cuentaMenu) return;
    cuentaWrap.classList.toggle("is-open", open);
    cuentaBtn.setAttribute("aria-expanded", open ? "true" : "false");
    cuentaMenu.hidden = !open;
  };
  cuentaBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    setCuentaOpen(!!cuentaMenu?.hidden);
  });
  document.addEventListener("click", (e) => {
    if (!cuentaWrap?.contains(e.target)) setCuentaOpen(false);
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") setCuentaOpen(false);
  });

  if (toast?.classList.contains("show")) {
    setTimeout(() => toast.classList.remove("show"), 2200);
  }

  // Countdown ofertas bomba
  const root = document.getElementById("countdown");
  if (root) {
    const end = Date.now() + 8 * 3600000 + 34 * 60000 + 11 * 1000;
    const tick = () => {
      const d = Math.max(0, end - Date.now());
      const h = root.querySelector('[data-unit="h"]');
      const m = root.querySelector('[data-unit="m"]');
      const s = root.querySelector('[data-unit="s"]');
      if (h) h.textContent = String(Math.floor(d / 3600000)).padStart(2, "0");
      if (m) m.textContent = String(Math.floor((d % 3600000) / 60000)).padStart(2, "0");
      if (s) s.textContent = String(Math.floor((d % 60000) / 1000)).padStart(2, "0");
    };
    tick();
    setInterval(tick, 1000);
  }

  // Qty +/- en formularios de producto
  document.addEventListener("click", (e) => {
    const minus = e.target.closest(".qty-minus");
    const plus = e.target.closest(".qty-plus");
    if (!minus && !plus) return;
    e.preventDefault();
    const wrap = (minus || plus).closest(".qty");
    const input = wrap?.querySelector(".qty-input");
    if (!input) return;
    let val = Number(input.value) || 1;
    val = minus ? Math.max(1, val - 1) : Math.min(99, val + 1);
    input.value = String(val);
  });

  // Sync range -> max input
  const range = document.getElementById("priceMaxRange");
  const maxInput = document.getElementById("priceMax");
  range?.addEventListener("input", () => {
    if (maxInput) maxInput.value = range.value;
  });

  // Filtros mobile toggle
  document.getElementById("filtersToggle")?.addEventListener("click", () => {
    const panel = document.getElementById("filtersPanel");
    const btn = document.getElementById("filtersToggle");
    panel?.classList.toggle("open");
    if (btn) {
      btn.textContent = panel?.classList.contains("open") ? "✕ Ocultar filtros" : "☰ Mostrar filtros";
    }
  });

  // PDP: galería miniaturas
  const mainImg = document.getElementById("pdpMainImg");
  document.querySelectorAll(".pdp-thumb").forEach((thumb) => {
    thumb.addEventListener("click", () => {
      const src = thumb.getAttribute("data-src");
      if (!src || !mainImg) return;
      mainImg.src = src;
      document.querySelectorAll(".pdp-thumb").forEach((t) => t.classList.remove("is-active"));
      thumb.classList.add("is-active");
    });
  });

  // Checkout: reserva timer
  const reserve = document.getElementById("reserveTimer");
  if (reserve) {
    let left =
      (Number(reserve.dataset.mins) || 14) * 60 +
      (Number(reserve.dataset.secs) || 50);
    const mEl = reserve.querySelector('[data-unit="m"]');
    const sEl = reserve.querySelector('[data-unit="s"]');
    const tickReserve = () => {
      left = Math.max(0, left - 1);
      if (mEl) mEl.textContent = String(Math.floor(left / 60)).padStart(2, "0");
      if (sEl) sEl.textContent = String(left % 60).padStart(2, "0");
    };
    tickReserve();
    setInterval(tickReserve, 1000);
  }

  // Checkout: highlight radio cards
  document.querySelectorAll(".entrega-opts, .pago-opts").forEach((group) => {
    group.addEventListener("change", (e) => {
      const input = e.target;
      if (!(input instanceof HTMLInputElement) || input.type !== "radio") return;
      group.querySelectorAll(".entrega-opt, .pago-opt").forEach((el) => el.classList.remove("is-active"));
      input.closest(".entrega-opt, .pago-opt")?.classList.add("is-active");
    });
  });

  // Paso 2: retiro vs delivery (paneles, horarios, tienda)
  const formPaso2 = document.getElementById("entregaFormPaso2");
  if (formPaso2) {
    const panelRetiro = document.getElementById("panelRetiro");
    const panelExpress = document.getElementById("panelExpress");
    const horarioSelect = document.getElementById("horarioSelect");
    const horarioLabel = document.getElementById("horarioLabel");
    const direccionInput = document.getElementById("direccionInput");
    const tiendaExpressHidden = document.getElementById("tiendaExpressHidden");
    const horariosRetiro = JSON.parse(formPaso2.dataset.horariosRetiro || "{}");
    const horariosExpress = JSON.parse(formPaso2.dataset.horariosExpress || "{}");

    const fillHorarios = (modo) => {
      if (!horarioSelect) return;
      const map = modo === "express" ? horariosExpress : horariosRetiro;
      const current = horarioSelect.value;
      horarioSelect.innerHTML = "";
      Object.entries(map).forEach(([key, label]) => {
        const opt = document.createElement("option");
        opt.value = key;
        opt.textContent = label;
        if (key === current) opt.selected = true;
        horarioSelect.appendChild(opt);
      });
      if (![...horarioSelect.options].some((o) => o.selected) && horarioSelect.options[0]) {
        horarioSelect.options[0].selected = true;
      }
      if (horarioLabel) {
        horarioLabel.textContent = modo === "express" ? "Ventana de entrega" : "Horario de retiro";
      }
    };

    const applyModo = (modo) => {
      const esRetiro = modo !== "express";
      panelRetiro?.classList.toggle("is-visible", esRetiro);
      panelExpress?.classList.toggle("is-visible", !esRetiro);
      formPaso2.dataset.modo = esRetiro ? "retiro" : "express";

      panelRetiro?.querySelectorAll('input[name="tienda_id"]').forEach((el) => {
        const agotada = !!el.closest(".is-disabled");
        el.disabled = !esRetiro || agotada;
      });
      if (tiendaExpressHidden) tiendaExpressHidden.disabled = esRetiro;
      if (direccionInput) direccionInput.required = !esRetiro;
      fillHorarios(esRetiro ? "retiro" : "express");
    };

    formPaso2.querySelectorAll('input[name="entrega"]').forEach((radio) => {
      radio.addEventListener("change", () => applyModo(radio.value));
    });

    formPaso2.querySelectorAll(".tienda-pick-card input").forEach((radio) => {
      radio.addEventListener("change", () => {
        formPaso2.querySelectorAll(".tienda-pick-card").forEach((c) => c.classList.remove("is-active"));
        radio.closest(".tienda-pick-card")?.classList.add("is-active");
      });
    });

    const checked = formPaso2.querySelector('input[name="entrega"]:checked');
    applyModo(checked?.value || "retiro");
  }

  // PDP: presentaciones
  const formatMoney = (n) =>
    "S/ " + Number(n).toFixed(2);

  document.querySelectorAll(".pdp-variant").forEach((btn) => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".pdp-variant").forEach((b) => b.classList.remove("is-active"));
      btn.classList.add("is-active");
      const precio = btn.getAttribute("data-precio");
      const antes = btn.getAttribute("data-antes");
      const priceEl = document.getElementById("pdpPrice");
      const oldEl = document.getElementById("pdpPriceOld");
      const btnPrice = document.getElementById("pdpBtnPrice");
      if (precio && priceEl) priceEl.textContent = formatMoney(precio);
      if (btnPrice && precio) btnPrice.textContent = formatMoney(precio);
      if (oldEl) {
        if (antes) {
          oldEl.textContent = formatMoney(antes);
          oldEl.hidden = false;
        } else {
          oldEl.hidden = true;
        }
      }
    });
  });
})();
