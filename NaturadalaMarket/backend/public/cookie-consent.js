(() => {
  const COOKIE = "naturadala_consent";
  const VERSION = 1;
  const MAX_AGE = 60 * 60 * 24 * 180;
  const CATEGORIES = ["preferences", "analytics", "marketing"];

  const read = () => {
    const match = document.cookie.split("; ").find((row) => row.startsWith(`${COOKIE}=`));
    if (!match) return null;
    try {
      const data = JSON.parse(decodeURIComponent(match.slice(COOKIE.length + 1)));
      return data && data.v === VERSION ? data : null;
    } catch (error) {
      return null;
    }
  };

  const write = (choices) => {
    const data = { v: VERSION, t: Date.now() };
    CATEGORIES.forEach((name) => {
      data[name] = Boolean(choices[name]);
    });
    const secure = window.location.protocol === "https:" ? "; Secure" : "";
    document.cookie = `${COOKIE}=${encodeURIComponent(JSON.stringify(data))}; Max-Age=${MAX_AGE}; Path=/; SameSite=Lax${secure}`;
    consent = data;
    document.dispatchEvent(new CustomEvent("naturadala:consent", { detail: data }));
    return data;
  };

  let consent = read();

  window.naturadalaConsent = {
    has: (category) => category === "necessary" || Boolean(consent && consent[category]),
    decided: () => consent !== null,
    open: () => show(true),
  };

  const root = document.querySelector("[data-cookie-consent]");
  if (!root) return;

  const card = root.querySelector(".cookie-card");
  const options = root.querySelector("[data-cookie-options]");
  const actions = root.querySelector("[data-cookie-actions]");
  const customize = root.querySelector("[data-cookie-customize]");
  let opener = null;

  const setOptionsOpen = (open) => {
    options.hidden = !open;
    customize.setAttribute("aria-expanded", String(open));
  };

  const syncCheckboxes = () => {
    CATEGORIES.forEach((name) => {
      options.elements[name].checked = window.naturadalaConsent.has(name);
    });
  };

  const hide = () => {
    root.hidden = true;
    if (opener && document.contains(opener)) opener.focus();
    opener = null;
  };

  const choose = (choices) => {
    consent = write(choices);
    hide();
  };

  function show(fromSettings) {
    opener = fromSettings ? document.activeElement : null;
    syncCheckboxes();
    setOptionsOpen(fromSettings);
    root.hidden = false;
    card.focus();
  }

  root.querySelector("[data-cookie-accept]").addEventListener("click", () =>
    choose({ preferences: true, analytics: true, marketing: true })
  );
  root.querySelector("[data-cookie-reject]").addEventListener("click", () => choose({}));
  customize.addEventListener("click", () => {
    setOptionsOpen(options.hidden);
    if (!options.hidden) options.elements.preferences.focus();
  });
  options.addEventListener("submit", (event) => {
    event.preventDefault();
    const choices = {};
    CATEGORIES.forEach((name) => {
      choices[name] = options.elements[name].checked;
    });
    choose(choices);
  });
  root.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && consent) hide();
  });

  document.querySelectorAll("[data-cookie-settings]").forEach((button) =>
    button.addEventListener("click", () => show(true))
  );

  if (!consent) show(false);
})();
