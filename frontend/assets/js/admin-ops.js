(() => {
  document.querySelectorAll("[data-ops-live]").forEach((input) => {
    const table = document.querySelector(input.getAttribute("data-ops-live") || "[data-ops-table]");
    input.addEventListener("input", () => {
      const q = input.value.trim().toLowerCase();
      if (!table) return;
      table.querySelectorAll("[data-ops-row]").forEach((row) => {
        const hay = (row.getAttribute("data-ops-row") || "").toLowerCase();
        row.hidden = q !== "" && !hay.includes(q);
      });
    });
  });

  document.querySelectorAll("form[data-ops-form]").forEach((form) => {
    form.addEventListener("submit", () => {
      const btn = form.querySelector("button[type='submit']");
      if (!btn || btn.disabled) return;
      btn.disabled = true;
      btn.textContent = "Guardando…";
    });
  });

  const fill = (dlg, source) => {
    dlg.querySelectorAll("[data-ops-field]").forEach((field) => {
      const key = field.getAttribute("data-ops-field");
      if (!key) return;
      field.value = source.getAttribute("data-" + key) || "";
    });
    const title = dlg.querySelector("[data-ops-title]");
    if (title) title.textContent = source.getAttribute("data-titulo") || title.textContent;
  };

  document.querySelectorAll("[data-ops-dialog]").forEach((opener) => {
    opener.addEventListener("click", (event) => {
      const dlg = document.getElementById(opener.getAttribute("data-ops-dialog") || "");
      if (!dlg || typeof dlg.showModal !== "function") return;
      event.preventDefault();
      fill(dlg, opener);
      dlg.showModal();
    });
  });

  document.querySelectorAll("[data-ops-close]").forEach((btn) => {
    btn.addEventListener("click", () => btn.closest("dialog")?.close());
  });

  document.querySelectorAll("dialog.ops-modal").forEach((dlg) => {
    dlg.addEventListener("click", (event) => {
      if (event.target === dlg) dlg.close();
    });
  });

  const toast = (message) => {
    let box = document.querySelector(".ops-toast-live");
    if (!box) {
      box = document.createElement("div");
      box.className = "ops-alert ops-toast-live";
      box.setAttribute("role", "status");
      document.querySelector(".admin-content")?.prepend(box);
    }
    box.textContent = message;
  };

  document.querySelectorAll("[data-ops-card]").forEach((card) => {
    card.addEventListener("dragstart", (event) => {
      const payload = JSON.stringify({
        codigo: card.getAttribute("data-codigo") || "",
        estado: card.getAttribute("data-estado") || "",
      });
      event.dataTransfer?.setData("text/plain", payload);
      card.classList.add("is-dragging");
    });
    card.addEventListener("dragend", () => card.classList.remove("is-dragging"));
  });

  document.querySelectorAll("[data-ops-col]").forEach((col) => {
    col.addEventListener("dragover", (event) => {
      event.preventDefault();
      col.classList.add("is-over");
    });
    col.addEventListener("dragleave", () => col.classList.remove("is-over"));
    col.addEventListener("drop", (event) => {
      event.preventDefault();
      col.classList.remove("is-over");
      let data = {};
      try {
        data = JSON.parse(event.dataTransfer?.getData("text/plain") || "{}");
      } catch (error) {
        data = {};
      }
      const dest = col.getAttribute("data-ops-col") || "";
      const estado = data.estado || "";
      const siguiente = (estado === "preparacion" && dest === "por_recoger")
        || (estado === "por_recoger" && dest === "cerrado");
      if (!siguiente) {
        toast("Suelta el pedido solo en la columna siguiente.");
        return;
      }
      const form = document.getElementById("opsDragForm");
      if (!form) return;
      form.elements.codigo.value = data.codigo || "";
      const btn = form.querySelector("button[type='submit']");
      if (btn) btn.textContent = "Guardando…";
      form.submit();
    });
  });
})();
