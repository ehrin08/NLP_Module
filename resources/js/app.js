import './bootstrap';

const toastStack = () => document.querySelector('[data-toast-stack]');
const chartInstances = new Map();
let activeModal = null;

const dashboardPreferenceDefaults = {
    animate_charts: true,
    compact_table: false,
};

function showToast(message, type = 'success') {
    const stack = toastStack();

    if (!stack || !message) {
        return;
    }

    const palette = {
        success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
        error: 'border-red-200 bg-red-50 text-red-800',
    };

    const toast = document.createElement('div');
    toast.className = `pointer-events-auto rounded-lg border px-4 py-3 text-sm font-medium shadow-lg transition ${palette[type] ?? palette.success}`;
    toast.textContent = message;

    stack.appendChild(toast);

    window.setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-[-4px]');
    }, 2800);

    window.setTimeout(() => {
        toast.remove();
    }, 3200);
}

function getModal(id) {
    return document.getElementById(id);
}

function openModal(id) {
    const modal = getModal(id);

    if (!modal) {
        return;
    }

    closeModal(activeModal?.id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
    activeModal = modal;

    const focusTarget = modal.querySelector('input, select, textarea, button');
    focusTarget?.focus();
}

function closeModal(id) {
    const modal = id ? getModal(id) : activeModal;

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');

    if (activeModal === modal) {
        activeModal = null;
        document.body.classList.remove('overflow-hidden');
    }
}

function setModalTitle(modalId, title) {
    const titleNode = getModal(modalId)?.querySelector('[data-modal-title]');

    if (titleNode) {
        titleNode.textContent = title;
    }
}

function currentQueryUrl(pathname) {
    return new URL(window.location.href).pathname === pathname ? new URL(window.location.href) : new URL(pathname, window.location.origin);
}

function buildUrlFromForm(form) {
    const url = currentQueryUrl(new URL(form.action, window.location.origin).pathname);
    const formData = new FormData(form);

    Array.from(url.searchParams.keys()).forEach((key) => {
        if (!form.elements.namedItem(key)) {
            return;
        }

        url.searchParams.delete(key);
    });

    formData.forEach((value, key) => {
        if (value !== '') {
            url.searchParams.set(key, value.toString());
        } else {
            url.searchParams.delete(key);
        }
    });

    return url;
}

function setSectionLoading(sectionKey, loading) {
    const section = document.querySelector(`[data-async-section="${sectionKey}"]`);

    if (!section) {
        return null;
    }

    section.classList.toggle('pointer-events-none', loading);
    section.classList.toggle('opacity-60', loading);

    return section;
}

async function refreshSection(sectionKey, url, pushState = true) {
    const section = setSectionLoading(sectionKey, true);

    if (!section) {
        return;
    }

    try {
        const response = await window.axios.get(url.toString(), {
            headers: {
                Accept: 'application/json',
            },
        });

        section.innerHTML = response.data.html;

        if (response.data.url && pushState) {
            window.history.pushState({}, '', response.data.url);
        }

        syncFormsToUrl();
        applyDashboardPreferences();
        renderDashboardCharts();
    } catch (error) {
        showToast(error.response?.data?.message || 'Unable to refresh data right now.', 'error');
    } finally {
        setSectionLoading(sectionKey, false);
    }
}

function clearFieldErrors(form) {
    form.querySelectorAll('[data-field-error]').forEach((node) => {
        node.textContent = '';
    });

    form.querySelectorAll('input, select, textarea').forEach((field) => {
        field.classList.remove('border-red-500', 'ring-red-100', 'focus:ring-red-100');
    });
}

function applyFieldErrors(form, errors = {}) {
    Object.entries(errors).forEach(([fieldName, messages]) => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        const errorNode = form.querySelector(`[data-field-error="${fieldName}"]`);

        if (field) {
            field.classList.add('border-red-500', 'ring-red-100', 'focus:ring-red-100');
        }

        if (errorNode) {
            errorNode.textContent = Array.isArray(messages) ? messages[0] : messages;
        }
    });
}

function setSubmitting(form, submitting, label = 'Save') {
    const submitButton = form.querySelector('[data-submit-label]');

    if (!submitButton) {
        return;
    }

    submitButton.disabled = submitting;
    submitButton.textContent = submitting ? `${label}...` : label;
}

function populateFeedbackForm(dataset = {}) {
    const form = document.querySelector('[data-feedback-form]');

    if (!form) {
        return;
    }

    const isEdit = Boolean(dataset.updateUrl);
    form.action = isEdit ? dataset.updateUrl : form.dataset.storeUrl;
    form.querySelector('[data-feedback-form-method]').innerHTML = isEdit
        ? '<input type="hidden" name="_method" value="PUT">'
        : '';

    form.querySelector('[name="customer_name"]').value = dataset.customerName || '';
    form.querySelector('[name="service_name"]').value = dataset.serviceName || '';
    form.querySelector('[name="rating"]').value = dataset.rating || '5';
    form.querySelector('[name="feedback_text"]').value = dataset.feedbackText || '';

    const buttonLabel = isEdit ? 'Save changes' : 'Save feedback';
    setModalTitle('feedback-form-modal', isEdit ? 'Edit Feedback' : 'Add Feedback');
    setSubmitting(form, false, buttonLabel);
    form.dataset.submitLabel = buttonLabel;
    clearFieldErrors(form);
}

