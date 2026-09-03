(() => {
  const button = document.getElementById("installMciApp");
  const help = document.getElementById("installMciHelp");
  if (!button) return;

  let installPrompt = null;
  const standalone = window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;
  const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
  const isAndroid = /android/i.test(navigator.userAgent);

  if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => navigator.serviceWorker.register("/sw.js?v=2").catch(() => {}));
  }

  if (standalone) {
    button.hidden = true;
    help.textContent = "MCI App is installed";
    return;
  }

  window.addEventListener("beforeinstallprompt", event => {
    event.preventDefault();
    installPrompt = event;
    help.textContent = "Ready to install on this device";
  });

  button.addEventListener("click", async () => {
    if (installPrompt) {
      installPrompt.prompt();
      const choice = await installPrompt.userChoice;
      if (choice.outcome === "accepted") help.textContent = "App installation started";
      installPrompt = null;
      return;
    }

    if (isIos) {
      help.textContent = "Safari में Share ⤴ दबाएँ, फिर Add to Home Screen चुनें";
      button.innerHTML = "Add to Home Screen <b aria-hidden=\"true\">＋</b>";
      return;
    }

    help.textContent = isAndroid
      ? "Chrome menu ⋮ में Add to Home screen या Install app चुनें"
      : "Chrome/Edge menu में Install Micro Computer Institute चुनें";
  });

  window.addEventListener("appinstalled", () => {
    button.hidden = true;
    help.textContent = "MCI App installed successfully";
  });
})();
