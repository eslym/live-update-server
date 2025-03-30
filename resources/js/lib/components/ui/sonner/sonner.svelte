<script lang="ts">
    import { Toaster as Sonner, type ToasterProps as SonnerProps } from 'svelte-sonner';
    import { local } from '$lib/storage';
    import { dark } from '$lib/config';

    let { ...restProps }: SonnerProps = $props();

    let theme: 'light' | 'dark' | 'system' = $derived(
        local.theme === 'light' || local.theme === 'dark'
            ? local.theme
            : dark.current
              ? 'dark'
              : 'light'
    );
</script>

<Sonner
    {theme}
    class="toaster group"
    toastOptions={{
        classes: {
            toast: 'group toast group-[.toaster]:bg-background group-[.toaster]:text-foreground group-[.toaster]:border-border group-[.toaster]:shadow-lg',
            description: 'group-[.toast]:text-muted-foreground',
            actionButton: 'group-[.toast]:bg-primary group-[.toast]:text-primary-foreground',
            cancelButton: 'group-[.toast]:bg-muted group-[.toast]:text-muted-foreground'
        }
    }}
    {...restProps}
/>
