<script lang="ts">
    import { inertia, useForm } from '@inertiajs/svelte';
    import { config } from '@/lib/config';
    import FormErrors from '@/components/FormErrors.svelte';

    const form = useForm({
        password: '',
        password_confirmation: ''
    });

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post(window.location.href, {
            onError: () => {
                $form.reset();
            }
        });
    }
</script>

<svelte:head>
    <title>Reset Password | {config.APP_NAME}</title>
</svelte:head>

<div class="min-h-dvh w-full px-4 py-8 flex flex-col justify-center items-center">
    <form
        method="post"
        action={window.location.href}
        class="form-group max-w-80"
        onsubmit={submit}
        novalidate
    >
        <div class="form-field">
            <label for="password" class="form-label">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="input max-w-full"
                bind:value={$form.password}
            />
            <FormErrors error={$form.errors.password} />
        </div>
        <div class="form-field">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="input max-w-full"
                bind:value={$form.password_confirmation}
            />
            <FormErrors error={$form.errors.password_confirmation} />
        </div>
        <div class="form-field pt-5">
            <div class="form-control justify-between">
                <button type="submit" class="btn btn-primary w-full">Reset Password</button>
            </div>
        </div>
        <div class="mt-2 text-center">
            <a href="/login" use:inertia class="link link-secondary">Go to login</a>
        </div>
    </form>
</div>