function populateFeedbackShow(dataset) {
    const modal = getModal('feedback-show-modal');

    if (!modal) {
        return;
    }

    const values = {
        customer_name: dataset.customerName,
        service_name: dataset.serviceName,
        rating: `${dataset.rating}/5`,
        confidence_score: `${dataset.confidenceScore}%`,
        predicted_sentiment: dataset.predictedSentiment,
        created_at: dataset.createdAt,
        feedback_text: dataset.feedbackText,
    };

    Object.entries(values).forEach(([key, value]) => {
        const node = modal.querySelector(`[data-feedback-show="${key}"]`);

        if (node) {
            node.textContent = value || '';
        }
    });
}

function populateDeleteModal(dataset) {
    const form = document.querySelector('[data-feedback-delete-form]');
    const nameNode = document.querySelector('[data-feedback-delete-name]');

    if (form) {
        form.action = dataset.deleteUrl || '';
    }

    if (nameNode) {
        nameNode.textContent = dataset.customerName || 'this customer';
    }
}

function getDashboardPreferences() {
    try {
        return {
            ...dashboardPreferenceDefaults,
            ...JSON.parse(window.localStorage.getItem('dashboardPreferences') || '{}'),
        };
    } catch {
        return { ...dashboardPreferenceDefaults };
    }
}

function saveDashboardPreferences(preferences) {
    window.localStorage.setItem('dashboardPreferences', JSON.stringify(preferences));
}

function applyDashboardPreferences() {
    const preferences = getDashboardPreferences();
    const table = document.querySelector('[data-dashboard-latest-table]');
    const settingsForm = document.querySelector('[data-dashboard-settings-form]');

    if (table) {
        table.querySelectorAll('th, td').forEach((cell) => {
            cell.classList.toggle('py-2', preferences.compact_table);
            cell.classList.toggle('py-3', !preferences.compact_table);
        });
    }

    if (settingsForm) {
        settingsForm.elements.animate_charts.checked = Boolean(preferences.animate_charts);
        settingsForm.elements.compact_table.checked = Boolean(preferences.compact_table);
    }
}

function destroyCharts() {
    chartInstances.forEach((chart) => chart.destroy());
    chartInstances.clear();
}

function renderDashboardCharts() {
    const state = document.querySelector('[data-dashboard-state]');

    if (!state || !window.Chart) {
        return;
    }

    destroyCharts();

    const preferences = getDashboardPreferences();
    const labels = JSON.parse(state.dataset.chartLabels || '[]');
    const pieLabels = JSON.parse(state.dataset.pieLabels || '[]');
    const pieData = JSON.parse(state.dataset.pieData || '[]');
    const trendData = JSON.parse(state.dataset.trendData || '{}');
    const taglishTrendData = JSON.parse(state.dataset.taglishTrendData || '{}');
    const animation = preferences.animate_charts;

    const pieContext = document.getElementById('sentimentPie');
    const trendContext = document.getElementById('trendLine');
    const taglishTrendContext = document.getElementById('taglishTrendLine');

    if (pieContext) {
        chartInstances.set('pie', new window.Chart(pieContext, {
            type: 'pie',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieData,
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }],
            },
            options: {
                animation,
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        }));
    }

    if (trendContext) {
        chartInstances.set('trend', new window.Chart(trendContext, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Positive',
                        data: trendData.Positive || [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        tension: 0.35,
                    },
                    {
                        label: 'Neutral',
                        data: trendData.Neutral || [],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.12)',
                        tension: 0.35,
                    },
                    {
                        label: 'Negative',
                        data: trendData.Negative || [],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.12)',
                        tension: 0.35,
                    },
                ],
            },
            options: {
                animation,
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                    },
                },
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        }));
    }

    if (taglishTrendContext) {
        chartInstances.set('taglish', new window.Chart(taglishTrendContext, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Positive Taglish',
                        data: taglishTrendData.Positive || [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        tension: 0.35,
                    },
                    {
                        label: 'Neutral Taglish',
                        data: taglishTrendData.Neutral || [],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.12)',
                        tension: 0.35,
                    },
                    {
                        label: 'Negative Taglish',
                        data: taglishTrendData.Negative || [],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.12)',
                        tension: 0.35,
                    },
                ],
            },
            options: {
                animation,
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                    },
                },
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        }));
    }
}

function syncFormsToUrl() {
    const url = new URL(window.location.href);

    document.querySelectorAll('[data-filter-form]').forEach((form) => {
        Array.from(form.elements).forEach((element) => {
            if (!element.name) {
                return;
            }

            const nextValue = url.searchParams.get(element.name);

            if (element.type === 'checkbox' || element.type === 'radio') {
                element.checked = nextValue === element.value;
                return;
            }

            element.value = nextValue ?? '';
        });
    });

    const settingsForm = document.querySelector('[data-dashboard-settings-form]');

    if (settingsForm) {
        settingsForm.elements.latest_limit.value = url.searchParams.get('latest_limit') || settingsForm.elements.latest_limit.value || '8';
    }
}

