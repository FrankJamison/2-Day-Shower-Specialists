/*
  Tracking loader
  - Replace IDs as needed:
    - Meta Pixel: YOUR_META_PIXEL_ID
    - Google Ads: YOUR_GOOGLE_ADS_ID (e.g. AW-123456789)
*/

(function () {
  const META_PIXEL_ID = 'YOUR_META_PIXEL_ID';
  const GOOGLE_ADS_ID = 'YOUR_GOOGLE_ADS_ID';

  const isRealId = (value) =>
    typeof value === 'string' && value.trim() !== '' && !value.includes('YOUR_');

  // Meta Pixel
  if (isRealId(META_PIXEL_ID)) {
    // eslint-disable-next-line no-unused-vars
    (function (f, b, e, v, n, t, s) {
      if (f.fbq) return;
      n = f.fbq = function () {
        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
      };
      if (!f._fbq) f._fbq = n;
      n.push = n;
      n.loaded = true;
      n.version = '2.0';
      n.queue = [];
      t = b.createElement(e);
      t.async = true;
      t.src = v;
      s = b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t, s);
    })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

    if (window.fbq) {
      window.fbq('init', META_PIXEL_ID);
      window.fbq('track', 'PageView');
    }
  }

  // Google Ads (gtag)
  if (isRealId(GOOGLE_ADS_ID)) {
    window.dataLayer = window.dataLayer || [];
    function gtag() {
      window.dataLayer.push(arguments);
    }
    window.gtag = window.gtag || gtag;

    const gtagScript = document.createElement('script');
    gtagScript.async = true;
    gtagScript.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(GOOGLE_ADS_ID)}`;
    document.head.appendChild(gtagScript);

    gtag('js', new Date());
    gtag('config', GOOGLE_ADS_ID);
  }
})();
