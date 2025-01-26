<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { useForm, inertia, useRemember } from '@inertiajs/svelte';
    import FormErrors from '@/components/FormErrors.svelte';
    import { config } from '@/lib/config';
    import { onMount } from 'svelte';
    import { Copy01Icon } from '@eslym/hugeicons-svelte';

    let {
        user,
        recovery_codes
    }: {
        user: { name: string; email: string; '2fa_enabled': boolean };
        recovery_codes: string[] | null;
    } = $props();

    const form = useForm({
        name: user.name,
        email: user.email,
        current_password: '',
        password: '',
        password_confirmation: ''
    });

    const disable2faForm = useForm({
        password: ''
    });

    const codeShown = useRemember(false, 'code-shown');

    let recoveryCodeModal = $state(false);
    let disable2faModal = $state(false);

    $form.transform((data) => {
        const d = { ...data };
        if (d.name === user.name) d.name = '';
        if (d.email === user.email) d.email = '';
        return d;
    });

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post('/profile', {
            preserveState: true,
            onSuccess: () => {
                $form.defaults({
                    name: user.name,
                    email: user.email,
                    current_password: '',
                    password: '',
                    password_confirmation: ''
                });
            },
            onFinish: () => $form.reset('current_password', 'password', 'password_confirmation')
        });
    }

    function disable2fa(ev: SubmitEvent) {
        ev.preventDefault();
        $disable2faForm.post('/2fa/disable', {
            preserveState: true,
            onSuccess: () => {
                disable2faModal = false;
            },
            onFinish: () => $disable2faForm.reset('password')
        });
    }

    onMount(() => {
        if (recovery_codes && !$codeShown) {
            recoveryCodeModal = true;
            $codeShown = true;
        }
    });
</script>

<svelte:head>
    <title>Profile | {config.APP_NAME}</title>
</svelte:head>

<div class="w-full px-4 py-8 flex flex-col items-center justify-center">
    <form method="post" class="form-group max-w-96" action="/profile" onsubmit={submit} novalidate>
        <div class="form-field">
            <label for="name" class="form-label">Name</label>
            <input
                type="text"
                id="name"
                name="name"
                class="input max-w-full"
                bind:value={$form.name}
                class:input-error={$form.errors.name}
            />
            <FormErrors error={$form.errors.name} />
        </div>
        <div class="form-field">
            <label for="email" class="form-label">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="input max-w-full"
                bind:value={$form.email}
                class:input-error={$form.errors.email}
            />
            <FormErrors error={$form.errors.email} />
        </div>
        <div class="form-field">
            <label for="current_password" class="form-label">Current Password</label>
            <input
                type="password"
                id="current_password"
                name="current_password"
                class="input max-w-full"
                bind:value={$form.current_password}
                class:input-error={$form.errors.current_password}
            />
            <FormErrors error={$form.errors.current_password} />
        </div>
        <div class="form-field">
            <label for="password" class="form-label">New Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="input max-w-full"
                bind:value={$form.password}
                class:input-error={$form.errors.password}
                placeholder="Leave empty to keep the same password"
            />
            <FormErrors error={$form.errors.password} />
        </div>
        <div class="form-field">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="input max-w-full"
                bind:value={$form.password_confirmation}
                class:input-error={$form.errors.password_confirmation}
            />
            <FormErrors error={$form.errors.password_confirmation} />
        </div>
        <div class="form-field mt-4">
            <div class="form-control justify-between">
                <button type="submit" class="btn btn-primary w-full" disabled={$form.processing}>
                    Save
                </button>
            </div>
        </div>
        <div class="form-field mt-8">
            <div class="form-control justify-between">
                {#if user['2fa_enabled']}
                    <label for="modal-disable-2fa" class="btn btn-solid-error w-full"
                        >Disable Two Factor Authentication</label
                    >
                {:else}
                    <a use:inertia href="/2fa/setup" class="btn btn-solid-secondary w-full">
                        Enable Two Factor Authentication
                    </a>
                {/if}
            </div>
        </div>
    </form>
</div>

<input type="checkbox" class="modal-state" id="modal-disable-2fa" bind:checked={disable2faModal} />
<div class="modal">
    <label class="modal-overlay" for="modal-disable-2fa"></label>
    <form
        action="/2fa/disable"
        method="post"
        class="modal-content flex flex-col gap-5 w-[24rem] max-w-full"
        ontransitionend={(ev) => {
            if (ev.currentTarget !== ev.target) return;
            if (disable2faModal) return;
            $disable2faForm.reset('password');
        }}
        onsubmit={disable2fa}
        novalidate
    >
        <label
            for="modal-disable-2fa"
            class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label
        >
        <h2 class="text-xl">Disable Two Factor Authentication</h2>
        <p class="text-content2">
            You need to enter your password to disable two-factor authentication.
        </p>
        <div class="form-field">
            <label for="disable-2fa-password" class="form-label">Password</label>
            <input
                type="password"
                id="disable-2fa-password"
                name="password"
                class="input max-w-full"
                bind:value={$disable2faForm.password}
                class:input-error={$disable2faForm.errors.password}
            />
            <FormErrors error={$disable2faForm.errors.password} />
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-error btn-block">Disable 2FA</button>
            <label for="modal-disable-2fa" class="btn btn-block">Cancel</label>
        </div>
    </form>
</div>

{#if recovery_codes}
    <input
        type="checkbox"
        class="modal-state"
        id="modal-recovery-codes"
        bind:checked={recoveryCodeModal}
    />
    <div class="modal">
        <label class="modal-overlay" for="modal-recovery-codes"></label>
        <div class="modal-content flex flex-col gap-5 w-[28rem] max-w-full">
            <label
                for="modal-recovery-codes"
                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label
            >
            <h2 class="text-xl">Two Factor Auth Recovery Codes</h2>
            <p class="text-content2">
                Save these recovery codes in a secure place. They can be used to recover access to
                your account if you lose your two-factor authentication device. Each code can only
                be used once.
            </p>
            <div class="flex flex-row flex-wrap justify-center gap-2">
                {#each recovery_codes as code}
                    <code class="text-sm font-mono block bg-gray-4 px-2 py-1 rounded">{code}</code>
                {/each}
                <button
                    type="button"
                    class="btn btn-sm btn-ghost"
                    onclick={() => navigator.clipboard.writeText(recovery_codes.join('\n'))}
                >
                    <Copy01Icon size={16} class="inline-block mr-2" />
                    Copy Codes
                </button>
            </div>
            <div class="flex gap-3">
                <label for="modal-recovery-codes" class="btn btn-block">Close</label>
            </div>
        </div>
    </div>
{/if}
