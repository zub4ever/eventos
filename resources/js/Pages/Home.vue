<script setup>
defineProps({
    appName: {
        type: String,
        default: 'Laravel',
    },
    appDomain: {
        type: String,
        default: 'saas.local',
    },
});
</script>

<template>
    <main class="min-h-screen bg-slate-950 text-slate-100">
        <section class="mx-auto max-w-3xl px-6 py-20">
            <p class="text-sm uppercase tracking-widest text-cyan-300">MVP Etapa 1</p>
            <h1 class="mt-4 text-4xl font-semibold">{{ appName }} integrado com Inertia + Vue 3</h1>
            <p class="mt-6 text-slate-300">
                Ambiente base pronto para o SaaS multi-tenant com domínio principalllll
                <strong>{{ appDomain }}</strong>.
            </p>

            <div class="mt-10 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-700 bg-slate-900 p-5">
                    <h2 class="font-medium">Backend</h2>
                    <p class="mt-2 text-sm text-slate-300">Laravel 12 + Inertia middleware configurado.</p>
                </div>
                <div class="rounded-xl border border-slate-700 bg-slate-900 p-5">
                    <h2 class="font-medium">Frontend</h2>
                    <p class="mt-2 text-sm text-slate-300">Vue 3 Composition API inicializado via Vite.</p>
                </div>
                <div class="rounded-xl border border-slate-700 bg-slate-900 p-5">
                    <h2 class="font-medium">Dados</h2>
                    <p class="mt-2 text-sm text-slate-300">PostgreSQL definido como conexão padrão no ambiente.</p>
                </div>
                <div class="rounded-xl border border-slate-700 bg-slate-900 p-5">
                    <h2 class="font-medium">Fila e sessão</h2>
                    <p class="mt-2 text-sm text-slate-300">Redis configurado para cache, fila e sessão.</p>
                </div>
            </div>
        </section>
    </main>
</template>
