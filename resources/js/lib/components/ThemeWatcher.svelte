<script lang="ts">
    import { local } from '$lib/storage';
    import { dark } from '$lib/config';

    let theme: 'light' | 'dark' | 'system' = $derived(
        local.theme === 'light' || local.theme === 'dark'
            ? local.theme
            : dark.current
              ? 'dark'
              : 'light'
    );

    $effect.pre(() => {
        if (theme === 'dark') document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
    });
</script>
