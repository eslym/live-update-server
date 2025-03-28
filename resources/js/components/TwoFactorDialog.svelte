<script lang="ts" module>
    import { mount, unmount } from 'svelte';
    import TwoFactorDialog from './TwoFactorDialog.svelte';
    import axios from 'axios';

    export async function checkTwoFactorSession() {
        const res = await axios.get<{
            should_renew: boolean;
            message: string;
            debug_code: string | null;
        }>('/2fa/check');
        if (!res.data.should_renew) return true;
        return new Promise<boolean>((resolve, reject) => {
            const dialog = mount(TwoFactorDialog, {
                target: document.body,
                props: {
                    message: res.data.message,
                    debug: res.data.debug_code,
                    resolve,
                    reject,
                    cleanup: () => unmount(dialog)
                }
            });
        });
    }
</script>

<script lang="ts">
    import { onMount } from 'svelte';
    import FormErrors from '@/components/FormErrors.svelte';

    let {
        message,
        debug,
        resolve,
        reject,
        cleanup
    }: {
        message: string;
        debug: string | null;
        resolve: (result: boolean) => void;
        reject: (reason?: any) => void;
        cleanup: () => void;
    } = $props();

    const id = $props.id();
    let otp: string = $state(debug ?? '');
    let errors: string = $state('');
    let result: boolean = false;

    let open = $state(false);

    onMount(() => {
        open = true;
    });

    async function submit(ev: SubmitEvent) {
        ev.preventDefault();
        const res = await axios.post<{
            errors: { otp: string[] };
        }>('/2fa/verify?mode=unlock', { otp }, { validateStatus: () => true });
        if (res.status === 200) {
            result = true;
            open = false;
        } else if (res.status === 422) {
            errors = res.data.errors.otp[0];
        } else if (res.status === 401) {
            errors = 'Invalid OTP';
        } else {
            reject(res);
            cleanup();
        }
    }

    function closed(ev: TransitionEvent) {
        if (ev.target !== ev.currentTarget) return;
        if (open) return;
        resolve(result);
        cleanup();
    }
</script>

<input type="checkbox" id="{id}-modal" class="modal-state" bind:checked={open} />
<form class="modal" novalidate onsubmit={submit}>
    <label class="modal-overlay" for="{id}-modal"></label>
    <div class="modal-content flex flex-col gap-5" ontransitionend={closed}>
        <label for="{id}-modal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
            >✕</label
        >
        <h2 class="text-xl">2 Factor Auth</h2>
        <span class="text-content2">{message}</span>
        <div class="form-field">
            <input
                type="text"
                id="otp"
                class="input input-block"
                bind:value={otp}
                class:input-error={errors}
                placeholder="6-digit code or recovery code"
                required
            />
            <FormErrors error={errors} />
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary btn-block">Unlock</button>

            <label for="{id}-modal" class="btn btn-block">Cancel</label>
        </div>
    </div>
</form>
