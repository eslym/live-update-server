<script lang="ts">
    import { inertia, useForm } from '@inertiajs/svelte';
    import FormErrors from '@/components/FormErrors.svelte';
    import { config } from '@/lib/config';

    const form = useForm({
        email: ''
    });

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post('/forgot-password');
    }
</script>

<svelte:head>
    <title>Forgot Password | {config.APP_NAME}</title>
</svelte:head>

<div class="min-h-dvh w-full px-4 py-8 flex flex-col justify-center items-center">
    <form
        method="post"
        action="/forgot-password"
        class="form-group max-w-80"
        onsubmit={submit}
        novalidate
    >
        <p class="text-content3">
            Input your email address below, and we will send you a link to reset your password.
        </p>
        <div class="form-field">
            <label for="email" class="form-label">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="input max-w-full"
                bind:value={$form.email}
            />
            <FormErrors error={$form.errors.email} />
        </div>
        <div class="form-field pt-5">
            <div class="form-control justify-between">
                <button type="submit" class="btn btn-primary w-full">Request Password Reset</button>
            </div>
        </div>
        <div class="mt-2 text-center">
            <a href="/login" use:inertia class="link link-secondary">Back to login</a>
        </div>
    </form>
</div>
