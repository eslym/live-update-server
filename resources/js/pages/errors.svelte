<script lang="ts" module>
    const messages = {
        400: {
            title: '400 Bad Request',
            description:
                'The request could not be understood by the server due to malformed syntax. Please check your request.'
        },
        404: {
            title: '404 Not Found',
            description:
                'The page you are looking for does not exist. Please check the URL or return to the homepage.'
        },
        405: {
            title: '405 Method Not Allowed',
            description:
                'The method you are using is not allowed for this resource. Please check the documentation.'
        },
        419: {
            title: '419 Page Expired',
            description: 'Your session has expired. Please refresh the page and try again.'
        },
        429: {
            title: '429 Too Many Requests',
            description:
                'You have made too many requests in a short period. Please try again later.'
        },
        500: {
            title: '500 Internal Server Error',
            description:
                'An unexpected error occurred on the server. Please try again later or contact support.'
        },
        503: {
            title: '503 Service Unavailable',
            description:
                'The server is currently unavailable. Please try again later or contact support.'
        }
    } as Record<number, { title: string; description: string }>;
</script>

<script lang="ts">
    import { config } from '$lib/config';
    import * as Alert from '$lib/components/ui/alert';
    import { TerminalIcon } from '@lucide/svelte';
    import { Button } from '$lib/components/ui/button';

    let { status }: { status: number } = $props();
</script>

<svelte:head>
    <title>{messages[status].title} | {config.APP_NAME}</title>
</svelte:head>

<main class="flex flex-col items-center justify-center h-dvh p-4 gap-4">
    <img src="https://http.cat/{status}" alt="404" class="max-w-lg select-none" />
    <Alert.Root class="max-w-md">
        <TerminalIcon class="size-4" />
        <Alert.Description>{messages[status].description}</Alert.Description>
    </Alert.Root>
    <Button href="/">Back to Home</Button>
</main>
