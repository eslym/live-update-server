<script lang="ts">
    import {inertia} from "@inertiajs/svelte";
    import {config} from "@/lib/config";
</script>

<svelte:head>
    <title>404 Not Found | { config.APP_NAME }</title>
</svelte:head>

<main
    aria-labelledby="pageTitle"
    class="flex items-center justify-center h-screen"
>
    <div class="p-4 space-y-4">
        <div class="flex flex-col items-start space-y-3 sm:flex-row sm:space-y-0 sm:items-center sm:space-x-3">
            <p class="font-semibold text-red-9 text-9xl">404</p>
            <div class="space-y-2">
                <h1 id="pageTitle" class="flex items-center space-x-2">
                    <span class="text-xl font-medium text-content2 sm:text-2xl">
                        Oops! Page not found.
                    </span>
                </h1>
                <p class="text-base font-normal text-content3">
                    The page you ara looking for was not found.
                </p>
                <p class="text-base font-normal text-content3">
                    You may return to
                    <a href="/" use:inertia
                       class="text-blue-600 hover:underline dark:text-blue-500">home page</a>.
                </p>
            </div>
        </div>
    </div>
</main>
