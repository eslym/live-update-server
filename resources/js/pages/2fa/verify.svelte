<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { useForm } from '@/inertia';
    import { loading } from '$lib/loading.svelte';
    import * as Tabs from '$lib/components/ui/tabs';
    import * as InputOTP from '$lib/components/ui/input-otp';
    import * as Card from '$lib/components/ui/card';
    import { REGEXP_ONLY_DIGITS } from 'bits-ui';
    import { Button } from '$lib/components/ui/button';
    import FieldError from '$lib/components/FieldError.svelte';
    import DashboardMain from '$lib/components/DashboardMain.svelte';

    type _keep = [typeof Tabs, typeof InputOTP, typeof Card];

    let { debug_code }: { debug_code: string } = $props();

    const form = useForm({
        type: 'otp' as 'otp' | 'recovery',
        otp: debug_code,
        code: ''
    });
    const processing = loading.derived(() => form.processing);
</script>

<DashboardMain
    title="Verify 2FA"
    breadcrumbs={[{ label: '2FA' }, { label: 'Verify', href: '/2fa/verify' }]}
>
    <div class="w-full min-h-full flex items-center justify-center px-4 py-2">
        <Tabs.Root bind:value={form.data.type} class="contents">
            <Card.Root class="max-w-md w-full">
                <form class="contents" method="post" action="/2fa/verify" use:form.action>
                    <Card.Header>
                        <Card.Title class="grid grid-cols-[1fr,auto]">
                            <span class="flex flex-row items-center text-lg">Verify With</span>
                            <Tabs.List class="grid w-full grid-cols-2">
                                <Tabs.Trigger value="otp" disabled={loading.value}>OTP</Tabs.Trigger
                                >
                                <Tabs.Trigger value="recovery" disabled={loading.value}
                                    >Recovery Code
                                </Tabs.Trigger>
                            </Tabs.List>
                        </Card.Title>
                    </Card.Header>
                    <Card.Content>
                        <Tabs.Content value="otp">
                            <InputOTP.Root
                                maxlength={6}
                                pattern={REGEXP_ONLY_DIGITS}
                                bind:value={form.data.otp}
                                disabled={loading.value}
                                class="justify-center my-12"
                            >
                                {#snippet children({ cells })}
                                    {#each cells as cell (cell)}
                                        <InputOTP.Group>
                                            <InputOTP.Slot {cell} />
                                        </InputOTP.Group>
                                    {/each}
                                {/snippet}
                            </InputOTP.Root>
                        </Tabs.Content>
                        <Tabs.Content value="recovery">
                            <InputOTP.Root
                                maxlength={9}
                                pattern={REGEXP_ONLY_DIGITS}
                                bind:value={form.data.code}
                                disabled={loading.value}
                                class="justify-center my-12"
                            >
                                {#snippet children({ cells })}
                                    {#each { length: 3 }, i}
                                        {@const n = i * 3}
                                        <InputOTP.Group>
                                            {#each cells.slice(n, n + 3) as cell (cell)}
                                                <InputOTP.Slot {cell} />
                                            {/each}
                                        </InputOTP.Group>
                                    {/each}
                                {/snippet}
                            </InputOTP.Root>
                        </Tabs.Content>
                    </Card.Content>
                    <Card.Footer class="flex flex-row items-center justify-between">
                        <FieldError error={form.errors.otp ?? form.errors.code} class="p-0" />
                        <Button
                            type="submit"
                            class="px-12 ml-auto"
                            loading={processing.value}
                            disabled={loading.or(
                                form.data.type === 'otp'
                                    ? form.data.otp.length !== 6
                                    : form.data.code.length !== 9
                            )}
                            >Verify
                        </Button>
                    </Card.Footer>
                </form>
            </Card.Root>
        </Tabs.Root>
    </div>
</DashboardMain>
