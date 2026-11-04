(() => {
  // Solo UI: carrito drawer, countdown, qty +/- y toast flash
  const openCart = document.getElementById("openCart");
  const closeCart = document.getElementById("closeCart");
  const overlay = document.getElementById("overlay");
  const drawer = document.getElementById("cartDrawer");
  const toast = document.getElementById("toast");
  let cartOpener = null;

  function cartFocusable() {
    if (!drawer) return [];
    return [...drawer.querySelectorAll("a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])")].filter(
      (el) => !el.hasAttribute("hidden") && el.getAttribute("aria-hidden") !== "true"
    );
  }

  function setCart(open) {
    overlay?.classList.toggle("open", open);
    drawer?.classList.toggle("open", open);
    openCart?.setAttribute("aria-expanded", open ? "true" : "false");
    document.body.style.overflow = open ? "hidden" : "";
    if (!drawer) return;
    if (open) {
      drawer.setAttribute("aria-hidden", "false");
      drawer.setAttribute("aria-modal", "true");
      drawer.removeAttribute("inert");
      cartOpener = document.activeElement instanceof HTMLElement ? document.activeElement : openCart;
      closeCart?.focus();
      return;
    }
    const volver = cartOpener && cartOpener !== closeCart ? cartOpener : openCart;
    cartOpener = null;
    if (volver instanceof HTMLElement) volver.focus();
    drawer.setAttribute("aria-hidden", "true");
    drawer.removeAttribute("aria-modal");
    drawer.setAttribute("inert", "");
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
    if (e.key === "Escape") {
      if (drawer?.classList.contains("open")) {
        setCart(false);
        return;
      }
      setCuentaOpen(false);
      return;
    }
    if (e.key !== "Tab" || !drawer?.classList.contains("open")) return;
    const items = cartFocusable();
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  });

  if (toast?.classList.contains("show")) {
    const initialToast = toast.textContent.trim();
    if (initialToast) {
      toast.textContent = "";
      window.setTimeout(() => {
        toast.textContent = initialToast;
      }, 50);
    }
    setTimeout(() => toast.classList.remove("show"), 2400);
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
    const abierto = !!panel?.classList.contains("open");
    if (btn) {
      btn.textContent = abierto ? "✕ Ocultar filtros" : "☰ Mostrar filtros";
      btn.setAttribute("aria-expanded", abierto ? "true" : "false");
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

  // =========================================================================
  // Integración progresiva con API JSON (Carrito y Stock)
  // =========================================================================
  const API_BASE = "../backend/public/index.php";
  let toastTimer = null;

  const showToast = (message, kind = "status") => {
    if (!toast || !message) return;
    const esError = kind === "error";
    toast.setAttribute("role", esError ? "alert" : "status");
    toast.setAttribute("aria-live", esError ? "assertive" : "polite");
    toast.textContent = "";
    window.setTimeout(() => {
      toast.textContent = message;
      toast.classList.add("show");
    }, 50);
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove("show"), 2400);
  };

  const escapeHtml = (str) =>
    String(str ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const getCsrfToken = () => {
    const input = document.querySelector('input[name="csrf_token"]');
    return input ? input.value : "";
  };

  const renderCartState = (carrito, csrfToken) => {
    if (!carrito || typeof carrito !== "object") return;
    const totalUnidades = Number(carrito.total_unidades ?? 0);
    const totalFormateado = carrito.total_formateado || formatMoney(carrito.total_monto || 0);

    const cartLabel = document.getElementById("cartLabel");
    const cartTotalNav = document.getElementById("cartTotalNav");
    const cartTotal = document.getElementById("cartTotal");
    const cartItems = document.getElementById("cartItems");

    if (cartLabel) {
      const etiqueta = totalUnidades === 1 ? "producto" : "productos";
      cartLabel.textContent = `${totalUnidades} ${etiqueta}`;
    }
    if (openCart) {
      openCart.setAttribute(
        "aria-label",
        `Abrir carrito, ${cartLabel ? cartLabel.textContent : totalUnidades}, total ${totalFormateado}`
      );
    }
    if (cartTotalNav) {
      cartTotalNav.textContent = totalFormateado;
    }
    if (cartTotal) {
      cartTotal.textContent = totalFormateado;
    }

    if (cartItems) {
      const items = Array.isArray(carrito.items) ? carrito.items : [];
      const token = escapeHtml(csrfToken || getCsrfToken());

      if (items.length === 0) {
        cartItems.innerHTML = '<div class="empty">Tu carrito está vacío.</div>';
      } else {
        cartItems.innerHTML = items
          .map((it) => {
            const p = it.producto || it;
            const id = Number(p.id || 0);
            const cant = Number(it.cantidad || 1);
            const img = p.img || p.imagen || "";
            const precioUnit = p.precio_formateado || formatMoney(p.precio || 0);
            const sub = it.subtotal_formateado || formatMoney(it.subtotal || 0);
            const nombre = escapeHtml(p.nombre || "producto");
            return `
              <div class="cart-item">
                <img src="${escapeHtml(img)}" alt="" />
                <div>
                  <h4>${nombre}</h4>
                  <p>${escapeHtml(precioUnit)} c/u · x${cant}</p>
                  <div class="qty" style="margin-top:0.3rem">
                    <form method="post" action="carrito.php" style="display:inline">
                      <input type="hidden" name="csrf_token" value="${token}" />
                      <input type="hidden" name="accion" value="cambiar" />
                      <input type="hidden" name="id" value="${id}" />
                      <input type="hidden" name="delta" value="-1" />
                      <button type="submit" aria-label="Disminuir cantidad de ${nombre}">−</button>
                    </form>
                    <span><span class="sr-only">Cantidad de ${nombre}: </span>${cant}</span>
                    <form method="post" action="carrito.php" style="display:inline">
                      <input type="hidden" name="csrf_token" value="${token}" />
                      <input type="hidden" name="accion" value="cambiar" />
                      <input type="hidden" name="id" value="${id}" />
                      <input type="hidden" name="delta" value="1" />
                      <button type="submit" aria-label="Aumentar cantidad de ${nombre}">+</button>
                    </form>
                    <form method="post" action="carrito.php" style="display:inline;margin-left:0.35rem">
                      <input type="hidden" name="csrf_token" value="${token}" />
                      <input type="hidden" name="accion" value="eliminar" />
                      <input type="hidden" name="id" value="${id}" />
                      <button type="submit" aria-label="Quitar ${nombre}">✕</button>
                    </form>
                  </div>
                </div>
                <strong>${escapeHtml(sub)}</strong>
              </div>
            `;
          })
          .join("");
      }
      if (drawer?.classList.contains("open") && !drawer.contains(document.activeElement)) {
        closeCart?.focus();
      }
    }
  };

  // Consulta de stock en tiempo real mediante GET ?action=stock.consultar
  const consultarStockProducto = async (id) => {
    const res = await fetch(
      `${API_BASE}?action=stock.consultar&id=${encodeURIComponent(id)}`,
      {
        method: "GET",
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      }
    );
    const body = await res.json();
    return { status: res.status, body };
  };

  document.querySelector(".stock-btn")?.addEventListener("click", async () => {
    const pdpIdInput = document.querySelector('.pdp-buy input[name="id"]');
    const firstCardIdInput = document.querySelector('.product-card input[name="id"]');
    const targetId = Number(pdpIdInput?.value || firstCardIdInput?.value || 1);
    try {
      const { body } = await consultarStockProducto(targetId);
      const prod = body?.data || body?.producto;
      if ((body?.success || body?.ok) && prod) {
        showToast(
          `${prod.nombre}: ${prod.stock_etiqueta} (${prod.stock_disponible} uds.)`
        );
      } else {
        const errMsg = body?.errors?.[0] || body?.message || body?.mensaje;
        if (errMsg) showToast(errMsg);
      }
    } catch {
      // Degradación silenciosa si falla la red
    }
  });

  // Interceptación progresiva de formularios de carrito (agregar, cambiar, eliminar)
  document.addEventListener("submit", async (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || typeof window.fetch !== "function") {
      return;
    }

    const actionAttr = form.getAttribute("action") || "";
    if (!actionAttr.endsWith("carrito.php")) {
      return;
    }

    // En la página de checkout (pedido.php) los formularios fuera del drawer recargan el resumen completo
    const isCheckoutPage = window.location.pathname.endsWith("pedido.php");
    const isInsideDrawer = !!form.closest("#cartDrawer");
    if (isCheckoutPage && !isInsideDrawer) {
      return;
    }

    const fd = new FormData(form);
    const accion = String(fd.get("accion") || "");
    const id = Number(fd.get("id") || 0);
    const csrfToken = String(fd.get("csrf_token") || getCsrfToken());

    let apiAction = "";
    const payload = { id, csrf_token: csrfToken };

    if (accion === "agregar") {
      apiAction = "carrito.agregar";
      payload.cantidad = Number(fd.get("cantidad") || 1);
    } else if (accion === "cambiar") {
      apiAction = "carrito.actualizar";
      payload.delta = Number(fd.get("delta") || 0);
    } else if (accion === "set_cantidad") {
      apiAction = "carrito.actualizar";
      payload.cantidad = Number(fd.get("cantidad") || 0);
    } else if (accion === "eliminar") {
      apiAction = "carrito.quitar";
    } else {
      return;
    }

    e.preventDefault();

    if (form.dataset.busy === "1") {
      return;
    }
    form.dataset.busy = "1";

    try {
      const response = await fetch(`${API_BASE}?action=${apiAction}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-CSRF-Token": csrfToken,
        },
        credentials: "same-origin",
        body: JSON.stringify(payload),
      });

      const result = await response.json();
      const carrito = result?.data?.carrito || result?.carrito;
      const mensaje =
        result?.errors?.[0] ||
        result?.message ||
        result?.mensaje ||
        (response.ok ? "Carrito actualizado" : "No se pudo procesar la operación del carrito.");

      if (response.ok && (result?.success || result?.ok)) {
        if (carrito) {
          renderCartState(carrito, csrfToken);
        }
        showToast(mensaje, "status");
      } else {
        if (carrito) {
          renderCartState(carrito, csrfToken);
        }
        showToast(mensaje, "error");
      }
    } catch {
      // Degradación elegante: envío tradicional del formulario si falla fetch()
      delete form.dataset.busy;
      form.submit();
      return;
    }

    delete form.dataset.busy;
  });
})();
