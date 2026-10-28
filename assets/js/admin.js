(() => {
  // UI admin: feedback demo en acciones rápidas / reponer
  // No aplicar a submit del modal de producto (bloqueaba el guardado y los errores)
  document.querySelectorAll(".btn-mini, .quick-actions button").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (btn.tagName === "A") return;
      if (btn.type === "submit" || btn.classList.contains("btn-save") || btn.closest("#formProducto")) {
        return;
      }
      const prev = btn.textContent;
      btn.textContent = "Listo ✓";
      btn.disabled = true;
      setTimeout(() => {
        btn.textContent = prev;
        btn.disabled = false;
      }, 1200);
    });
  });

  // Desplegable cuenta / cerrar sesión
  const account = document.querySelector(".admin-account");
  const btn = document.getElementById("adminAccountBtn");
  const menu = document.getElementById("adminAccountMenu");

  const setOpen = (open) => {
    if (!account || !btn || !menu) return;
    account.classList.toggle("is-open", open);
    btn.setAttribute("aria-expanded", open ? "true" : "false");
    menu.hidden = !open;
  };

  btn?.addEventListener("click", (e) => {
    e.stopPropagation();
    setOpen(!!menu?.hidden);
  });

  document.addEventListener("click", (e) => {
    if (!account?.contains(e.target)) setOpen(false);
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") setOpen(false);
  });

  // Modal Editar Producto (Figma 35-9928): margen + activo + ABC UI
  const costoInput = document.getElementById("costoInput");
  const precioInput = document.getElementById("precioInput");
  const margenPctLabel = document.getElementById("margenPctLabel");
  const margenBrutoLabel = document.getElementById("margenBrutoLabel");
  const activoUi = document.querySelector('.activo-switch input[name="activo_ui"]');
  const activoHidden = document.getElementById("activoHidden");

  const fmtMoney = (n) =>
    "S/ " + (Number.isFinite(n) ? n.toFixed(2) : "0.00");

  const recalcMargen = () => {
    if (!costoInput || !precioInput || !margenPctLabel || !margenBrutoLabel) return;
    const costo = parseFloat(costoInput.value) || 0;
    const precio = parseFloat(precioInput.value) || 0;
    const bruto = precio - costo;
    const pct = precio > 0 ? (bruto / precio) * 100 : 0;
    margenPctLabel.textContent = pct.toFixed(2) + "%";
    margenBrutoLabel.textContent = "(" + fmtMoney(bruto) + " bruto)";
  };

  costoInput?.addEventListener("input", recalcMargen);
  precioInput?.addEventListener("input", recalcMargen);

  activoUi?.addEventListener("change", () => {
    if (activoHidden) activoHidden.value = activoUi.checked ? "1" : "0";
  });

  document.querySelectorAll(".abc-option").forEach((opt) => {
    opt.addEventListener("click", () => {
      document.querySelectorAll(".abc-option").forEach((o) => o.classList.remove("is-active"));
      opt.classList.add("is-active");
      const radio = opt.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    });
  });

  // Escape cierra modal (vuelve sin query editar)
  document.addEventListener("keydown", (e) => {
    if (e.key !== "Escape") return;
    const close = document.querySelector(".prod-modal-close");
    if (close) window.location.href = close.href;
  });

  // Reglas de negocio en cliente (alta / edición)
  const formProducto = document.getElementById("formProducto");
  const clientErrors = document.getElementById("prodClientErrors");

  const validarProductoForm = () => {
    const errores = [];
    if (!formProducto) return errores;

    const val = (name) => (formProducto.elements[name]?.value || "").trim();
    const num = (name) => parseFloat(val(name)) || 0;
    const nombre = val("nombre");
    const ean = val("ean").replace(/\D+/g, "");
    const unidad = val("unidad");
    const categoria = val("categoria");
    const subcategoria = val("subcategoria");
    const descripcion = val("descripcion");
    const costo = num("costo");
    const precio = num("precio");
    const reorden = parseInt(val("puntoReorden"), 10) || 0;
    const capacidad = parseInt(val("capacidadGondola"), 10) || 0;
    const abc = (formProducto.querySelector('input[name="abc"]:checked') || {}).value || "";

    if (nombre.length < 3) errores.push("El nombre comercial debe tener al menos 3 caracteres");
    if (ean.length !== 13) errores.push("El EAN-13 debe tener exactamente 13 dígitos");
    if (!unidad) errores.push("Indica la unidad de medida");
    if (!categoria) errores.push("Indica la categoría");
    if (!subcategoria) errores.push("Indica la subcategoría");
    if (descripcion.length < 10) errores.push("La descripción de góndola debe tener al menos 10 caracteres");
    if (costo <= 0) errores.push("El costo unitario debe ser mayor a 0");
    if (precio <= 0) errores.push("El PVP debe ser mayor a 0");
    if (precio > 0 && costo > 0 && precio <= costo) {
      errores.push("El PVP debe ser mayor al costo (margen comercial positivo)");
    }
    if (reorden < 1) errores.push("El punto de reorden debe ser al menos 1");
    if (capacidad < 1) errores.push("La capacidad de góndola debe ser al menos 1");
    if (capacidad > 0 && reorden >= capacidad) {
      errores.push("El punto de reorden debe ser menor a la capacidad máxima de góndola");
    }
    if (!["A", "B", "C"].includes(abc)) errores.push("Selecciona una clasificación ABC (A, B o C)");

    return errores;
  };

  const marcarCamposInvalidos = () => {
    if (!formProducto) return;
    const nombres = [
      "nombre",
      "ean",
      "unidad",
      "categoria",
      "subcategoria",
      "descripcion",
      "costo",
      "precio",
      "puntoReorden",
      "capacidadGondola",
    ];
    nombres.forEach((name) => {
      const el = formProducto.elements[name];
      if (!el) return;
      el.classList.remove("is-invalid");
    });
    document.querySelectorAll(".abc-option").forEach((o) => o.classList.remove("is-invalid-abc"));

    const erroresCheck = validarProductoForm();
    // Marca visual según reglas
    const setInv = (name, cond) => {
      const el = formProducto.elements[name];
      if (el && cond) el.classList.add("is-invalid");
    };
    const nombre = (formProducto.elements.nombre?.value || "").trim();
    const ean = (formProducto.elements.ean?.value || "").replace(/\D+/g, "");
    setInv("nombre", nombre.length < 3);
    setInv("ean", ean.length !== 13);
    setInv("unidad", !(formProducto.elements.unidad?.value || "").trim());
    setInv("categoria", !(formProducto.elements.categoria?.value || "").trim());
    setInv("subcategoria", !(formProducto.elements.subcategoria?.value || "").trim());
    setInv("descripcion", (formProducto.elements.descripcion?.value || "").trim().length < 10);
    setInv("costo", (parseFloat(formProducto.elements.costo?.value) || 0) <= 0);
    setInv("precio", (parseFloat(formProducto.elements.precio?.value) || 0) <= 0);
    const costo = parseFloat(formProducto.elements.costo?.value) || 0;
    const precio = parseFloat(formProducto.elements.precio?.value) || 0;
    if (precio > 0 && costo > 0 && precio <= costo) {
      formProducto.elements.precio?.classList.add("is-invalid");
      formProducto.elements.costo?.classList.add("is-invalid");
    }
    const reorden = parseInt(formProducto.elements.puntoReorden?.value || "0", 10) || 0;
    const capacidad = parseInt(formProducto.elements.capacidadGondola?.value || "0", 10) || 0;
    setInv("puntoReorden", reorden < 1 || (capacidad > 0 && reorden >= capacidad));
    setInv("capacidadGondola", capacidad < 1);
    const abc = formProducto.querySelector('input[name="abc"]:checked');
    if (!abc) {
      document.querySelectorAll(".abc-option").forEach((o) => o.classList.add("is-invalid-abc"));
    }
    return erroresCheck;
  };

  formProducto?.addEventListener("submit", (e) => {
    const errores = marcarCamposInvalidos();
    if (!errores.length) return;
    e.preventDefault();
    e.stopPropagation();
    if (clientErrors) {
      clientErrors.hidden = false;
      clientErrors.innerHTML =
        "<strong>No se puede guardar: completa o selecciona los campos requeridos</strong><ul class=\"prod-error-list\">" +
        errores.map((err) => "<li>" + err + "</li>").join("") +
        "</ul>";
      clientErrors.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
  });
})();
