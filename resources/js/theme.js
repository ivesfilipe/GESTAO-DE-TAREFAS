(() => {
  const STORAGE_KEY = 'mt-theme';
  const root = document.documentElement;

  const apply = (theme) => {
    const isDark = theme === 'dark';
    root.classList.toggle('dark', isDark);
    // update theme-color meta
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', isDark ? '#020a14' : '#083048');
    // update icons
    document.querySelectorAll('[data-theme-icon]').forEach((el) => {
      const sun = el.querySelector('[data-icon="sun"]');
      const moon = el.querySelector('[data-icon="moon"]');
      if (sun && moon) {
        sun.classList.toggle('hidden', isDark);
        moon.classList.toggle('hidden', !isDark);
      }
    });
  };

  const getPreferred = () => {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored === 'dark' || stored === 'light') return stored;
    } catch {}
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const current = getPreferred();
  apply(current);

  const toggle = () => {
    const isDark = root.classList.contains('dark');
    const next = isDark ? 'light' : 'dark';
    try { localStorage.setItem(STORAGE_KEY, next); } catch {}
    apply(next);
  };

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
      btn.addEventListener('click', toggle);
    });
  });

  // also bind immediately if buttons already in DOM (for non-DOMContentLoaded cases)
  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', toggle);
  });
})();
