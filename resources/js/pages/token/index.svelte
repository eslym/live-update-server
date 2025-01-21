<script lang="ts" module>
    export {default as layout} from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import {fade} from "svelte/transition";
    import {alert} from '@/components/Alert.svelte';
    import {Delete01Icon, InformationCircleIcon} from "hugeicons-svelte";
    import moment from "moment";
    import PaginateLinks from "@/components/PaginateLinks.svelte";
    import {router, useForm} from "@inertiajs/svelte";
    import FormErrors from "@/components/FormErrors.svelte";
    import {config} from "@/lib/config";

    let {tokens, recentCreated}: {
        tokens: Pagination<{
            id: number,
            name: string,
            expires_at: string | null,
            created_at: string,
            last_used_at: string | null,
        }>, recentCreated: string | null
    } = $props();

    let form = useForm({
        name: '',
        expires_at: '',
    });

    let createModal = $state(false);

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post('/tokens', {
            onSuccess: () => {
                createModal = false;
            },
        });
    }
</script>

<svelte:head>
    <title>Tokens | {config.APP_NAME}</title>
</svelte:head>

<div class="w-full flex flex-row">
    <h1 class="text-3xl font-semibold">Tokens</h1>
    <label for="modal-create" class="ml-auto btn btn-primary">Create Token</label>
</div>

{#if recentCreated}
    <div class="alert alert-info max-w-2xl mx-auto mt-4" transition:fade={{duration: 250}}>
        <InformationCircleIcon size={48} class="text-primary"/>
        <div class="flex flex-col w-0 flex-grow">
            <span>New Token Created</span>
            <span class="text-content2">
                Your newly created token is <code
                class="font-mono py-1 px-2 bg-gray-4/80 rounded select-all">{recentCreated}</code>,
                please keep it safe as it will not be shown again.
            </span>
        </div>
    </div>
{/if}

<table class="table table-hover mt-4">
    <thead>
    <tr>
        <th>Name</th>
        <th class="w-0 min-w-max">Created At</th>
        <th class="w-0 min-w-max">Expires At</th>
        <th class="w-0 min-w-max">Last Used At</th>
        <th class="w-0 min-w-max"></th>
    </tr>
    </thead>
    <tbody>
    {#each tokens.data as token}
        <tr>
            <td>{token.name}</td>
            <td>{moment(token.created_at).fromNow()}</td>
            <td>{token.expires_at ? moment(token.expires_at).calendar() : 'Never'}</td>
            <td>{token.last_used_at ? moment(token.last_used_at).fromNow() : 'Never'}</td>
            <td class="text-right">
                <div class="btn-group min-w-max">
                    <button onclick={async ()=>{
                        const res = await alert({
                            title: 'Revoke Token',
                            content: 'Are you sure you want to revoke this token?',
                            actions: {
                                primary: 'Revoke',
                                secondary: 'Cancel',
                            }
                        });
                        if(res) {
                            router.delete(`/tokens/${token.id}`);
                        }
                    }}
                            title="Delete"
                            class="btn btn-sm">
                        <Delete01Icon size={16}/>
                    </button>
                </div>
            </td>
        </tr>
    {:else}
        <tr>
            <td colspan="5" class="!text-center !text-content3 !text-lg">
                No Tokens
            </td>
        </tr>
    {/each}
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5">
            <div class="flex justify-end mt-2">
                <PaginateLinks links={tokens.links}/>
            </div>
        </td>
    </tr>
    </tfoot>
</table>

<input type="checkbox" class="modal-state" id="modal-create" bind:checked={createModal}/>
<div class="modal">
    <label class="modal-overlay" for="modal-create"></label>
    <form onsubmit={submit} method="post"
          class="modal-content flex flex-col gap-5 w-[24rem] max-w-full"
          ontransitionend={(ev)=>{
              if(ev.target !== ev.currentTarget) return;
              if(!createModal) {
                  $form.reset();
                  $form.clearErrors();
              }
          }}
          action="/tokens" novalidate>
        <label for="modal-create" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label>
        <h2 class="text-xl">Create Token</h2>
        <div class="form-group">
            <div class="form-field">
                <label for="name" class="form-label">Name <span class="text-error">*</span></label>
                <input type="text" id="name" name="name" class="input max-w-full"
                       bind:value={$form.name}
                       class:input-error={$form.errors.name}
                />
                <FormErrors error={$form.errors.name}/>
            </div>
            <div class="form-field">
                <label for="expires_at" class="form-label">Expires At</label>
                <input type="date" id="expires_at" name="expires_at" class="input max-w-full"
                       bind:value={$form.expires_at}
                       class:input-error={$form.errors.expires_at}
                />
                <FormErrors error={$form.errors.expires_at}/>
            </div>
        </div>
        <div class="flex gap-3">
            <button class="btn btn-primary btn-block">Create</button>
            <label for="modal-create" class="btn btn-block">Cancel</label>
        </div>
    </form>
</div>
