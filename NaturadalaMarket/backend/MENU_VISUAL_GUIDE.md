# Menu Bar Structure - Visual Guide

## Menu Hierarchy After Consolidation

```
┌─────────────────────────────────────────────────────────────┐
│                      UNIFIED MENU BAR                       │
│              (shared/header.blade.php component)            │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  [Logo] [Menu Toggle] [Language]    [Navigation]  [Actions] │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## Desktop Layout (≥981px)

```
┌────────────────────────────────────────────────────────────────┐
│  Naturadała Logo                                               │
│                                                                 │
│  Produkty | Dla kogo | FAQ | O Nas | Kontakt | Zaloguj [↗]    │
│                                                        Oferta   │
│                                                        Koszyk    │
└────────────────────────────────────────────────────────────────┘
```

## Tablet Layout (681px - 980px)

```
┌──────────────────────────────────────────────────────────────┐
│  [Logo Logo] [EN▼]        Zaloguj | Oferta [↗] | Koszyk      │
├──────────────────────────────────────────────────────────────┤
│  Produkty                                                      │
│  Dla kogo                                                      │
│  Pytania i odpowiedzi                                          │
│  O Naturadale                                                  │
│  Kontakt                                                       │
└──────────────────────────────────────────────────────────────┘
```

## Mobile Layout (≤680px)

```
┌─────────────────────────────────────────┐
│  [Logo Logo] [EN▼]                      │
├─────────────────────────────────────────┤
│  Zaloguj        Oferta [↗]  Koszyk      │
├─────────────────────────────────────────┤
│  Produkty                                 │
│  Dla kogo                                 │
│  Pytania i odpowiedzi                     │
│  O Naturadale                             │
│  Kontakt                                  │
└─────────────────────────────────────────┘
```

## How the Consolidation Works

### Before (Fragmented)
```
storefront.blade.php (34 lines of header code)
    ↓
    ├─→ Different nav links
    └─→ Missing "Strona główna"

products/layout.blade.php (38 lines of header code)
    ↓
    ├─→ Different nav links
    ├─→ Includes "Strona główna"
    └─→ Extra partner/wishlist links
```

### After (Consolidated)
```
shared/header.blade.php (1 unified component)
    ↓
    ├─→ storefront.blade.php
    │      @include('shared.header', ['currentPage' => 'home'])
    │      
    ├─→ products/layout.blade.php
    │      @include('shared.header', ['currentPage' => 'products'])
    │
    └─→ auth/layout.blade.php (still separate - simplified)
    └─→ retailer/layout.blade.php (still separate - partner nav)
```

## Component Parameters

```php
@include('shared.header', [
    'currentPage' => 'home|products',  // Used for aria-current="page"
    'hideNavigation' => false           // Can hide nav if needed
])
```

## Navigation Links (Consistent Order)

### On Home Page (storefront)
1. Produkty (link to products)
2. Dla kogo (anchor to #dla-kogo)
3. Pytania i odpowiedzi (anchor to #faq)
4. O Naturadale (anchor to #o-nas)
5. Kontakt (anchor to #kontakt)

*Note: "Strona główna" is hidden on home page (redundant)*

### On Products Page
1. **Strona główna** (link to home)
2. Produkty (active - marked with aria-current="page")
3. Dla kogo (anchor to home#dla-kogo)
4. Pytania i odpowiedzi (anchor to home#faq)
5. O Naturadale (anchor to home#o-nas)
6. Kontakt (anchor to home#kontakt)

## CSS Cascade

```css
/* Base header (desktop) */
.site-header {
  grid-template-columns: auto 1fr auto;
  display: grid;
  /* ... full navigation visible ... */
}

/* Tablet (1150px to 980px) */
@media (max-width: 1150px) {
  .site-header {
    /* ... grid adjustments ... */
  }
  .primary-navigation {
    /* ... dropdown positioning ... */
  }
}

/* Mobile (≤980px) */
@media (max-width: 980px) {
  .site-header {
    grid-template-columns: 1fr;
  }
  .primary-navigation {
    position: absolute;
    display: none; /* hidden until toggled */
  }
  .primary-navigation.is-open {
    display: flex;
  }
}

/* Small mobile (≤680px) */
@media (max-width: 680px) {
  .menu-toggle-logo {
    width: 207px; /* responsive logo sizing */
  }
}
```

## JavaScript Behavior

```javascript
const menuToggle = document.querySelector(".menu-toggle");
const navigation = document.querySelector(".primary-navigation");

if (menuToggle && navigation) {
  // Toggle menu open/closed
  menuToggle.addEventListener("click", () => {
    const isOpen = menuToggle.getAttribute("aria-expanded") === "true";
    menuToggle.setAttribute("aria-expanded", String(!isOpen));
    navigation.classList.toggle("is-open", !isOpen);
  });

  // Close menu when link clicked
  navigation.addEventListener("click", (event) => {
    if (event.target.tagName === "A") {
      menuToggle.setAttribute("aria-expanded", "false");
      navigation.classList.remove("is-open");
    }
  });
}
```

## Accessibility Features

✓ **Semantic HTML**
- `<header>` element properly used
- `<nav>` with aria-label for context
- `<a>` and `<button>` used correctly

✓ **ARIA Attributes**
- `aria-label="Menu"` on toggle button
- `aria-expanded` tracks menu state
- `aria-controls="primary-navigation"` links button to menu
- `aria-label="Nawigacja główna"` on nav
- `aria-current="page"` on active navigation link

✓ **Keyboard Navigation**
- All elements tab-accessible
- Focus indicators visible
- Menu closes with Escape key (via app.js)
- Enter/Space activates buttons

✓ **Mobile Touch**
- Touch targets ≥44px minimum height
- Responsive font sizes
- Clear button areas

## Performance Impact

**Before (Fragmented):**
- storefront.blade.php: 34 lines header
- products/layout.blade.php: 38 lines header
- Total: 72 duplicate lines

**After (Consolidated):**
- shared/header.blade.php: 45 lines
- storefront.blade.php: 1 line include
- products/layout.blade.php: 1 line include
- Total: 47 lines (34% reduction)

**CSS Changes:**
- Removed 3 duplicate `.retailer-header` definitions
- Removed 2 duplicate `.retailer-navigation` definitions
- File size slightly reduced while maintaining all functionality

## Migration Checklist

- [x] Component created with all necessary logic
- [x] Storefront updated to use component
- [x] Products layout updated to use component
- [x] CSS duplicates removed
- [x] Blade syntax validated
- [x] CSS validated (all braces balanced)
- [x] Responsive behavior verified
- [x] Navigation links consistent
- [x] Auth logic preserved
- [x] Special layouts preserved (auth, retailer)
- [x] Documentation created

## Future Enhancements

Now that the header is centralized, you can easily:
- Add new navigation items globally
- Change menu styling without affecting multiple files
- Implement mega-menus or dropdowns
- Add announcement bars
- Modify responsive breakpoints
- Update accessibility features
- A/B test different navigation structures
