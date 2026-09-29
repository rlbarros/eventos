import "../../vendor/masmerise/livewire-toaster/resources/js";

document.addEventListener("livewire:init", () => {
  Livewire.on("log-event", (event) => {
    // Check for modern Livewire event structure
    if (event[0] && event[0].obj) {
      console[event[0].level || "log"](event[0].obj);
    } else {
      // Fallback for older Livewire versions or simple events
      console.log(event);
    }
  });
});

/*
 * Tabelas no celular: abaixo de 768px o CSS (app.css, "tabelas em cartões")
 * mostra cada linha como um cartão. Para cada célula ganhar o nome da coluna
 * como rótulo, copiamos o texto do cabeçalho para data-label. Rodamos de novo
 * a cada mudança no DOM porque o Livewire troca as linhas ao paginar/filtrar.
 */
function rotularTabelas(root = document) {
  root.querySelectorAll("table[data-flux-table]").forEach((table) => {
    const headers = Array.from(table.querySelectorAll("thead th")).map((th) =>
      th.textContent.replace(/\s+/g, " ").trim(),
    );
    if (!headers.length) return;

    table.querySelectorAll("tbody tr").forEach((tr) => {
      let col = 0;
      Array.from(tr.children).forEach((td) => {
        const span = td.colSpan || 1;
        const label = span > 1 ? "" : headers[col] ?? "";
        col += span;

        if (td.dataset.label !== label) td.dataset.label = label;
        const isActions = /^a[cç][oõ]es$/i.test(label);
        td.toggleAttribute("data-cell-actions", isActions);
        td.toggleAttribute("data-cell-full", span > 1);
      });
    });
  });
}

let rotularAgendado = false;
function agendarRotulos() {
  if (rotularAgendado) return;
  rotularAgendado = true;
  requestAnimationFrame(() => {
    rotularAgendado = false;
    rotularTabelas();
  });
}

document.addEventListener("DOMContentLoaded", () => {
  rotularTabelas();
  new MutationObserver(agendarRotulos).observe(document.body, {
    childList: true,
    subtree: true,
  });
});
document.addEventListener("livewire:navigated", agendarRotulos);
