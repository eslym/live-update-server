<script lang="ts" module>
    export {default as layout} from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import QRCode from 'qrcode';
    import {useForm} from "@inertiajs/svelte";
    import FormErrors from "@/components/FormErrors.svelte";
    import {config} from "@/lib/config";

    let {qr, secret, debug_code}: { qr: string, secret: string, debug_code: string | null } = $props();

    const form = useForm({
        otp: '',
    });

    $form.otp = debug_code ?? '';

    function renderQR(data: string) {
        return new Promise((resolve, reject) => {
            QRCode.toString(data, {
                type: 'svg',
                color: {
                    dark: '#000000',
                    light: '#ffffff',
                }
            }, (err, svg) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(
                        svg.replace('stroke="#000000"', 'class="stroke-purple-11"')
                            .replace('fill="#ffffff"', 'class="fill-gray-3"')
                    );
                }
            });
        });
    }

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post('/2fa/setup');
    }
</script>

<svelte:head>
    <title>Setup 2FA | {config.APP_NAME}</title>
</svelte:head>

<div class="flex flex-col xl:flex-row gap-8 items-center max-w-4xl mx-auto w-full mt-8">
    <div class="rounded-xl w-full max-w-xs aspect-square overflow-hidden shadow-lg min-h-max">
        {#await renderQR(qr) then svg}
            {@html svg}
        {:catch error}
            <p>{error.message}</p>
        {/await}
    </div>
    <div class="flex-grow h-full">
        <p class="text-2xl font-semibold my-4">Scan this QR code with your authenticator app</p>
        <p class="text-content2">If you can't scan the QR code, you can manually enter the code below.</p>
        <p class="text-content2">You can also use the following code to set up your authenticator app:</p>
        <p class="mt-6 text-center">
            <input type="text" class="input input-xl input-solid font-mono text-center" value={secret} readonly/>
        </p>
        <p class="text-content2 mt-8">
            Enter the 6-digit code from your authenticator app to complete the setup.
        </p>
        <form class="mt-4 flex flex-row w-full gap-2" action="/2fa/setup" method="post" novalidate onsubmit={submit}>
            <input type="text" name="otp"
                   class="input flex-grow font-mono max-w-full"
                   placeholder="6 digit code"
                   bind:value={$form.otp}
                   class:input-error={$form.errors.otp}
                   required/>
            <button class="btn btn-primary">Submit</button>
        </form>
        <FormErrors error={$form.errors.otp}/>
    </div>
</div>

