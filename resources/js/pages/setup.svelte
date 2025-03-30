<script lang="ts" module>
    import AuthLayout from '@/layouts/auth.svelte';
    import illustration from '$assets/illust/settings.svg';

    export const layout = AuthLayout;

    export const layoutProps = new Map([[layout, { illustration }]]);
</script>

<script lang="ts">
    import { config } from '$lib/config';
    import { useForm } from '@/inertia';
    import { Button } from '$lib/components/ui/button';
    import { loading } from '$lib/loading.svelte';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import FieldError from '$lib/components/FieldError.svelte';

    const form = useForm(
        {
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        },
        'setup-form'
    );

    const processing = loading.derived(() => form.processing);
</script>

<svelte:head>
    <title>Setup | {config.APP_NAME}</title>
</svelte:head>

<form
    class="my-24 flex flex-col gap-2 max-w-sm w-full"
    novalidate
    action="/setup"
    method="post"
    onsubmit={form.handleSubmit}
>
    <h2 class="text-2xl font-semibold text-center">Setup</h2>
    <p class="text-muted-foreground text-center">
        Please setup the superadmin account to get started.
    </p>
    <div class="flex flex-col gap-1.5 mt-8">
        <Label for="name" required>Name</Label>
        <Input
            id="name"
            type="text"
            name="name"
            placeholder="John Doe"
            bind:value={form.data.name}
            disabled={loading.value}
            required
        />
        <FieldError error={form.errors.name} />
    </div>
    <div class="flex flex-col gap-1.5">
        <Label for="email" required>Email</Label>
        <Input
            id="email"
            type="text"
            name="email"
            placeholder="john.doe@example.com"
            bind:value={form.data.email}
            disabled={loading.value}
            required
        />
        <FieldError error={form.errors.email} />
    </div>
    <div class="flex flex-col gap-1.5">
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
        <FieldError error={form.errors.password} />
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
        <FieldError error={form.errors.password_confirmation} />
    </div>
    <Button class="mt-12" type="submit" loading={processing.value} disabled={loading.value}
        >Submit</Button
    >
</form>
