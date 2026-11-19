const PRODUCT_NAME = "Naturadała – suplement diety Bulion kolagenowy · 400 ml";
const STORAGE_KEY = "naturadala-inquiry-list";
const englishTranslations = window.naturadalaEnglish || {};

function translatePolish(value) {
  const normalized = value.replace(/\s+/g, " ").trim();
  const translated = englishTranslations[normalized];
  if (translated) {
    return translated;
  }

  const retailerWelcome = normalized.match(/^Witaj, (.+)\. Tutaj znajdziesz katalog, warunki partnerskie i przypisane informacje o współpracy\.$/);
  if (retailerWelcome) {
    return `Welcome, ${retailerWelcome[1]}. Find your catalogue, partner terms and assigned cooperation records here.`;
  }

  if (normalized.endsWith(" | Naturadała")) {
    const pageTitle = normalized.slice(0, normalized.length - " | Naturadała".length);
    if (englishTranslations[pageTitle]) {
      return `${englishTranslations[pageTitle]} | Naturadała`;
    }
  }

  const resultCount = normalized.match(/^Wyświetlono (.+?) z (.+?) produktów(?: dla frazy „(.+)”)?$/);
  if (resultCount) {
    return `Showing ${resultCount[1]} of ${resultCount[2]} products${resultCount[3] ? ` for “${resultCount[3]}”` : ""}`;
  }

  const quantity = normalized.match(/^(\d+) szt\.$/);
  if (quantity) {
    return `${quantity[1]} ${quantity[1] === "1" ? "pc" : "pcs"}`;
  }

  const subscriberPrice = normalized.match(/^Cena dla subskrybenta \(−(\d+)%\)$/);
  if (subscriberPrice) {
    return `Subscriber price (−${subscriberPrice[1]}%)`;
  }

  const newsletterDiscount = normalized.match(/^Zapisz się do newslettera: −(\d+)%?(?: na wszystkie produkty)?$/);
  if (newsletterDiscount) {
    return `Subscribe to the newsletter: −${newsletterDiscount[1]}%${normalized.endsWith("na wszystkie produkty") ? " on all products" : ""}`;
  }

  const discountSuffix = normalized.match(/^(\d+)% zniżki\.$/);
  if (discountSuffix) {
    return `${discountSuffix[1]}% off.`;
  }

  const newsletterDescription = normalized.match(/^Zapisz się, a powiadomimy Cię e-mailem o nowych produktach\. Subskrybenci mają (\d+)% zniżki na wszystkie produkty po zalogowaniu na konto z tym samym adresem e-mail\.$/);
  if (newsletterDescription) {
    return `Subscribe and we will email you about new products. Subscribers receive a ${newsletterDescription[1]}% discount on all products when signed into the account with the same email address.`;
  }

  const subscriptionConfirmed = normalized.match(/^Subskrypcja jest potwierdzona\. Będziemy informować Cię o nowych produktach\. Zniżka (\d+)% na wszystkie produkty działa po zalogowaniu na konto z tym adresem e-mail\.$/);
  if (subscriptionConfirmed) {
    return `Your subscription is confirmed. We will let you know about new products. A ${subscriptionConfirmed[1]}% discount on all products applies when signed in with this email address.`;
  }

  const basketLinePrice = normalized.match(/^(.+?) zł \/ szt\. · razem (.+?) zł$/);
  if (basketLinePrice) {
    return `${basketLinePrice[1]} PLN / pc · total ${basketLinePrice[2]} PLN`;
  }

  const basketTotal = normalized.match(/^Suma: (.+?) zł$/);
  if (basketTotal) {
    return `Total: ${basketTotal[1]} PLN`;
  }

  const productSummaryLine = normalized.match(/^(.+) — (\d+) szt\.$/);
  if (productSummaryLine) {
    const productName = englishTranslations[productSummaryLine[1]] || productSummaryLine[1];
    const count = Number(productSummaryLine[2]);
    return `${productName} — ${count} ${count === 1 ? "pc" : "pcs"}`;
  }

  const dynamicPatterns = [
    [/^Zmniejsz ilość produktu (.+)$/, "Decrease quantity of $1"],
    [/^Zwiększ ilość produktu (.+)$/, "Increase quantity of $1"],
    [/^Usuń produkt (.+)$/, "Remove $1"],
    [/^Usuń (.+) z koszyka$/, "Remove $1 from basket"],
    [/^(.+) dodano do listy zapytania\.$/, "$1 added to the enquiry list."],
    [/^netto · od (.+) szt\.$/, "net · from $1 pcs"],
  ];

  for (const [pattern, replacement] of dynamicPatterns) {
    const match = normalized.match(pattern);
    if (match) {
      const result = normalized.replace(pattern, replacement);
      const productName = match[1];
      const translatedProductName = englishTranslations[productName];
      return translatedProductName ? result.replaceAll(productName, translatedProductName) : result;
    }
  }

  return value;
}

