<script lang="ts" module>
    import AuthLayout from '@/layouts/auth.svelte';
    import illustration from '$assets/illust/password.svg';

    export const layout = AuthLayout;

    export const layoutProps = new Map([[layout, { illustration }]]);
</script>

<script lang="ts">
    import { config } from '$lib/config';
    import { useForm, page } from '@/inertia';
    import { Button } from '$lib/components/ui/button';
    import { loading } from '$lib/loading.svelte';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import FieldError from '$lib/components/FieldError.svelte';

    const form = useForm(
        {
            password: '',
            password_confirmation: ''
        },
        'reset-form'
    );

    const processing = loading.derived(() => form.processing);
</script>

<svelte:head>
    <title>Reset Password | {config.APP_NAME}</title>
</svelte:head>

<form
    class="my-24 flex flex-col gap-2 max-w-sm w-full"
    novalidate
    action={page.url}
    method="post"
    onsubmit={form.handleSubmit}
>
    <h1 class="text-2xl font-semibold text-center">Reset Password</h1>
    <p class="text-muted-foreground text-center">Set a new password for your account.</p>
    <div class="flex flex-col gap-1.5 mt-8">
        <Label for="password" required>Password</Label>
        <Input
            id="password"
            type="password"
            name="password"
            placeholder="********"
            bind:value={form.data.password}
            disabled={loading.value}
            required
        />
        <FieldError error={form.errors.email} />
    </div>
    <div class="flex flex-col gap-1.5">
        <Label for="password_confirmation" required>Confirm Password</Label>
        <Input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            placeholder="********"
            bind:value={form.data.password_confirmation}
            disabled={loading.value}
            required
        />
        <FieldError error={form.errors.password} />
    </div>
    <Button class="mt-12" type="submit" loading={processing.value} disabled={loading.value}
        >Reset Password</Button
    >
    <Button href="/login" variant="ghost" disabled={loading.value}>Login</Button>
</form>
