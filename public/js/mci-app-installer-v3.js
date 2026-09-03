(() => {
  const button = document.getElementById("installMciApp");
  const help = document.getElementById("installMciHelp");
  if (!button || !help) return;

  let installPrompt = null;
  const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
  const isAndroid = /android/i.test(navigator.userAgent);
  const standalone =
    matchMedia("(display-mode: standalone)").matches ||
    navigator.standalone === true;

  const guide = document.createElement("div");
  guide.className = "mci-install-guide";
  guide.hidden = true;
  guide.innerHTML =
    '<div role="dialog" aria-modal="true">' +
    '<h3>Install MCI App</h3>' +
    '<p id="mciInstallSteps"></p>' +
    '<button type="button">समझ गया / Close</button></div>';

  document.body.appendChild(guide);

  const steps = guide.querySelector("#mciInstallSteps");
  const closeGuide = () => { guide.hidden = true; };

  guide.querySelector("button").addEventListener("click", closeGuide);
  guide.addEventListener("click", event => {
    if (event.target === guide) closeGuide();
  });

  if ("serviceWorker" in navigator) {
    navigator.serviceWorker
      .register("/mci-sw-v3.js", {scope: "/"})
      .then(() => navigator.serviceWorker.ready)
      .catch(() => {
        help.textContent =
          "Installation service could not start. Please reload once.";
      });
  }

  if (standalone) {
    button.hidden = true;
    help.textContent = "MCI App is already installed";
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

      help.textContent =
        choice.outcome === "accepted"
          ? "App installation started"
          : "Installation cancelled";

      installPrompt = null;
      return;
    }

    steps.textContent = isIos
      ? "Safari का Share बटन दबाएँ और Add to Home Screen चुनें।"
      : isAndroid
        ? "Chrome के ऊपर ⋮ menu को खोलें और Install app या Add to Home screen चुनें।"
        : "Chrome या Edge के address bar में Install icon चुनें, अथवा browser menu से Install Micro Computer Institute दबाएँ।";

    guide.hidden = false;
  });

  window.addEventListener("appinstalled", () => {
    button.hidden = true;
    help.textContent = "MCI App installed successfully";
  });
})();
