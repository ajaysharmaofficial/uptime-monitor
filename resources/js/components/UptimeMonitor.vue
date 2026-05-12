<script setup>
import { computed, onMounted, ref } from 'vue';

const clients = ref([]);
const selectedClientId = ref('');
const pendingWebsite = ref(null);
const loading = ref(true);
const error = ref('');

const selectedClient = computed(() => clients.value.find((client) => String(client.id) === selectedClientId.value));
const websites = computed(() => selectedClient.value?.websites ?? []);

onMounted(async () => {
    try {
        const response = await fetch('/api/clients', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (! response.ok) {
            throw new Error('Unable to load clients.');
        }

        const data = await response.json();
        clients.value = data.clients;
        selectedClientId.value = clients.value.length ? String(clients.value[0].id) : '';
    } catch {
        error.value = 'Unable to load clients right now.';
    } finally {
        loading.value = false;
    }
});

function askToVisit(website) {
    pendingWebsite.value = website;
}

function continueToWebsite() {
    window.open(pendingWebsite.value.url, '_blank', 'noopener,noreferrer');
    pendingWebsite.value = null;
}
</script>

<template>
    <main class="min-h-screen bg-neutral-950 text-neutral-100">
        <section class="mx-auto flex min-h-screen w-full max-w-4xl flex-col justify-center px-6 py-12">
            <div class="mb-8">
                <p class="text-sm font-medium uppercase tracking-wide text-cyan-300">Uptime Monitor</p>
                <h1 class="mt-3 text-4xl font-semibold text-white">Client websites</h1>
            </div>

            <div class="rounded-lg border border-neutral-800 bg-neutral-900 p-6 shadow-2xl shadow-black/20">
                <label for="client" class="block text-sm font-medium text-neutral-300">Client email</label>
                <select
                    id="client"
                    v-model="selectedClientId"
                    class="mt-2 w-full rounded-md border border-neutral-700 bg-neutral-950 px-3 py-2 text-white outline-none transition focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/25"
                    :disabled="loading || clients.length === 0"
                >
                    <option value="" disabled>Select a client</option>
                    <option v-for="client in clients" :key="client.id" :value="String(client.id)">
                        {{ client.email }}
                    </option>
                </select>

                <p v-if="loading" class="mt-5 text-sm text-neutral-400">Loading clients...</p>
                <p v-else-if="error" class="mt-5 text-sm text-red-300">{{ error }}</p>
                <p v-else-if="clients.length === 0" class="mt-5 text-sm text-neutral-400">No clients have been added yet.</p>

                <ul v-else class="mt-6 list-disc space-y-3 pl-6">
                    <li v-for="website in websites" :key="website.id">
                        <button
                            type="button"
                            class="text-left text-cyan-300 underline decoration-cyan-300/40 underline-offset-4 transition hover:text-cyan-100"
                            @click="askToVisit(website)"
                        >
                            {{ website.url }}
                        </button>
                    </li>
                    <li v-if="websites.length === 0" class="text-neutral-400">No websites are assigned to this client.</li>
                </ul>
            </div>
        </section>

        <div
            v-if="pendingWebsite"
            class="fixed inset-0 z-50 grid place-items-center bg-black/70 px-4"
            role="dialog"
            aria-modal="true"
        >
            <div class="w-full max-w-md rounded-lg border border-neutral-700 bg-neutral-900 p-6 shadow-2xl">
                <p class="text-lg font-medium text-white">
                    You are about to visit {{ pendingWebsite.url }}. Do you want to continue?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-md border border-neutral-700 px-4 py-2 text-neutral-200 transition hover:bg-neutral-800"
                        @click="pendingWebsite = null"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-cyan-300 px-4 py-2 font-medium text-neutral-950 transition hover:bg-cyan-200"
                        @click="continueToWebsite"
                    >
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </main>
</template>
