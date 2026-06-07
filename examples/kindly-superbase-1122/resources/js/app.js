document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_MAIN = 'kindly-superbase-1122:main-tab';
    const STORAGE_SUPABASE_SUB = 'kindly-superbase-1122:supabase-sub';
    const STORAGE_LOGS_STATUS = 'kindly-superbase-1122:logs-status-filter';
    const VALID_MAIN_TABS = ['tab1', 'tab2', 'supabase', 'logs'];
    const VALID_LOG_STATUSES = ['pending', 'processing', 'completed', 'failed'];
    const VALID_SUPABASE_SUBS = ['health'];
    const TRIGGER_TAB_OPEN = 'tab_open';
    const TRIGGER_TAB_RESTORE = 'tab_restore';
    const TRIGGER_TEST_CONNECTION = 'test_connection';

    const mainTriggers = document.querySelectorAll('.tab-trigger');
    const mainPanels = document.querySelectorAll('.tab-panel');
    const supabaseSubTriggers = document.querySelectorAll('.supabase-sub-trigger');
    const supabaseSubPanels = document.querySelectorAll('.supabase-sub-panel');
    const supabasePanel = document.querySelector('[data-supabase-panel]');
    const supabaseTab = document.querySelector('[data-supabase-tab]');
    const supabaseTabLoading = document.querySelector('[data-supabase-tab-loading]');
    const healthRoot = document.querySelector('[data-supabase-health]');
    const healthButton = document.querySelector('[data-supabase-health-check]');
    const logsPanel = document.querySelector('[data-logs-panel]');
    const panelScroll = document.querySelector('[data-tab-panel-scroll]');

    let supabaseRequest = null;
    let activeMainTab = null;

    const readStorage = (key) => {
        try {
            return localStorage.getItem(key);
        } catch {
            return null;
        }
    };

    const writeStorage = (key, value) => {
        try {
            localStorage.setItem(key, value);
        } catch {
            // Ignore quota / private mode errors.
        }
    };

    const statusBadgeClasses = {
        healthy: 'bg-emerald-100 text-emerald-800',
        unhealthy: 'bg-red-100 text-red-800',
        not_configured: 'bg-amber-100 text-amber-800',
        idle: 'bg-zinc-100 text-zinc-600',
    };

    const logStatusBadgeClasses = {
        completed: 'bg-emerald-100 text-emerald-800',
        processing: 'bg-sky-100 text-sky-800',
        failed: 'bg-red-100 text-red-800',
        pending: 'bg-amber-100 text-amber-800',
    };

    const setBadgeClasses = (element, classMap, status, fallback = 'idle') => {
        Object.values(classMap).forEach((className) => {
            className.split(' ').forEach((token) => element.classList.remove(token));
        });

        (classMap[status] ?? classMap[fallback])
            .split(' ')
            .forEach((token) => element.classList.add(token));
    };

    const setSupabaseLoading = (isLoading) => {
        const loadingOverlay = healthRoot?.querySelector('[data-supabase-loading]');

        if (loadingOverlay) {
            loadingOverlay.classList.toggle('hidden', !isLoading);
            loadingOverlay.classList.toggle('flex', isLoading);
            loadingOverlay.setAttribute('aria-hidden', isLoading ? 'false' : 'true');
        }

        if (supabasePanel) {
            supabasePanel.setAttribute('aria-busy', isLoading ? 'true' : 'false');
        }

        if (supabaseTabLoading) {
            supabaseTabLoading.classList.toggle('hidden', !isLoading);
        }

        if (healthButton) {
            healthButton.disabled = isLoading;
        }
    };

    const activateMainTab = (tab, { persist = true, runSupabase = true } = {}) => {
        if (!VALID_MAIN_TABS.includes(tab)) {
            return;
        }

        if (activeMainTab === tab) {
            return;
        }

        activeMainTab = tab;

        mainTriggers.forEach((item) => {
            const active = item.dataset.tab === tab;
            item.setAttribute('aria-selected', active ? 'true' : 'false');
            item.classList.toggle('border-zinc-900', active);
            item.classList.toggle('text-zinc-900', active);
            item.classList.toggle('border-transparent', !active);
            item.classList.toggle('text-zinc-500', !active);
        });

        mainPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.id !== `panel-${tab}`);
        });

        if (panelScroll) {
            panelScroll.scrollTop = 0;
        }

        if (persist) {
            writeStorage(STORAGE_MAIN, tab);
        }

        if (runSupabase && tab === 'supabase') {
            runSupabaseHealthCheck(TRIGGER_TAB_OPEN);
        }
    };

    const activateSupabaseSub = (sub, { persist = true } = {}) => {
        if (!VALID_SUPABASE_SUBS.includes(sub)) {
            return;
        }

        supabaseSubTriggers.forEach((item) => {
            const active = item.dataset.supabaseSub === sub;
            item.setAttribute('aria-selected', active ? 'true' : 'false');
            item.classList.toggle('bg-zinc-900', active);
            item.classList.toggle('text-white', active);
            item.classList.toggle('bg-zinc-100', !active);
            item.classList.toggle('text-zinc-600', !active);
        });

        supabaseSubPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.id !== `supabase-panel-${sub}`);
        });

        if (persist) {
            writeStorage(STORAGE_SUPABASE_SUB, sub);
        }
    };

    const resolveInitialMainTab = () => {
        const hashTab = window.location.hash.replace(/^#/, '');
        if (VALID_MAIN_TABS.includes(hashTab)) {
            return hashTab;
        }

        const stored = readStorage(STORAGE_MAIN);
        if (stored && VALID_MAIN_TABS.includes(stored)) {
            return stored;
        }

        return 'tab1';
    };

    const resolveInitialSupabaseSub = () => {
        const stored = readStorage(STORAGE_SUPABASE_SUB);
        if (stored && VALID_SUPABASE_SUBS.includes(stored)) {
            return stored;
        }

        return 'health';
    };

    const renderDetails = (details) => {
        const container = healthRoot?.querySelector('[data-health-details]');
        const list = healthRoot?.querySelector('[data-health-details-list]');

        if (!container || !list) {
            return;
        }

        if (!details || Object.keys(details).length === 0) {
            container.classList.add('hidden');
            list.innerHTML = '';
            return;
        }

        container.classList.remove('hidden');
        list.innerHTML = Object.entries(details)
            .map(([key, value]) => {
                const display = typeof value === 'string' || typeof value === 'number'
                    ? value
                    : JSON.stringify(value);

                return `
                    <div class="grid grid-cols-[8rem_1fr] gap-2 py-1">
                        <dt class="font-medium text-zinc-500">${key}</dt>
                        <dd class="text-zinc-800">${display}</dd>
                    </div>
                `;
            })
            .join('');
    };

    const updateHealthPanel = (result) => {
        if (!healthRoot) {
            return;
        }

        const statusEl = healthRoot.querySelector('[data-supabase-status]');
        const endpointEl = healthRoot.querySelector('[data-health-endpoint]');
        const checkedAtEl = healthRoot.querySelector('[data-health-checked-at]');
        const httpRowEl = healthRoot.querySelector('[data-health-http-row]');
        const httpStatusEl = healthRoot.querySelector('[data-health-http-status]');
        const messageEl = healthRoot.querySelector('[data-health-message]');
        const errorEl = healthRoot.querySelector('[data-health-error]');

        if (statusEl) {
            statusEl.dataset.supabaseStatus = result.status;
            statusEl.textContent = result.status.replaceAll('_', ' ');
            setBadgeClasses(statusEl, statusBadgeClasses, result.status, 'not_configured');
        }

        if (endpointEl) {
            endpointEl.textContent = result.endpoint ?? 'Not configured';
        }

        if (checkedAtEl) {
            checkedAtEl.textContent = result.checked_at ?? '—';
        }

        if (httpRowEl && httpStatusEl) {
            if (result.http_status !== undefined && result.http_status !== null) {
                httpRowEl.classList.remove('hidden');
                httpStatusEl.textContent = String(result.http_status);
            } else {
                httpRowEl.classList.add('hidden');
                httpStatusEl.textContent = '';
            }
        }

        if (messageEl) {
            messageEl.textContent = result.message ?? '';
        }

        const transactionRowEl = healthRoot.querySelector('[data-health-transaction-row]');
        const transactionIdEl = healthRoot.querySelector('[data-health-transaction-id]');
        if (transactionRowEl && transactionIdEl) {
            if (result.transaction_id) {
                transactionRowEl.classList.remove('hidden');
                transactionIdEl.textContent = result.transaction_id;
            } else {
                transactionRowEl.classList.add('hidden');
                transactionIdEl.textContent = '';
            }
        }

        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }

        renderDetails(result.details ?? null);
    };

    const activityLogsUrl = logsPanel?.dataset.activityLogsUrl ?? '';
    const logDefaultLimit = Number.parseInt(logsPanel?.dataset.logDefaultLimit ?? '5', 10) || 5;

    const readLogStatusFilter = () => {
        const stored = readStorage(STORAGE_LOGS_STATUS);

        if (stored === null || stored === '') {
            return '';
        }

        return VALID_LOG_STATUSES.includes(stored) ? stored : '';
    };

    let activeLogStatusFilter = readLogStatusFilter();

    const logSearchInput = logsPanel?.querySelector('[data-log-search]');
    const logClearButton = logsPanel?.querySelector('[data-log-clear]');
    const logFilterToggle = logsPanel?.querySelector('[data-log-filter-toggle]');
    const logFilterMenu = logsPanel?.querySelector('[data-log-filter-menu]');
    let activeLogSearch = (logSearchInput?.value ?? '').trim();

    const buildActivityLogsUrl = (offset, limit, { grouped = false, status = null } = {}) => {
        const url = new URL(activityLogsUrl, window.location.origin);
        url.searchParams.set('offset', String(offset));
        url.searchParams.set('limit', String(limit));

        if (grouped) {
            url.searchParams.set('grouped', '1');
        }

        const statusValue = status ?? activeLogStatusFilter;
        if (statusValue) {
            url.searchParams.set('status', statusValue);
        }

        if (activeLogSearch) {
            url.searchParams.set('search', activeLogSearch);
        }

        return url;
    };

    const fetchActivityLogs = async (offset, limit, options = {}) => {
        const response = await fetch(buildActivityLogsUrl(offset, limit, options), {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error(`Failed to load logs (${response.status}).`);
        }

        return response.json();
    };

    const updateLogSummary = (logs, { updateShowing = true } = {}) => {
        if (updateShowing) {
            const showingEl = logsPanel?.querySelector('[data-log-showing]');
            if (showingEl) {
                const label = buildShowingLabel(logs.showing ?? 0, logs.total ?? 0, logs.has_more === true);
                showingEl.textContent = label;
                showingEl.classList.toggle('hidden', label === '');
            }
        }

        const summaryRoot = logsPanel?.querySelector('[data-log-summary]');
        if (summaryRoot && logs.summary) {
            summaryRoot.querySelectorAll('[data-log-summary-item]').forEach((item) => {
                const status = item.dataset.status;
                const countEl = item.querySelector('[data-log-summary-count]');
                if (countEl && Object.prototype.hasOwnProperty.call(logs.summary, status)) {
                    countEl.textContent = String(logs.summary[status]);
                }
            });
        }
    };

    const buildLogEmptyMessage = () => {
        if (activeLogSearch) {
            return `No log entries match "${activeLogSearch}".`;
        }

        if (activeLogStatusFilter) {
            return `No ${activeLogStatusFilter} log entries.`;
        }

        return 'No log entries yet.';
    };

    const renderLogEntries = (logs) => {
        const entriesRoot = logsPanel?.querySelector('[data-log-entries]');
        if (!entriesRoot) {
            return;
        }

        const groups = Array.isArray(logs.groups)
            ? logs.groups.filter((group) => Array.isArray(group.entries) && group.entries.length > 0)
            : [];

        if (groups.length === 0) {
            entriesRoot.innerHTML = `
                <p class="rounded-lg border border-dashed border-zinc-300 px-4 py-6 text-center text-zinc-500" data-log-empty>
                    ${escapeHtml(buildLogEmptyMessage())}
                </p>
            `;
            return;
        }

        const sections = groups
            .slice()
            .sort((a, b) => VALID_LOG_STATUSES.indexOf(a.status) - VALID_LOG_STATUSES.indexOf(b.status))
            .map((group) => buildLogGroupHtml(group))
            .join('');

        entriesRoot.innerHTML = `<div class="space-y-5" data-log-groups>${sections}</div>`;

        wireLogMessageToggle(entriesRoot);
    };

    const updateLogStatusFilterButtons = () => {
        logsPanel?.querySelectorAll('[data-log-status-filter-value]').forEach((button) => {
            const value = button.dataset.logStatusFilterValue ?? '';
            const active = value === activeLogStatusFilter;
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
            button.classList.toggle('bg-zinc-900', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('hover:bg-zinc-800', active);
            button.classList.toggle('bg-white', !active);
            button.classList.toggle('text-zinc-600', !active);
            button.classList.toggle('hover:bg-zinc-50', !active);
        });
    };

    const refreshLogEntries = async () => {
        if (!activityLogsUrl) {
            return;
        }

        const logs = await fetchActivityLogs(0, logDefaultLimit, { grouped: true });
        updateLogSummary(logs);
        renderLogEntries(logs);
    };

    const updateLogClearVisibility = () => {
        if (!logClearButton) {
            return;
        }

        logClearButton.classList.remove('hidden');
    };

    const updateLogFilterIndicator = () => {
        if (logFilterToggle) {
            logFilterToggle.dataset.active = activeLogStatusFilter !== '' ? 'true' : 'false';
        }
    };

    const closeLogFilterMenu = () => {
        if (!logFilterMenu || logFilterMenu.classList.contains('hidden')) {
            return;
        }

        logFilterMenu.classList.add('hidden');
        logFilterToggle?.setAttribute('aria-expanded', 'false');
    };

    const toggleLogFilterMenu = () => {
        if (!logFilterMenu) {
            return;
        }

        const isOpen = logFilterMenu.classList.toggle('hidden') === false;
        logFilterToggle?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    };

    const applyLogStatusFilter = async (status) => {
        activeLogStatusFilter = VALID_LOG_STATUSES.includes(status) ? status : '';
        writeStorage(STORAGE_LOGS_STATUS, activeLogStatusFilter);
        updateLogStatusFilterButtons();
        updateLogFilterIndicator();
        updateLogClearVisibility();
        await refreshLogEntries();
    };

    const clearLogFilters = async () => {
        if (logSearchInput) {
            logSearchInput.value = '';
        }

        activeLogSearch = '';
        closeLogFilterMenu();
        await applyLogStatusFilter('');
    };

    const wireLogFilterControls = () => {
        logClearButton?.addEventListener('click', () => {
            clearLogFilters();
        });

        logFilterToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleLogFilterMenu();
        });

        logFilterMenu?.addEventListener('click', (event) => {
            if (event.target.closest('[data-log-status-filter-value]')) {
                closeLogFilterMenu();
            }
        });

        document.addEventListener('click', (event) => {
            if (!logFilterMenu || logFilterMenu.classList.contains('hidden')) {
                return;
            }

            if (!event.target.closest('[data-log-status-filter]')) {
                closeLogFilterMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeLogFilterMenu();
            }
        });
    };

    const wireLogStatusFilter = () => {
        logsPanel?.querySelectorAll('[data-log-status-filter-value]').forEach((button) => {
            if (button.dataset.wired === 'true') {
                return;
            }

            button.dataset.wired = 'true';
            button.addEventListener('click', () => {
                const value = button.dataset.logStatusFilterValue ?? '';
                if (value === activeLogStatusFilter) {
                    return;
                }

                applyLogStatusFilter(value);
            });
        });

        updateLogStatusFilterButtons();
    };

    const wireLogSearch = () => {
        if (!logSearchInput || logSearchInput.dataset.wired === 'true') {
            return;
        }

        logSearchInput.dataset.wired = 'true';

        let debounceTimer = null;

        const applySearch = async () => {
            const value = logSearchInput.value.trim();
            if (value === activeLogSearch) {
                return;
            }

            activeLogSearch = value;
            updateLogClearVisibility();
            await refreshLogEntries();
        };

        logSearchInput.addEventListener('input', () => {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(applySearch, 250);
        });

        logSearchInput.addEventListener('search', () => {
            window.clearTimeout(debounceTimer);
            applySearch();
        });
    };

    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const renderLogStatusBadge = (status) => {
        const classes = logStatusBadgeClasses[status] ?? 'bg-zinc-100 text-zinc-800';

        return `<span class="rounded-full px-2.5 py-0.5 text-xs font-medium uppercase tracking-wide ${classes}">${status}</span>`;
    };

    const buildLogMeta = (entry) => {
        const parts = [];

        if (entry.transaction_id) {
            const txn = entry.transaction_id.startsWith('sup_')
                ? entry.transaction_id
                : `sup_${entry.transaction_id}`;
            parts.push(`txn ${txn}`);
        }

        if (entry.action_label) {
            parts.push(`supabase · ${entry.action_label}`);
        }

        if (entry.trigger_label) {
            parts.push(entry.trigger_label);
        }

        if (entry.created_at_human) {
            parts.push(entry.created_at_human);
        }

        return parts.join(' · ');
    };

    const buildShowingLabel = (showing, total, hasMore) => {
        if (!showing || showing <= 0) {
            return '';
        }

        if (hasMore || total > showing) {
            return `Showing ${showing} of ${total} most recent`;
        }

        return `Showing ${showing} most recent`;
    };

    const encodeLogMessage = (message) => btoa(unescape(encodeURIComponent(message)));

    const decodeLogMessage = (encoded) => decodeURIComponent(escape(atob(encoded)));

    const buildLogMessageHtml = (entry) => {
        const truncated = entry.message_truncated === true;
        const preview = escapeHtml(entry.message_preview ?? entry.message ?? '');
        const fullMessageB64 = encodeLogMessage(entry.message ?? '');

        if (!truncated) {
            return `<p class="font-medium text-zinc-900" data-log-message><span data-log-message-text>${preview}</span></p>`;
        }

        return `
            <p
                class="font-medium text-zinc-900"
                data-log-message
                data-log-message-truncated="true"
                data-log-message-full-b64="${fullMessageB64}"
            >
                <span data-log-message-text>${preview}</span>
                <button
                    type="button"
                    class="ml-1 inline text-xs font-medium text-sky-600 hover:text-sky-700"
                    data-log-message-toggle
                    aria-expanded="false"
                >
                    Show more
                </button>
            </p>
        `;
    };

    const buildLogTxnDisplay = (transactionId) => {
        if (!transactionId) {
            return '';
        }

        return transactionId.startsWith('sup_') ? transactionId : `sup_${transactionId}`;
    };

    const buildLogEntryActionsHtml = (entry) => {
        const txn = buildLogTxnDisplay(entry.transaction_id);
        const entryJson = JSON.stringify({
            id: entry.id,
            transaction_id: txn,
            status: entry.status,
            message: entry.message,
            action: entry.action ?? null,
            trigger: entry.trigger ?? null,
            created_at: entry.created_at ?? null,
        }, null, 2);

        return `
            <div class="relative shrink-0" data-log-actions>
                <button
                    type="button"
                    data-log-actions-toggle
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-label="Manage entry"
                    class="rounded p-1 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <circle cx="10" cy="4" r="1.5" />
                        <circle cx="10" cy="10" r="1.5" />
                        <circle cx="10" cy="16" r="1.5" />
                    </svg>
                </button>

                <div
                    data-log-actions-menu
                    role="menu"
                    class="absolute right-0 z-20 mt-1 hidden w-44 overflow-hidden rounded-md border border-zinc-200 bg-white py-1 shadow-lg"
                >
                    <button type="button" role="menuitem" data-log-copy="txn" data-log-copy-value="${escapeHtml(txn)}" class="block w-full px-3 py-1.5 text-left text-xs font-medium text-zinc-600 hover:bg-zinc-50">Copy transaction ID</button>
                    <button type="button" role="menuitem" data-log-copy="entry" data-log-copy-encoded="true" data-log-copy-value="${encodeLogMessage(entryJson)}" class="block w-full px-3 py-1.5 text-left text-xs font-medium text-zinc-600 hover:bg-zinc-50">Copy log entry</button>
                </div>
            </div>
        `;
    };

    const buildLogEntryHtml = (entry) => `
        <li class="relative flex items-start justify-between gap-3 px-4 py-3" data-log-id="${entry.id}">
            <div class="min-w-0 flex-1 space-y-1">
                ${buildLogMessageHtml(entry)}
                <p class="text-xs text-zinc-500">${escapeHtml(buildLogMeta(entry))}</p>
            </div>
            ${buildLogEntryActionsHtml(entry)}
        </li>
    `;

    const copyText = async (text) => {
        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text);
                return true;
            }
        } catch {
            // Secure-context clipboard blocked — fall through to legacy path.
        }

        try {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            const ok = document.execCommand('copy');
            document.body.removeChild(textarea);
            return ok;
        } catch {
            return false;
        }
    };

    const closeAllLogActionMenus = () => {
        logsPanel?.querySelectorAll('[data-log-actions-menu]:not(.hidden)').forEach((menu) => {
            menu.classList.add('hidden');
            menu.parentElement
                ?.querySelector('[data-log-actions-toggle]')
                ?.setAttribute('aria-expanded', 'false');
        });
    };

    const handleLogCopy = async (button) => {
        const raw = button.dataset.logCopyValue ?? '';
        const text = button.dataset.logCopyEncoded === 'true' ? decodeLogMessage(raw) : raw;
        const ok = await copyText(text);

        if (!button.dataset.logCopyLabel) {
            button.dataset.logCopyLabel = button.textContent;
        }

        button.textContent = ok ? 'Copied!' : 'Copy failed';

        window.setTimeout(() => {
            button.textContent = button.dataset.logCopyLabel ?? button.textContent;
            closeAllLogActionMenus();
        }, 800);
    };

    const wireLogActions = () => {
        if (!logsPanel || logsPanel.dataset.actionsWired === 'true') {
            return;
        }

        logsPanel.dataset.actionsWired = 'true';

        logsPanel.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-log-actions-toggle]');
            if (toggle) {
                event.stopPropagation();
                const menu = toggle.parentElement?.querySelector('[data-log-actions-menu]');
                const willOpen = menu?.classList.contains('hidden');
                closeAllLogActionMenus();
                if (menu && willOpen) {
                    menu.classList.remove('hidden');
                    toggle.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            const copyButton = event.target.closest('[data-log-copy]');
            if (copyButton) {
                event.stopPropagation();
                handleLogCopy(copyButton);
            }
        });

        document.addEventListener('click', () => closeAllLogActionMenus());
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllLogActionMenus();
            }
        });
    };

    const buildLogGroupHtml = (group) => `
        <section data-log-group="${group.status}">
            <div class="mb-2 flex items-center gap-2">
                ${renderLogStatusBadge(group.status)}
                <span class="text-xs font-medium text-zinc-400">${group.total ?? group.entries.length}</span>
            </div>
            <ul class="divide-y divide-zinc-200 rounded-lg border border-zinc-200" data-log-list data-log-group-list="${group.status}">
                ${group.entries.map((entry) => buildLogEntryHtml(entry)).join('')}
            </ul>
            ${group.has_more ? buildShowMoreHtml(group.status) : ''}
        </section>
    `;

    const wireLogMessageToggle = (root) => {
        root.querySelectorAll('[data-log-message-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const messageEl = button.closest('[data-log-message]');
                const textEl = messageEl?.querySelector('[data-log-message-text]');
                const fullMessage = messageEl?.dataset.logMessageFullB64
                    ? decodeLogMessage(messageEl.dataset.logMessageFullB64)
                    : messageEl?.dataset.logMessageFull;
                const isExpanded = button.getAttribute('aria-expanded') === 'true';

                if (!messageEl || !textEl || !fullMessage) {
                    return;
                }

                if (isExpanded) {
                    textEl.textContent = messageEl.dataset.logMessagePreview ?? textEl.textContent;
                    button.textContent = 'Show more';
                    button.setAttribute('aria-expanded', 'false');
                    return;
                }

                if (!messageEl.dataset.logMessagePreview) {
                    messageEl.dataset.logMessagePreview = textEl.textContent;
                }

                textEl.textContent = fullMessage;
                button.textContent = 'Show less';
                button.setAttribute('aria-expanded', 'true');
            });
        });
    };

    const buildShowMoreHtml = (status) => `
        <div class="flex flex-col items-center gap-2 pt-3" data-log-more-footer>
            <div class="flex items-center gap-1.5 text-zinc-300" aria-hidden="true">
                <span class="h-1 w-1 rounded-full bg-current"></span>
                <span class="h-1 w-1 rounded-full bg-current"></span>
                <span class="h-1 w-1 rounded-full bg-current"></span>
            </div>
            <button
                type="button"
                class="text-sm font-medium text-zinc-600 underline-offset-2 hover:text-zinc-900 hover:underline"
                data-log-show-more
                data-log-status="${status}"
            >
                Load more
            </button>
        </div>
    `;

    // Delegated: one listener handles every per-status "Load more" footer,
    // including server-rendered ones and any re-rendered after a refresh.
    const wireLogShowMore = (root) => {
        if (!root || root.dataset.showMoreWired === 'true') {
            return;
        }

        root.dataset.showMoreWired = 'true';
        root.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-log-show-more]');
            if (!button || !activityLogsUrl) {
                return;
            }

            const section = button.closest('[data-log-group]');
            const list = section?.querySelector('[data-log-list]');
            const status = button.dataset.logStatus || section?.dataset.logGroup;
            if (!list || !status) {
                return;
            }

            const footer = button.closest('[data-log-more-footer]');
            const offset = list.querySelectorAll('[data-log-id]').length;
            const defaultLabel = button.textContent;

            button.disabled = true;
            button.textContent = 'Loading…';

            try {
                // Flat slice for this one status: next page after what is shown.
                const logs = await fetchActivityLogs(offset, logDefaultLimit, { status });

                if (!Array.isArray(logs.entries) || logs.entries.length === 0) {
                    footer?.remove();
                    return;
                }

                list.insertAdjacentHTML('beforeend', logs.entries.map((entry) => buildLogEntryHtml(entry)).join(''));
                wireLogMessageToggle(list);

                if (!logs.has_more) {
                    footer?.remove();
                }
            } catch {
                button.textContent = defaultLabel;
            } finally {
                if (button.isConnected) {
                    button.disabled = false;
                    button.textContent = defaultLabel;
                }
            }
        });
    };

    const updateLogsPanel = (logs) => {
        if (!logsPanel) {
            return;
        }

        if (logs) {
            updateLogSummary(logs, { updateShowing: false });
        }

        refreshLogEntries().catch(() => {
            // Keep prior entries if reload fails.
        });
    };

    const runSupabaseHealthCheck = async (trigger) => {
        if (!healthButton) {
            return null;
        }

        if (supabaseRequest) {
            return supabaseRequest;
        }

        const url = healthButton.dataset.healthCheckUrl;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const defaultLabel = healthButton.textContent;

        setSupabaseLoading(true);
        healthButton.textContent = 'Checking…';

        supabaseRequest = (async () => {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf ?? '',
                    },
                    body: JSON.stringify({ trigger }),
                });

                if (!response.ok) {
                    throw new Error(`Health check failed (${response.status}).`);
                }

                const payload = await response.json();
                updateHealthPanel(payload.health ?? payload);
                updateLogsPanel(payload.logs ?? null);

                return payload;
            } catch (error) {
                const errorEl = healthRoot?.querySelector('[data-health-error]');
                if (errorEl) {
                    errorEl.textContent = error instanceof Error ? error.message : 'Health check failed.';
                    errorEl.classList.remove('hidden');
                }

                return null;
            } finally {
                setSupabaseLoading(false);
                healthButton.textContent = defaultLabel;
                supabaseRequest = null;
            }
        })();

        return supabaseRequest;
    };

    mainTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            activateMainTab(trigger.dataset.tab);
        });
    });

    supabaseSubTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            activateSupabaseSub(trigger.dataset.supabaseSub);
        });
    });

    if (healthButton) {
        healthButton.addEventListener('click', () => {
            runSupabaseHealthCheck(TRIGGER_TEST_CONNECTION);
        });
    }

    const initialTab = resolveInitialMainTab();
    activateMainTab(initialTab, { persist: false, runSupabase: false });
    activateSupabaseSub(resolveInitialSupabaseSub(), { persist: false });

    if (initialTab === 'supabase') {
        runSupabaseHealthCheck(TRIGGER_TAB_RESTORE);
    }

    if (logsPanel) {
        wireLogMessageToggle(logsPanel);
        wireLogShowMore(logsPanel);
        wireLogStatusFilter();
        wireLogSearch();
        wireLogFilterControls();
        wireLogActions();
        updateLogFilterIndicator();
        updateLogClearVisibility();

        if (activeLogStatusFilter) {
            refreshLogEntries().catch(() => {
                activeLogStatusFilter = '';
                writeStorage(STORAGE_LOGS_STATUS, '');
                updateLogStatusFilterButtons();
            });
        }
    }
});
