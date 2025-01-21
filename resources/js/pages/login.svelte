<script lang="ts">
    import {useForm} from '@inertiajs/svelte';
    import FormErrors from "@/components/FormErrors.svelte";
    import {config} from "@/lib/config";

    const form = useForm('login-form', {
        email: '',
        password: '',
    });

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post('/login', {
            onError: () => {
                $form.reset('password');
            },
        });
    }
</script>

<svelte:head>
    <title>Login | {config.APP_NAME}</title>
</svelte:head>

<div class="min-h-dvh w-full px-4 py-8 flex flex-col items-center justify-center">
    <form method="post" action="/login" class="form-group max-w-80" onsubmit={submit} novalidate>
        <div class="form-field">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="input max-w-full"
                   bind:value={$form.email}
                   class:input-error={$form.errors.email}
            />
            <FormErrors error={$form.errors.email}/>
        </div>
        <div class="form-field">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="input max-w-full"
                   bind:value={$form.password}
                   class:input-error={$form.errors.password}
            />
            <FormErrors error={$form.errors.password}/>
        </div>
        <div class="form-field pt-5">
            <div class="form-control justify-between">
                <button type="submit" class="btn btn-primary w-full" disabled={$form.processing}>Login</button>
            </div>
        </div>
    </form>
</div>