function translateDocumentNode(root) {
  const nodes = [root];
  while (nodes.length > 0) {
    const node = nodes.pop();

    if (node.nodeType === Node.TEXT_NODE) {
      const value = node.nodeValue;
      const trimmed = value.trim();
      if (trimmed) {
        const translated = translatePolish(trimmed);
        if (translated !== trimmed) {
          node.nodeValue = value.replace(trimmed, translated);
        }
      }
      continue;
    }

    if (!(node instanceof Element) || ["SCRIPT", "STYLE", "NOSCRIPT"].includes(node.tagName)) {
      continue;
    }

    for (const attribute of ["alt", "aria-label", "content", "placeholder", "title", "value"]) {
      if (!node.hasAttribute(attribute)) {
        continue;
      }
      if (attribute === "value" && node.tagName !== "BUTTON" && !(node.tagName === "INPUT" && ["button", "submit", "reset"].includes(node.type))) {
        continue;
      }
      const value = node.getAttribute(attribute);
      const translated = translatePolish(value);
      if (translated !== value) {
        node.setAttribute(attribute, translated);
      }
    }

    if (node.tagName !== "TEXTAREA") {
      for (let index = node.childNodes.length - 1; index >= 0; index--) {
        nodes.push(node.childNodes[index]);
      }
    }
  }
}

if (document.documentElement.lang === "en") {
  document.title = translatePolish(document.title);
  const description = document.head.querySelector('meta[name="description"]');
  if (description) {
    translateDocumentNode(description);
  }
  document.body.childNodes.forEach(translateDocumentNode);

  const translationObserver = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.type === "attributes") {
        translateDocumentNode(mutation.target);
      } else {
        mutation.addedNodes.forEach(translateDocumentNode);
      }
    });
  });
  translationObserver.observe(document.body, {
    attributes: true,
    childList: true,
    subtree: true,
    attributeFilter: ["alt", "aria-label", "content", "placeholder", "title", "value"],
  });
}

const cartPanel = document.querySelector(".cart-panel");
const cartBackdrop = document.querySelector(".cart-backdrop");
const cartItems = document.querySelector(".cart-items");
const announcementStatus = document.querySelector("#announcement-status");
const menuToggle = document.querySelector(".menu-toggle");
const navigation = document.querySelector(".primary-navigation");
let cart = [];
let previousFocus = null;

function canPersistCart() {
  return !window.naturadalaConsent || window.naturadalaConsent.has("preferences");
}

function readCart() {
  if (!canPersistCart()) {
    return [];
  }

  try {
    const saved = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || "[]");
    if (!Array.isArray(saved)) {
      throw new Error("Saved inquiry list is not an array.");
    }

    return saved.filter((item) =>
      item &&
      item.name === PRODUCT_NAME &&
      Number.isInteger(item.quantity) &&
      item.quantity > 0 &&
      item.quantity <= 99
    );
  } catch (error) {
    if (announcementStatus) {
      announcementStatus.textContent = "Nie udało się odczytać zapisanej listy zapytania. Rozpoczynamy nową listę.";
    }
    return [];
  }
}

function saveCart() {
  if (!canPersistCart()) {
    return true;
  }

  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    return true;
  } catch (error) {
    if (announcementStatus) {
      announcementStatus.textContent = "Nie udało się zapisać listy w tej przeglądarce. Możesz nadal wysłać zapytanie.";
    }
    return false;
  }
}