async function submitFeedbackForm(form) {
    clearFieldErrors(form);
    setSubmitting(form, true, form.dataset.submitLabel || 'Save');

    try {
        const response = await window.axios.post(form.action, new FormData(form), {
            headers: {
                Accept: 'application/json',
            },
        });

        closeModal('feedback-form-modal');
        showToast(response.data.message || 'Saved successfully.');
        form.reset();
        populateFeedbackForm();
        await refreshSection('feedback', new URL(window.location.href));
    } catch (error) {
        if (error.response?.status === 422) {
            applyFieldErrors(form, error.response.data.errors || {});
        } else {
            showToast(error.response?.data?.message || 'Unable to save feedback right now.', 'error');
        }
    } finally {
        setSubmitting(form, false, form.dataset.submitLabel || 'Save');
    }
}

async function submitDeleteForm(form) {
    const submitButton = form.querySelector('button[type="submit"]');

    submitButton.disabled = true;

    try {
        const response = await window.axios.post(form.action, new FormData(form), {
            headers: {
                Accept: 'application/json',
            },
        });

        closeModal('feedback-delete-modal');
        showToast(response.data.message || 'Deleted successfully.');
        await refreshSection('feedback', new URL(window.location.href));
    } catch (error) {
        showToast(error.response?.data?.message || 'Unable to delete feedback right now.', 'error');
    } finally {
        submitButton.disabled = false;
    }
}

async function submitDashboardSettings(form) {
    const url = new URL(window.location.href);
    const latestLimit = form.elements.latest_limit.value || '8';
    const preferences = {
        animate_charts: form.elements.animate_charts.checked,
        compact_table: form.elements.compact_table.checked,
    };

    url.searchParams.set('latest_limit', latestLimit);
    saveDashboardPreferences(preferences);
    applyDashboardPreferences();
    closeModal('dashboard-settings-modal');
    showToast('Dashboard settings saved.');
    await refreshSection('dashboard', url);
}

document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-modal-open]');

    if (opener) {
        if (opener.matches('[data-feedback-create]')) {
            populateFeedbackForm();
        }

        openModal(opener.dataset.modalOpen);
        return;
    }

    if (event.target.closest('[data-modal-close]')) {
        closeModal();
        return;
    }

    if (event.target.hasAttribute('data-modal-backdrop')) {
        closeModal();
        return;
    }

    const paginationLink = event.target.closest('[data-async-section="feedback"] .pagination a, [data-async-section="feedback"] nav a');

    if (paginationLink) {
        event.preventDefault();
        refreshSection('feedback', new URL(paginationLink.href));
        return;
    }

    const viewLink = event.target.closest('[data-feedback-view]');

    if (viewLink) {
        event.preventDefault();
        populateFeedbackShow(viewLink.dataset);
        openModal('feedback-show-modal');
        return;
    }

    const editLink = event.target.closest('[data-feedback-edit]');

    if (editLink) {
        event.preventDefault();
        populateFeedbackForm(editLink.dataset);
        openModal('feedback-form-modal');
        return;
    }

    const deleteButton = event.target.closest('[data-feedback-delete]');

    if (deleteButton) {
        event.preventDefault();
        populateDeleteModal(deleteButton.dataset);
        openModal('feedback-delete-modal');
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeModal();
    }
});

document.addEventListener('submit', async (event) => {
    const filterForm = event.target.closest('[data-filter-form]');

    if (filterForm) {
        event.preventDefault();

        const isDashboard = new URL(filterForm.action, window.location.origin).pathname.includes('/dashboard');
        await refreshSection(isDashboard ? 'dashboard' : 'feedback', buildUrlFromForm(filterForm));
        closeModal();
        return;
    }

    const feedbackForm = event.target.closest('[data-feedback-form]');

    if (feedbackForm) {
        event.preventDefault();
        await submitFeedbackForm(feedbackForm);
        return;
    }

    const deleteForm = event.target.closest('[data-feedback-delete-form]');

    if (deleteForm) {
        event.preventDefault();
        await submitDeleteForm(deleteForm);
        return;
    }

    const dashboardSettingsForm = event.target.closest('[data-dashboard-settings-form]');

    if (dashboardSettingsForm) {
        event.preventDefault();
        await submitDashboardSettings(dashboardSettingsForm);
    }
});

window.addEventListener('popstate', () => {
    const pathname = window.location.pathname;

    if (pathname.includes('/dashboard')) {
        refreshSection('dashboard', new URL(window.location.href), false);
    }

    if (pathname.includes('/feedback')) {
        refreshSection('feedback', new URL(window.location.href), false);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    syncFormsToUrl();
    applyDashboardPreferences();
    renderDashboardCharts();
});

window.addEventListener('load', () => {
    applyDashboardPreferences();
    renderDashboardCharts();
});
