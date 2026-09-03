(() => {
  const button = document.getElementById("installMciApp");
  const help = document.getElementById("installMciHelp");
  if (!button) return;

  let installPrompt = null;
  const standalone = window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;

  if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => navigator.serviceWorker.register("/sw.js").catch(() => {}));
  }

  if (standalone) {
    button.hidden = true;
    help.textContent = "MCI App is installed";
    return;
  }

  window.addEventListener("beforeinstallprompt", event => {
    event.preventDefault();
    installPrompt = event;
    button.disabled = false;
  });

  button.addEventListener("click", async () => {
    if (installPrompt) {
      installPrompt.prompt();
      const choice = await installPrompt.userChoice;
      if (choice.outcome === "accepted") help.textContent = "App installation started";
      installPrompt = null;
      return;
    }

    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    help.textContent = isIos
      ? "Safari: Share → Add to Home Screen"
      : "Browser menu खोलें और Install app चुनें";
  });

  window.addEventListener("appinstalled", () => {
    button.hidden = true;
    help.textContent = "MCI App installed successfully";
  });
})();
