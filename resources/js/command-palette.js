function initCommandPalette() {
    const trigger = document.getElementById('palette-trigger');
    const modal = document.getElementById('command-palette');
    if (!modal) return;

    const input = modal.querySelector('#palette-input');
    const resultsBox = modal.querySelector('#palette-results');
    let activeIndex = -1;
    let items = [];
    let debounceTimer = null;
    let controller = null;

    const open = () => {
        modal.classList.remove('hidden');
        input.value = '';
        renderResults([]);
        requestAnimationFrame(() => input.focus());
    };

    const close = () => {
        modal.classList.add('hidden');
        if (controller) controller.abort();
    };

    const toggle = () => (modal.classList.contains('hidden') ? open() : close());

    trigger?.addEventListener('click', open);
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            toggle();
        }
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
    });
    modal.addEventListener('click', (e) => {
        if (e.target === modal) close();
    });

    function renderResults(results) {
        items = results;
        activeIndex = -1;
        if (!results.length) {
            resultsBox.innerHTML =
                '<p class="px-4 py-3 text-sm text-slate-400">Nenhuma tarefa encontrada. Digite pelo menos 2 caracteres.</p>';
            return;
        }
        resultsBox.innerHTML = results
            .map(
                (r, i) => `
                <a href="${r.url}" data-index="${i}" class="palette-item flex items-center justify-between gap-2 px-4 py-2.5 text-sm ${i === 0 ? 'bg-slate-100' : ''} hover:bg-slate-100">
                    <span class="min-w-0 truncate text-slate-700">${escapeHtml(r.title)}</span>
                    <span class="shrink-0 flex items-center gap-2">
                        ${r.assignee ? `<span class="text-xs text-slate-400">${escapeHtml(r.assignee)}</span>` : ''}
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase ${badgeClass(r.priority)}">${escapeHtml(r.priority)}</span>
                    </span>
                </a>`,
            )
            .join('');
        bindItems();
    }

    function badgeClass(priority) {
        return {
            critica: 'bg-red-100 text-red-700',
            urgente: 'bg-orange-100 text-orange-700',
            importante: 'bg-yellow-100 text-yellow-700',
            normal: 'bg-slate-100 text-slate-600',
        }[priority] ?? 'bg-slate-100 text-slate-600';
    }

    function bindItems() {
        resultsBox.querySelectorAll('.palette-item').forEach((el) => {
            el.addEventListener('mousemove', () => setActive(parseInt(el.dataset.index, 10)));
        });
    }

    function setActive(index) {
        resultsBox.querySelectorAll('.palette-item').forEach((el) => {
            el.classList.toggle('bg-slate-100', parseInt(el.dataset.index, 10) === index);
        });
        activeIndex = index;
    }

    function move(delta) {
        if (!items.length) return;
        const next = (activeIndex + delta + items.length) % items.length;
        setActive(next);
        resultsBox.querySelector(`[data-index="${next}"]`)?.scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            move(1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            move(-1);
        } else if (e.key === 'Enter') {
            const target = items[activeIndex] ?? items[0];
            if (target) window.location.href = target.url;
        }
    });

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const q = input.value.trim();
        if (q.length < 2) {
            renderResults([]);
            return;
        }
        debounceTimer = setTimeout(async () => {
            if (controller) controller.abort();
            controller = new AbortController();
            try {
                const res = await fetch(`/busca?q=${encodeURIComponent(q)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                if (!res.ok) return;
                const data = await res.json();
                renderResults(data.results ?? []);
            } catch (err) {
                if (err.name !== 'AbortError') console.error(err);
            }
        }, 250);
    });
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

initCommandPalette();
