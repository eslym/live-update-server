<script lang="ts">
    import {useForm} from '@inertiajs/svelte';
    import FormErrors from "@/components/FormErrors.svelte";

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post('/setup');
    }
</script>

<svelte:head>
    <title>Setup</title>
</svelte:head>

<div class="min-h-dvh w-full px-4 py-8 flex flex-col items-center justify-center">
    <form method="post" class="form-group max-w-80" onsubmit={submit} novalidate>
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
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="input max-w-full"
                   bind:value={$form.password}
                   class:input-error={$form.errors.password}
            />
            <FormErrors error={$form.errors.password}/>
        </div>
        <div class="form-field">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="input max-w-full"
                   bind:value={$form.password_confirmation}
                   class:input-error={$form.errors.password_confirmation}
            />
            <FormErrors error={$form.errors.password_confirmation}/>
        </div>
        <div class="form-field pt-5">
            <div class="form-control justify-between">
                <button type="submit" class="btn btn-primary w-full" disabled={$form.processing}>
                    Submit
                </button>
            </div>
        </div>
    </form>
</div>
