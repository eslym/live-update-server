<script lang="ts">
    let theme = $derived(localStorage.getItem('theme'));

    $effect.pre(() => {
        switch (theme) {
            case 'dark':
                document.documentElement.dataset.theme = 'dark';
                break;
            case 'light':
                document.documentElement.dataset.theme = 'light';
                break;
            default:
                delete document.documentElement.dataset.theme;
        }
    });
</script>