function renderCart() {
  if (!cartPanel || !cartItems) {
    return;
  }

  const accountCount = Number(cartPanel.dataset.accountCount || 0);
  const count = cart.reduce((sum, item) => sum + item.quantity, 0) + accountCount;
  document.querySelectorAll(".cart-count").forEach((element) => {
    element.textContent = String(count);
  });

  cartPanel.classList.toggle("is-empty", cart.length === 0 && accountCount === 0);
  cartItems.replaceChildren();

  cart.forEach((item, index) => {
    const line = document.createElement("div");
    line.className = "cart-line";

    const name = document.createElement("strong");
    name.textContent = item.name;

    const actions = document.createElement("div");
    actions.className = "cart-line-actions";

    const decrease = document.createElement("button");
    decrease.className = "quantity-button";
    decrease.type = "button";
    decrease.dataset.action = "decrease";
    decrease.dataset.index = String(index);
    decrease.setAttribute("aria-label", `Zmniejsz ilość produktu ${item.name}`);
    decrease.textContent = "−";

    const quantity = document.createElement("span");
    quantity.textContent = `${item.quantity} szt.`;

    const increase = document.createElement("button");
    increase.className = "quantity-button";
    increase.type = "button";
    increase.dataset.action = "increase";
    increase.dataset.index = String(index);
    increase.setAttribute("aria-label", `Zwiększ ilość produktu ${item.name}`);
    increase.textContent = "+";

    const remove = document.createElement("button");
    remove.className = "remove-button";
    remove.type = "button";
    remove.dataset.action = "remove";
    remove.dataset.index = String(index);
    remove.setAttribute("aria-label", `Usuń produkt ${item.name}`);
    remove.textContent = "Usuń";

    actions.append(decrease, quantity, increase, remove);
    line.append(name, actions);
    cartItems.append(line);
  });

}

function openCart() {
  previousFocus = document.activeElement;
  cartBackdrop.hidden = false;
  cartPanel.classList.add("is-open");
  cartPanel.setAttribute("aria-hidden", "false");
  document.body.classList.add("cart-open");
  document.querySelector(".cart-close").focus();
}

function closeCart() {
  cartPanel.classList.remove("is-open");
  cartPanel.setAttribute("aria-hidden", "true");
  document.body.classList.remove("cart-open");
  window.setTimeout(() => {
    cartBackdrop.hidden = true;
  }, 250);

  if (previousFocus instanceof HTMLElement) {
    previousFocus.focus();
  }
}

if (cartPanel && cartBackdrop && cartItems) {
  document.querySelectorAll(".cart-trigger").forEach((button) => {
    button.addEventListener("click", openCart);
  });

  document.querySelectorAll(".add-to-cart").forEach((button) => {
    button.addEventListener("click", () => {
      const existing = cart.find((item) => item.name === button.dataset.product);
      if (existing) {
        if (existing.quantity < 99) {
          existing.quantity += 1;
        } else {
          if (announcementStatus) {
            announcementStatus.textContent = "Maksymalna liczba sztuk na liście to 99.";
          }
          return;
        }
      } else {
        cart.push({ name: button.dataset.product, quantity: 1 });
      }

      saveCart();
      renderCart();
      if (announcementStatus) {
        announcementStatus.textContent = `${button.dataset.product} dodano do listy zapytania.`;
      }
      openCart();
    });
  });

  cartItems.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-action]");
    if (!button) {
      return;
    }

    const index = Number(button.dataset.index);
    const item = cart[index];
    if (!item) {
      return;
    }

    if (button.dataset.action === "increase" && item.quantity < 99) {
      item.quantity += 1;
    } else if (button.dataset.action === "decrease") {
      item.quantity -= 1;
      if (item.quantity === 0) {
        cart.splice(index, 1);
      }
    } else if (button.dataset.action === "remove") {
      cart.splice(index, 1);
    }

    saveCart();
    renderCart();
  });

  document.querySelector(".cart-close").addEventListener("click", closeCart);
  cartBackdrop.addEventListener("click", closeCart);
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && cartPanel.classList.contains("is-open")) {
      closeCart();
    }
  });
}

if (menuToggle && navigation) {
  menuToggle.addEventListener("click", () => {
    const isOpen = menuToggle.getAttribute("aria-expanded") === "true";
    menuToggle.setAttribute("aria-expanded", String(!isOpen));
    navigation.classList.toggle("is-open", !isOpen);
  });

  navigation.addEventListener("click", (event) => {
    if (event.target.closest("a")) {
      menuToggle.setAttribute("aria-expanded", "false");
      navigation.classList.remove("is-open");
    }
  });
}

document.querySelectorAll("[data-product-description-toggle]").forEach((toggle) => {
  toggle.addEventListener("click", () => {
    const description = document.getElementById(toggle.getAttribute("aria-controls"));
    if (!description) {
      return;
    }

    const isExpanded = toggle.getAttribute("aria-expanded") === "true";
    toggle.setAttribute("aria-expanded", String(!isExpanded));
    description.hidden = isExpanded;
    toggle.textContent = isExpanded ? toggle.dataset.moreLabel : toggle.dataset.lessLabel;
  });
});

