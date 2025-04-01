<script lang="ts" module>
    import AuthLayout from '@/layouts/auth.svelte';
    import illustration from '$assets/illust/auth.svg';

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
    import ForgotPasswordDialog from '$lib/dialogs/ForgotPasswordDialog.svelte';
    import { buttonVariants } from '$lib/components/ui/button';

    const form = useForm(
        {
            email: '',
            password: ''
        },
        'login-form'
    ).remember((data) => {
        data.password = '';
        return data;
    });

    const processing = loading.derived(() => form.processing);
</script>

<svelte:head>
    <title>Login | {config.APP_NAME}</title>
</svelte:head>

<form
    class="my-24 flex flex-col gap-2 max-w-sm w-full"
    novalidate
    action="/login"
    method="post"
    use:form.action
>
    <h1 class="text-2xl font-semibold text-center">{config.APP_NAME}</h1>
    <p class="text-muted-foreground text-center">Please login to your account.</p>
    <div class="flex flex-col gap-1.5 mt-8">
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
    <Button class="mt-12" type="submit" loading={processing.value} disabled={loading.value}
        >Login</Button
    >
    <ForgotPasswordDialog email={form.data.email}>
        {#snippet children(Trigger)}
            <Trigger type="button" class={buttonVariants({ variant: 'ghost' })}
                >Forgot Password</Trigger
            >
        {/snippet}
    </ForgotPasswordDialog>
</form>
