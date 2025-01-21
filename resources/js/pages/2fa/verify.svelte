<script lang="ts" module>
    export {default as layout} from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import {useForm} from "@inertiajs/svelte";
    import {config} from "@/lib/config";
    import FormErrors from "@/components/FormErrors.svelte";

    let {debug_code}: { debug_code: string | null } = $props();

    const form = useForm({
        otp: '',
    });

    $form.otp = debug_code ?? '';

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post('/2fa/verify');
    }
</script>

<svelte:head>
    <title>Verify 2FA | {config.APP_NAME}</title>
</svelte:head>

<div class="w-full px-4 py-8 flex flex-col items-center justify-center">
    <form method="post" class="form-group max-w-96"
          action="/profile"
          onsubmit={submit} novalidate>
        <h1 class="text-2xl font-semibold mb-4">Verify 2FA</h1>
        <p class="text-content2">Enter the 6-digit code from your authenticator app or any recovery code.</p>
        <div class="form-field">
            <input type="text" id="otp" class="input input-block" bind:value={$form.otp}
                   class:input-error={$form.errors.otp} placeholder="6-digit code or recovery code"
                   required/>
            <FormErrors error={$form.errors.otp}/>
        </div>
        <div class="form-field mt-4">
            <button type="submit" class="btn btn-primary w-full">Verify</button>
        </div>
    </form>
</div>
