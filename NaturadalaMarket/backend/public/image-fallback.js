(function () {
  var placeholder = 'data:image/svg+xml;utf8,' + encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><rect width="400" height="400" fill="#f3efe3"/>' +
    '<text x="200" y="190" text-anchor="middle" font-family="Georgia,serif" font-size="34" fill="#4f6b3a">Naturadała</text>' +
    '<text x="200" y="230" text-anchor="middle" font-family="Arial,sans-serif" font-size="16" fill="#8a8a7a">obraz niedostępny</text></svg>'
  );

  function swap(img) {
    if (img.dataset.fallbackApplied) return;
    img.dataset.fallbackApplied = '1';
    img.removeAttribute('srcset');
    img.src = placeholder;
    img.classList.add('image-fallback');
  }

  document.addEventListener('error', function (event) {
    if (event.target && event.target.tagName === 'IMG') swap(event.target);
  }, true);

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('img').forEach(function (img) {
      if (img.complete && img.naturalWidth === 0 && img.getAttribute('src')) swap(img);
    });

    // Remote images that hang (host unreachable) never fire "error", so give up after a while.
    setTimeout(function () {
      document.querySelectorAll('img').forEach(function (img) {
        if (img.loading !== 'lazy' && !img.complete && !img.dataset.fallbackApplied) swap(img);
      });
    }, 8000);
  });
})();
