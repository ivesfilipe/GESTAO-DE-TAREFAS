import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

function initEcho() {
    const key = document.body.dataset.reverbKey;
    if (!key || !document.body.dataset.authenticated) return;

    const isHttps = window.location.protocol === 'https:';

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: window.location.hostname,
        wsPort: 8080,
        wssPort: 8080,
        forceTLS: isHttps,
        enabledTransports: ['ws', 'wss'],
    });

    subscribeToUserChannel();
}

const TASK_EVENTS = [
    'task.tarefa_criada',
    'task.status_alterado',
    'task.prazo_alterado',
    'task.prioridade_alterada',
    'task.tarefa_atribuida',
    'task.comentario_adicionado',
    'task.anexo_adicionado',
    'task.tarefa_bloqueada',
    'task.tarefa_desbloqueada',
    'task.conclusao_solicitada',
    'task.tarefa_aprovada',
    'task.tarefa_reprovada',
    'task.alteracao_solicitada',
    'task.tarefa_cancelada',
];

const EVENT_LABELS = {
    'task.tarefa_criada': 'criou uma tarefa',
    'task.status_alterado': 'alterou o status',
    'task.prazo_alterado': 'alterou o prazo',
    'task.prioridade_alterada': 'alterou a prioridade',
    'task.tarefa_atribuida': 'atribuiu uma tarefa',
    'task.comentario_adicionado': 'comentou em',
    'task.anexo_adicionado': 'anexou arquivo em',
    'task.tarefa_bloqueada': 'bloqueou',
    'task.tarefa_desbloqueada': 'desbloqueou',
    'task.conclusao_solicitada': 'solicitou conclusão de',
    'task.tarefa_aprovada': 'aprovou',
    'task.tarefa_reprovada': 'reprovou',
    'task.alteracao_solicitada': 'solicitou alteração em',
    'task.tarefa_cancelada': 'cancelou',
};

function subscribeToUserChannel() {
    const userId = document.body.dataset.userId;
    if (!userId || !window.Echo) return;

    TASK_EVENTS.forEach((event) => {
        window.Echo.private(`user.${userId}`).listen(event, (payload) => {
            showLiveToast(payload, event);
            bumpNotificationBadge(event);
            document.dispatchEvent(new CustomEvent('task:updated', { detail: payload }));
        });
    });
}

function bumpNotificationBadge(event) {
    const badgeEvents = [
        'task.tarefa_atribuida',
        'task.comentario_adicionado',
        'task.conclusao_solicitada',
        'task.alteracao_solicitada',
        'task.tarefa_aprovada',
        'task.tarefa_reprovada',
        'task.tarefa_desbloqueada',
    ];
    if (!badgeEvents.includes(event)) return;

    const badge = document.querySelector('[data-notification-badge]');
    if (!badge) {
        location.reload();
        return;
    }
    const current = parseInt(badge.textContent, 10) || 0;
    badge.textContent = current + 1 > 9 ? '9+' : current + 1;
    badge.classList.remove('hidden');
}

function showLiveToast(payload, event) {
    let container = document.getElementById('live-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'live-toast-container';
        container.className = 'fixed bottom-6 right-6 z-[100] flex flex-col gap-2 max-w-sm';
        document.body.appendChild(container);
    }

    const label = EVENT_LABELS[event] ?? 'atualizou';
    const toast = document.createElement('div');
    toast.className =
        'flex items-start gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-lg transition-opacity duration-300 opacity-0';
    toast.innerHTML = `
        <div class="min-w-0">
            <p class="text-sm text-slate-700">
                <span class="font-semibold">${escapeHtml(payload.actor_name ?? 'Alguém')}</span>
                ${label}
                <a href="/tarefas/${payload.id}" class="font-medium text-brand-700 hover:underline">${escapeHtml(payload.title ?? '')}</a>
            </p>
        </div>
        <button type="button" aria-label="Fechar" class="ml-auto shrink-0 text-slate-400 hover:text-slate-600">&times;</button>
    `;
    toast.querySelector('button').addEventListener('click', () => toast.remove());
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.replace('opacity-0', 'opacity-100'));
    setTimeout(() => {
        toast.classList.replace('opacity-100', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 6000);
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

initEcho();
