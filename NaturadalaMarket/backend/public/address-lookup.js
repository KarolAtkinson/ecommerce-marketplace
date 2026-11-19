(() => {
  const form = document.querySelector("[data-address-form]");
  if (!form) return;

  const box = form.querySelector("[data-address-search]");
  const input = box.querySelector("input");
  const list = box.querySelector("ul");
  const status = box.querySelector("[data-address-status]");
  const country = form.querySelector("#country_code");
  const locked = {
    street: form.querySelector("#street_line_1"),
    postal_code: form.querySelector("#postal_code"),
    city: form.querySelector("#city"),
    region: form.querySelector("#region"),
  };
  let timer;
  let controller;
  let items = [];
  let active = -1;

  box.hidden = false;
  Object.values(locked).forEach((field) => {
    field.readOnly = true;
    field.classList.add("is-locked");
  });

  const close = () => {
    list.hidden = true;
    input.setAttribute("aria-expanded", "false");
    active = -1;
  };

  const choose = (item) => {
    locked.street.value = item.street;
    locked.postal_code.value = item.postal_code;
    locked.city.value = item.city;
    locked.region.value = item.region;
    if (item.country_code) country.value = item.country_code;
    input.value = [item.street, item.postal_code, item.city].filter(Boolean).join(", ");
    status.textContent = "Adres uzupełniony.";
    close();
  };

  const highlight = (index) => {
    active = index;
    [...list.children].forEach((li, i) => li.setAttribute("aria-selected", i === index ? "true" : "false"));
  };

  const render = () => {
    list.replaceChildren();
    items.forEach((item, index) => {
      const li = document.createElement("li");
      li.setAttribute("role", "option");
      li.setAttribute("aria-selected", "false");
      li.textContent = [item.street, [item.postal_code, item.city].filter(Boolean).join(" ")].filter(Boolean).join(", ");
      li.addEventListener("mousedown", (event) => {
        event.preventDefault();
        choose(item);
      });
      li.addEventListener("mousemove", () => highlight(index));
      list.append(li);
    });
    list.hidden = items.length === 0;
    input.setAttribute("aria-expanded", items.length ? "true" : "false");
  };

  const search = async () => {
    const q = input.value.trim();
    if (q.length < 3) {
      items = [];
      render();
      status.textContent = "";
      return;
    }
    controller?.abort();
    controller = new AbortController();
    status.textContent = "Szukam…";
    try {
      const params = new URLSearchParams({ q, country: (country.value || "PL").slice(0, 2) });
      const response = await fetch(`${form.dataset.suggestUrl}?${params}`, {
        headers: { Accept: "application/json" },
        signal: controller.signal,
      });
      if (!response.ok) throw new Error("lookup failed");
      items = (await response.json()).suggestions || [];
      status.textContent = items.length ? "" : "Brak podpowiedzi. Dodaj numer domu lub miejscowość.";
      render();
    } catch (error) {
      if (error.name === "AbortError") return;
      items = [];
      render();
      status.textContent = "Nie udało się pobrać podpowiedzi. Spróbuj ponownie.";
    }
  };

  input.addEventListener("input", () => {
    clearTimeout(timer);
    timer = setTimeout(search, 600);
  });

  input.addEventListener("keydown", (event) => {
    if (event.key === "ArrowDown" || event.key === "ArrowUp") {
      if (!items.length) return;
      event.preventDefault();
      const next = event.key === "ArrowDown" ? active + 1 : active - 1;
      highlight((next + items.length) % items.length);
    } else if (event.key === "Enter") {
      event.preventDefault();
      if (items[active]) choose(items[active]);
    } else if (event.key === "Escape") {
      close();
    }
  });

  input.addEventListener("blur", close);
})();
