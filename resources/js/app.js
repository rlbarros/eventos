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
