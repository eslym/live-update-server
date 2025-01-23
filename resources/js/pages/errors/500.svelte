<script lang="ts">
    import { inertia } from '@inertiajs/svelte';
    import { config } from '@/lib/config';
</script>

<svelte:head>
    <title>500 Internal Server Error | {config.APP_NAME}</title>
</svelte:head>

<main aria-labelledby="pageTitle" class="flex items-center justify-center h-screen">
    <div class="p-4 space-y-4">
        <div
            class="flex flex-col items-start space-y-3 sm:flex-row sm:space-y-0 sm:items-center sm:space-x-3"
        >
            <p class="font-semibold text-red-9 text-9xl">500</p>
            <div class="space-y-2">
                <h1 id="pageTitle" class="flex items-center space-x-2">
                    <span class="text-xl font-medium text-content2 sm:text-2xl">
                        Oops! Something went wrong.
                    </span>
                </h1>
                <p class="text-base font-normal text-content3">
                    The server encountered an internal error and was unable to complete your
                    request.
                </p>
                <p class="text-base font-normal text-content3">
                    You may return to
                    <a href="/" use:inertia class="text-blue-600 hover:underline dark:text-blue-500"
                        >home page</a
                    > or try again later.
                </p>
            </div>
        </div>
    </div>
</main>
