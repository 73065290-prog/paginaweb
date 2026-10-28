(() => {
  const form = document.getElementById("loginForm");
  if (!form) return;

  const hint = document.getElementById("roleHint");
  const hints = {
    admin: "Modo Administrador: Acceso a WMS, góndolas, compras, mermas y auditoría de tiendas.",
    cliente: "Modo Cliente/Proveedor: Acceso al portal de pedidos y seguimiento de entregas.",
  };

  form.querySelectorAll('input[name="rol"]').forEach((radio) => {
    radio.addEventListener("change", () => {
      form.querySelectorAll(".role-tab").forEach((tab) => tab.classList.remove("is-active"));
      radio.closest(".role-tab")?.classList.add("is-active");
      if (hint) hint.textContent = hints[radio.value] || hints.admin;
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
