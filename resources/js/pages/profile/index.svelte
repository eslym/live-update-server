<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { onMount } from 'svelte';
    import { router } from '@/inertia';

    let { user, recovery_codes }: { user: User; recovery_codes: string[] | null } = $props();

    let display_codes = recovery_codes;
    let codes_dialog = $state(false);

    onMount(() => {
        if (recovery_codes) {
            codes_dialog = true;
            router.replace({
                props(props) {
                    delete props.recovery_codes;
                    return props;
                }
            });
        }
    });
</script>
