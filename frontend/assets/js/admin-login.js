(() => {
  const form = document.getElementById("loginForm");
  if (!form) return;

  const hint = document.getElementById("roleHint");
  const usuario = document.getElementById("usuario");
  const hints = {
    admin: "Modo Administrador: acceso a WMS, góndolas, compras, mermas y auditoría de tiendas.",
    cliente: "Modo Cliente: tienda Mass Online, catálogo, carrito y seguimiento de pedidos personales.",
    proveedor: "Modo Proveedor: portal B2B para OC, catálogo SKU, despachos y entregas a tienda.",
  };
  const defaults = {
    admin: "admin.operaciones@mass.pe",
    cliente: "cliente@mass.pe",
    proveedor: "proveedor@mass.pe",
  };

  form.querySelectorAll('input[name="rol"]').forEach((radio) => {
    radio.addEventListener("change", () => {
      form.querySelectorAll(".role-tab").forEach((tab) => tab.classList.remove("is-active"));
      radio.closest(".role-tab")?.classList.add("is-active");
      if (hint) hint.textContent = hints[radio.value] || hints.admin;
      if (usuario && defaults[radio.value]) {
        const current = (usuario.value || "").trim().toLowerCase();
        const known = Object.values(defaults).map((v) => v.toLowerCase());
        if (current === "" || known.includes(current) || ["admin", "cliente", "proveedor"].includes(current)) {
          usuario.value = defaults[radio.value];
          usuario.placeholder = defaults[radio.value];
        }
      }
    });
  });

  const toggle = document.getElementById("togglePass");
  const pass = document.getElementById("password");
  toggle?.addEventListener("click", () => {
    if (!pass) return;
    const show = pass.type === "password";
    pass.type = show ? "text" : "password";
    toggle.textContent = show ? "🙈" : "👁";
    toggle.setAttribute("aria-label", show ? "Ocultar contraseña" : "Mostrar contraseña");
  });
})();
