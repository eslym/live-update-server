<script lang="ts" module>
    export {default as layout} from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import {useForm} from "@inertiajs/svelte";
    import FormErrors from "@/components/FormErrors.svelte";

    let {user, APP_NAME}: { user: { name: string, email: string }, APP_NAME: string } = $props();

    const form = useForm({
        name: user.name,
        email: user.email,
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    $form.transform((data) => {
        const d = {...data};
        if (d.name === user.name) d.name = '';
        if (d.email === user.email) d.email = '';
        return d;
    });

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post('/profile', {
            onSuccess: () => {
                $form.defaults({
                    name: user.name,
                    email: user.email,
                    current_password: '',
                    password: '',
                    password_confirmation: '',
                });
            },
            onFinish: () => $form.reset('current_password', 'password', 'password_confirmation'),
        });
    }
</script>

<svelte:head>
    <title>Profile | {APP_NAME}</title>
</svelte:head>

<div class="w-full px-4 py-8 flex flex-col items-center justify-center">
    <form method="post" class="form-group max-w-96"
          action="/profile"
          onsubmit={submit} novalidate>
        <div class="form-field">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="input max-w-full"
                   bind:value={$form.name}
                   class:input-error={$form.errors.name}
            />
            <FormErrors error={$form.errors.name}/>
        </div>
        <div class="form-field">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="input max-w-full"
                   bind:value={$form.email}
                   class:input-error={$form.errors.email}
            />
            <FormErrors error={$form.errors.email}/>
        </div>
        <div class="form-field">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" id="current_password" name="current_password" class="input max-w-full"
                   bind:value={$form.current_password}
                   class:input-error={$form.errors.current_password}
            />
            <FormErrors error={$form.errors.current_password}/>
        </div>
        <div class="form-field">
            <label for="password" class="form-label">New Password</label>
            <input type="password" id="password" name="password" class="input max-w-full"
                   bind:value={$form.password}
                   class:input-error={$form.errors.password}
                   placeholder="Leave empty to keep the same password"
            />
            <FormErrors error={$form.errors.password}/>
        </div>
        <div class="form-field">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="input max-w-full"
                   bind:value={$form.password_confirmation}
                   class:input-error={$form.errors.password_confirmation}
            />
            <FormErrors error={$form.errors.password_confirmation}/>
        </div>
        <div class="form-field pt-5">
            <div class="form-control justify-between">
                <button type="submit" class="btn btn-primary w-full" disabled={$form.processing}>
                    Save
                </button>
            </div>
        </div>
    </form>
</div>