document.querySelectorAll("[data-postcard-toggle]").forEach((toggle) => {
  const front = toggle.querySelector(".postcard-front");
  const back = toggle.querySelector(".postcard-back");

  if (!front || !back) {
    return;
  }

  toggle.addEventListener("click", () => {
    const isFlipped = toggle.getAttribute("aria-pressed") !== "true";
    toggle.setAttribute("aria-pressed", String(isFlipped));
    toggle.setAttribute("aria-label", isFlipped ? "Obróć kartę, aby wrócić" : "Obróć kartę, aby zobaczyć zdjęcie");
    front.setAttribute("aria-hidden", String(isFlipped));
    back.setAttribute("aria-hidden", String(!isFlipped));
  });
});

const accountType = document.querySelector("#account_type");
const businessFields = document.querySelector(".business-fields");
if (accountType && businessFields) {
  const updateBusinessFields = () => {
    const isRetailer = accountType.value === "retailer";
    businessFields.hidden = !isRetailer;
    businessFields.querySelector("#business_name").required = isRetailer;
  };

  accountType.addEventListener("change", updateBusinessFields);
  updateBusinessFields();
}

const currentYear = document.querySelector("#current-year");
if (currentYear) {
  currentYear.textContent = String(new Date().getFullYear());
}

const priceTierList = document.querySelector("[data-price-tier-list]");
const priceTierTemplate = document.querySelector("[data-price-tier-template]");
const addPriceTierButton = document.querySelector("[data-add-price-tier]");
if (priceTierList && priceTierTemplate && addPriceTierButton) {
  let nextPriceTierIndex = 0;
  priceTierList.querySelectorAll("[name]").forEach((input) => {
    const match = input.name.match(/^wholesale_prices\[(\d+)\]/);
    if (match) {
      nextPriceTierIndex = Math.max(nextPriceTierIndex, Number(match[1]) + 1);
    }
  });

  priceTierList.querySelectorAll("[data-remove-price-tier]").forEach((button) => {
    button.hidden = false;
  });
  addPriceTierButton.hidden = false;

  addPriceTierButton.addEventListener("click", () => {
    const rowMarkup = priceTierTemplate.innerHTML.replaceAll("__INDEX__", String(nextPriceTierIndex));
    priceTierList.insertAdjacentHTML("beforeend", rowMarkup);
    nextPriceTierIndex += 1;
    priceTierList.lastElementChild?.querySelector("input[type=number]")?.focus();
  });

  priceTierList.addEventListener("click", (event) => {
    const button = event.target.closest("[data-remove-price-tier]");
    if (button) {
      button.closest(".price-tier-row")?.remove();
    }
  });
}

const faqItems = document.querySelectorAll(".faq-item");
faqItems.forEach((item) => {
  const button = item.querySelector(".faq-question");
  if (!button) {
    return;
  }

  button.addEventListener("click", () => {
    const isOpen = item.classList.contains("is-open");

    faqItems.forEach((faqItem) => {
      faqItem.classList.remove("is-open");
      const faqButton = faqItem.querySelector(".faq-question");
      if (faqButton) {
        faqButton.setAttribute("aria-expanded", "false");
      }
    });

    if (!isOpen) {
      item.classList.add("is-open");
      button.setAttribute("aria-expanded", "true");
    }
  });
});

cart = readCart();
if (cartPanel) {
  renderCart();
}

