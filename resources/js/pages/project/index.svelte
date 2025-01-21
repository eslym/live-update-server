<script lang="ts" module>
    export {default as layout} from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import {useForm, inertia, router} from "@inertiajs/svelte";
    import FormErrors from "@/components/FormErrors.svelte";
    import PaginateLinks from "@/components/PaginateLinks.svelte";
    import {alert} from '@/components/Alert.svelte';
    import moment from "moment";
    import {EyeIcon, Delete01Icon} from "hugeicons-svelte";
    import {config} from "@/lib/config";

    let {projects}: {
        projects: Pagination<{
            id: number,
            nanoid: string,
            name: string,
            created_at: string,
            versions: [{
                name: string,
            } | undefined]
        }>
    } = $props();

    let editModal = $state(false);

    let form = useForm({
        name: '',
        description: '',
        private_key: '',
    });

    function submit(ev: SubmitEvent) {
        ev.preventDefault();
        $form.post('/projects', {
            onSuccess: () => {
                editModal = false;
            },
        });
    }
</script>

<svelte:head>
    <title>Projects | {config.APP_NAME}</title>
</svelte:head>

<div class="w-full flex flex-row">
    <h1 class="text-3xl font-semibold">Projects</h1>
    <label for="modal-create" class="ml-auto btn btn-primary">Create Project</label>
</div>

<table class="table table-hover mt-4">
    <thead>
    <tr>
        <th>Name</th>
        <th>Created At</th>
        <th>Latest Version</th>
        <th class="w-0 min-w-max"></th>
    </tr>
    </thead>
    <tbody>
    {#each projects.data as project(project.id)}
        <tr>
            <td>{project.name}</td>
            <td class="w-0 min-w-max">{moment(project.created_at).fromNow()}</td>
            <td class="w-0 min-w-max">{project.versions[0]?.name ?? 'No Versions'}</td>
            <td class="w-0 !min-w-max">
                <div class="btn-group min-w-max">
                    <a use:inertia href={`/projects/${project.nanoid}`} title="View" class="btn btn-sm">
                        <EyeIcon size={16}/>
                    </a>
                    <button onclick={async ()=>{
                        const res = await alert({
                            title: 'Delete Project',
                            content: 'Are you sure you want to delete this project?',
                            actions: {
                                primary: 'Delete',
                                secondary: 'Cancel',
                            }
                        });
                        if(res) {
                            router.delete(`/projects/${project.nanoid}`);
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
            <td colspan="4" class="!text-center !text-content3 !text-lg">
                No Projects
            </td>
        </tr>
    {/each}
    </tbody>
    <tfoot>
    <tr>
        <td colspan="4">
            <div class="flex justify-end mt-2">
                <PaginateLinks links={projects.links}/>
            </div>
        </td>
    </tr>
    </tfoot>
</table>

<input class="modal-state" id="modal-create" type="checkbox" bind:checked={editModal}/>
<div class="modal">
    <label class="modal-overlay" for="modal-create"></label>
    <form onsubmit={submit} method="post"
          class="modal-content flex flex-col gap-5 w-[24rem] max-w-full"
          ontransitionend={(ev)=>{
              if(ev.target !== ev.currentTarget) return;
              if(!editModal) {
                  $form.reset();
                  $form.clearErrors();
              }
          }}
          action="/projects" novalidate>
        <label for="modal-create" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label>
        <h2 class="text-xl">Create Project</h2>
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
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description"
                          class="textarea max-w-full resize-none"
                          rows="5"
                          placeholder="Project description"
                          bind:value={$form.description}
                          class:textarea-error={$form.errors.description}
                ></textarea>
                <FormErrors error={$form.errors.description}/>
            </div>
            <div class="form-field">
                <label for="private_key" class="form-label">Private Key</label>
                <textarea id="private_key" name="private_key"
                          class="textarea max-w-full resize-none font-mono"
                          rows="5"
                          placeholder="Leave empty to generate a new key"
                          bind:value={$form.private_key}
                          class:textarea-error={$form.errors.private_key}
                ></textarea>
                <FormErrors error={$form.errors.private_key}/>
            </div>
        </div>
        <div class="flex gap-3">
            <button class="btn btn-primary btn-block">Create</button>
            <label for="modal-create" class="btn btn-block">Cancel</label>
        </div>
    </form>
</div>