const inquiryForm = document.querySelector("#inquiry-form");
const inquiryProductsInput = document.querySelector("#inquiry-products");
const inquiryProductSummary = document.querySelector("#inquiry-product-summary");
if (inquiryForm && inquiryProductsInput && inquiryProductSummary) {
  const inquiryWasSent = document.querySelector("[data-inquiry-sent]") !== null;
  if (inquiryWasSent) {
    try {
      window.localStorage.removeItem(STORAGE_KEY);
      cart = [];
    } catch (error) {
      if (announcementStatus) {
        announcementStatus.textContent = "Zapytanie zostało wysłane, ale nie udało się wyczyścić zapisanej listy produktów.";
      }
    }
  }

  let initialProducts = [];
  try {
    const parsedProducts = JSON.parse(inquiryProductsInput.value || "[]");
    if (Array.isArray(parsedProducts)) {
      initialProducts = parsedProducts.filter((item) =>
        item &&
        typeof item.name === "string" &&
        Number.isInteger(item.quantity) &&
        item.quantity > 0 &&
        item.quantity <= 999
      );
    }
  } catch (error) {
    if (announcementStatus) {
      announcementStatus.textContent = "Nie udało się odczytać produktów przekazanych z koszyka.";
    }
  }

  const productsFromBasket = initialProducts.length > 0;
  if (!inquiryWasSent && productsFromBasket) {
    cart = initialProducts;
  }

  if (!productsFromBasket) {
    inquiryProductsInput.value = JSON.stringify(cart);
  }
  inquiryProductSummary.replaceChildren();

  const heading = document.createElement("strong");
  heading.textContent = "Produkty z Twojej listy";
  inquiryProductSummary.append(heading);

  if (cart.length === 0) {
    const emptyMessage = document.createElement("p");
    emptyMessage.textContent = "Możesz wysłać także ogólne zapytanie bez wybranych produktów.";
    inquiryProductSummary.append(emptyMessage);
  } else {
    const list = document.createElement("ul");
    cart.forEach((item) => {
      const line = document.createElement("li");
      line.textContent = `${item.name} — ${item.quantity} szt.`;
      list.append(line);
    });
    inquiryProductSummary.append(list);
  }
}

document.querySelectorAll("[data-announcement-slider]").forEach((slider) => {
  const slides = [...slider.querySelectorAll(".announcement-slide")];
  const toggle = slider.querySelector("[data-announcement-toggle]");
  const delay = Number(slider.dataset.interval) || 20000;
  let index = 0;
  let timer = null;
  let userPaused = false;
  let hoverPaused = false;

  function show(nextIndex) {
    index = (nextIndex + slides.length) % slides.length;
    slides.forEach((slide, slideIndex) => {
      const isActive = slideIndex === index;
      slide.classList.toggle("is-active", isActive);
      slide.toggleAttribute("inert", !isActive);
    });
  }

  function stop() {
    window.clearInterval(timer);
    timer = null;
  }

  function start() {
    stop();
    if (slides.length > 1 && !userPaused && !hoverPaused) {
      timer = window.setInterval(() => show(index + 1), delay);
    }
  }

  slider.addEventListener("mouseenter", () => {
    hoverPaused = true;
    start();
  });
  slider.addEventListener("mouseleave", () => {
    hoverPaused = false;
    start();
  });
  slider.addEventListener("focusin", () => {
    hoverPaused = true;
    start();
  });
  slider.addEventListener("focusout", () => {
    hoverPaused = false;
    start();
  });

  if (toggle) {
    toggle.addEventListener("click", () => {
      userPaused = !userPaused;
      toggle.setAttribute("aria-label", userPaused ? "Wznów komunikaty" : "Wstrzymaj komunikaty");
      toggle.firstElementChild.textContent = userPaused ? "▶" : "❚❚";
      start();
    });
  }

  start();
});

if ("IntersectionObserver" in window) {
  const revealTargets = document.querySelectorAll(
    "main h1, main h2, main h3, .product-image-wrap, .story-image img, .catalogue-card-image img, .product-detail-image img"
  );
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const target = entry.target;
        target.classList.add("is-visible");
        revealObserver.unobserve(target);
        window.setTimeout(() => target.classList.remove("reveal", "reveal-image", "is-visible"), 1600);
      }
    });
  }, { threshold: 0.15, rootMargin: "0px 0px -6% 0px" });

  revealTargets.forEach((target) => {
    const bounds = target.getBoundingClientRect();
    const isInitiallyVisible = bounds.top < window.innerHeight && bounds.bottom > 0;
    if (isInitiallyVisible || target.closest(".hero")) {
      return;
    }
    target.classList.add(target.matches("img, .product-image-wrap") ? "reveal-image" : "reveal");
    revealObserver.observe(target);
  });

  document.documentElement.classList.add("motion-ready");
}

document.addEventListener("naturadala:consent", () => {
  if (canPersistCart()) {
    if (cart.length === 0) {
      cart = readCart();
      if (cartPanel) {
        renderCart();
      }
    } else {
      saveCart();
    }
  } else {
    try {
      window.localStorage.removeItem(STORAGE_KEY);
    } catch (error) {
      // Storage is unavailable, so there is nothing to remove.
    }
  }
});
