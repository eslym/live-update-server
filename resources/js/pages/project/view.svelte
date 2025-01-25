<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { router, useForm } from '@inertiajs/svelte';
    import moment from 'moment';
    import FormErrors from '@/components/FormErrors.svelte';
    import { Upload } from 'tus-js-client';
    import {
        Delete01Icon,
        Download01Icon,
        MultiplicationSignIcon,
        PencilEdit01Icon
    } from 'hugeicons-svelte';
    import PaginateLinks from '@/components/PaginateLinks.svelte';
    import { promptAlert } from '@/components/Alert.svelte';
    import { config } from '@/lib/config';
    import CodeSnippet from '@/components/CodeSnippet.svelte';
    import { fragment } from '@/lib/hash';

    let fileInput: HTMLInputElement = $state(null as any);
    let uploadProgress: number | null = $state(null);
    let tusUpload: Upload | null = $state(null);

    let {
        project,
        versions,
        latestRequirements
    }: {
        project: {
            id: number;
            nanoid: string;
            name: string;
            description: string | null;
            public_key: string;
            created_at: string;
        };
        versions: Pagination<{
            id: string;
            nanoid: string;
            name: string;
            created_at: string;
            android_requirements: string | null;
            ios_requirements: string | null;
        }>;
        latestRequirements: {
            android_requirements: string;
            ios_requirements: string;
        };
    } = $props();

    let editModal = $state(false);
    let versionModal = $state(false);

    const editForm = useForm({
        name: project.name,
        description: project.description
    });

    const versionForm = useForm({
        name: '',
        ...latestRequirements,
        bundle_file: null as string | null
    });

    const editVersionForm = useForm({
        name: '',
        android_requirements: '',
        ios_requirements: ''
    });

    let editVersion: string | null = $state(null);
    let editVersionModal = $state(false);

    let apiEndpoint = $derived(
        new URL(`/api/bundles/${project.nanoid}`, window.location.href).href
    );

    let tab = $derived(
        {
            '#versions': 'versions',
            '#code': 'code'
        }[fragment.value] ?? 'info'
    );

    function submitEdit(ev: SubmitEvent) {
        ev.preventDefault();
        $editForm.post(`/projects/${project.nanoid}`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                $editForm.defaults({
                    name: project.name,
                    description: project.description
                });
                editModal = false;
            }
        });
    }

    function submitVersion(ev: SubmitEvent) {
        ev.preventDefault();
        $versionForm.post(`/projects/${project.nanoid}/versions`, {
            preserveState: true,
            onSuccess: () => {
                $versionForm.defaults({
                    name: '',
                    ...latestRequirements,
                    bundle_file: null
                });
                versionModal = false;
                fragment.value = '#versions';
            }
        });
    }

    function submitEditVersion(ev: SubmitEvent) {
        ev.preventDefault();
        $editVersionForm.post(`/projects/${project.nanoid}/versions/${editVersion}`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                editVersionModal = false;
            }
        });
    }
</script>

<svelte:head>
    <title>Project: {project.name} | {config.APP_NAME}</title>
</svelte:head>

<div class="w-full flex flex-row gap-4">
    <h1 class="text-3xl font-semibold">Project: {project.name}</h1>
    <label for="modal-version" class="ml-auto btn btn-solid-primary">New Version</label>
    <label for="modal-edit" class="btn btn-secondary">Edit Project</label>
</div>

<div class="tabs mt-4">
    <a
        href="#info"
        class="font-semibold tab text-lg tab-bordered px-8"
        class:tab-active={tab === 'info'}
    >
        Info
    </a>
    <a
        href="#versions"
        class="font-semibold tab text-lg tab-bordered px-8"
        class:tab-active={tab === 'versions'}
    >
        Versions ({versions.total})
    </a>
    <a
        href="#code"
        class="font-semibold tab text-lg tab-bordered px-8"
        class:tab-active={tab === 'code'}
    >
        Example Code
    </a>
</div>

<div class="mt-4">
    <div class="w-full" class:hidden={tab !== 'info'}>
        <div class="form-group max-w-[48rem]">
            <div class="grid grid-cols-2 gap-2">
                <div class="form-field">
                    <span class="form-label">ID</span>
                    <input type="text" value={project.nanoid} class="input input-block" readonly />
                </div>
                <div class="form-field">
                    <span class="form-label">Created At</span>
                    <input
                        type="text"
                        value={moment(project.created_at).format('MMMM Do YYYY, h:mm:ss a')}
                        class="input input-block"
                        readonly
                    />
                </div>
            </div>
            <div class="form-field">
                <span class="form-label">Name</span>
                <input type="text" value={project.name} class="input input-block" readonly />
            </div>
            <div class="form-field">
                <span class="form-label">Description</span>
                <textarea
                    class="textarea max-w-full resize-none"
                    rows="3"
                    placeholder="No Description"
                    readonly>{project.description}</textarea
                >
            </div>
            <div class="form-field">
                <span class="form-label">API Endpoint</span>
                <input
                    type="text"
                    value={apiEndpoint}
                    class="input input-block font-mono"
                    readonly
                />
            </div>
            <div class="form-field">
                <span class="form-label">Public Key</span>
                <textarea class="textarea max-w-full resize-none font-mono" rows="5" readonly
                    >{project.public_key}</textarea
                >
            </div>
        </div>
    </div>
    <div class="w-full" class:hidden={tab !== 'versions'}>
        <table class="table table-hover table-compact">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="w-0 min-w-max">Created At</th>
                    <th class="w-0 min-w-max">Android Requirements</th>
                    <th class="w-0 min-w-max">iOS Requirements</th>
                    <th class="w-0 min-w-max"></th>
                </tr>
            </thead>
            <tbody>
                {#each versions.data as version (version.id)}
                    {@const timestamp = moment(version.created_at).fromNow()}
                    <tr class="min-h-0 overflow-hidden">
                        <td>{version.name}</td>
                        <td class="w-0 min-w-max">{timestamp}</td>
                        <td class="w-0 min-w-max">{version.android_requirements || 'N/A'}</td>
                        <td class="w-0 min-w-max">{version.ios_requirements || 'N/A'}</td>
                        <td class="w-0 min-w-max">
                            <div class="btn-group min-w-max">
                                <button
                                    onclick={() => {
                                        editVersion = version.nanoid;
                                        editVersionModal = true;
                                        $editVersionForm.defaults({
                                            name: version.name,
                                            android_requirements:
                                                version.android_requirements || '',
                                            ios_requirements: version.ios_requirements || ''
                                        });
                                        $editVersionForm.reset();
                                        $editVersionForm.clearErrors();
                                    }}
                                    title="Edit"
                                    class="btn btn-sm"
                                >
                                    <PencilEdit01Icon size={16} />
                                </button>
                                <a
                                    href={`/api/bundles/${project.nanoid}/${version.nanoid}.zip`}
                                    title="Download"
                                    class="btn btn-sm"
                                >
                                    <Download01Icon size={16} />
                                </a>
                                <button
                                    onclick={async () => {
                                        const res = await promptAlert({
                                            title: 'Delete Version',
                                            content:
                                                'Are you sure you want to delete this version?',
                                            actions: {
                                                primary: 'Delete',
                                                secondary: 'Cancel'
                                            }
                                        });
                                        if (res) {
                                            router.delete(
                                                `/projects/${project.nanoid}/versions/${version.nanoid}`,
                                                {
                                                    replace: true,
                                                    preserveState: true,
                                                    preserveScroll: true
                                                }
                                            );
                                        }
                                    }}
                                    title="Delete"
                                    class="btn btn-sm"
                                >
                                    <Delete01Icon size={16} />
                                </button>
                            </div>
                        </td>
                    </tr>
                {:else}
                    <tr>
                        <td colspan="5" class="!text-center !text-content3 !text-lg">
                            No Versions
                        </td>
                    </tr>
                {/each}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">
                        <div class="flex flex-row justify-end mt-4">
                            <PaginateLinks
                                links={versions.links}
                                url={(url) => {
                                    url.hash = '#versions';
                                    return url;
                                }}
                            />
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="w-full" class:hidden={tab !== 'code'}>
        <CodeSnippet endpoint={apiEndpoint} />
    </div>
</div>

<input class="modal-state" id="modal-edit" type="checkbox" bind:checked={editModal} />
<div class="modal">
    <label class="modal-overlay" for="modal-edit"></label>
    <form
        onsubmit={submitEdit}
        method="post"
        class="modal-content flex flex-col gap-5 w-[24rem] max-w-full"
        ontransitionend={(ev) => {
            if (ev.target !== ev.currentTarget) return;
            if (!editModal) {
                $editForm.reset();
                $editForm.clearErrors();
            }
        }}
        action="/projects/{project.nanoid}"
        novalidate
    >
        <label for="modal-edit" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
            >✕</label
        >
        <h2 class="text-xl">Update Project</h2>
        <div class="form-group">
            <div class="form-field">
                <label for="name" class="form-label">Name <span class="text-red-10">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="input max-w-full"
                    bind:value={$editForm.name}
                    class:input-error={$editForm.errors.name}
                />
                <FormErrors error={$editForm.errors.name} />
            </div>
            <div class="form-field">
                <label for="description" class="form-label">Description</label>
                <textarea
                    id="description"
                    name="description"
                    class="textarea max-w-full resize-none"
                    rows="5"
                    placeholder="Project description"
                    bind:value={$editForm.description}
                    class:textarea-error={$editForm.errors.description}
                ></textarea>
                <FormErrors error={$editForm.errors.description} />
            </div>
        </div>
        <div class="flex gap-3">
            <button class="btn btn-primary btn-block">Save</button>
            <label for="modal-edit" class="btn btn-block">Cancel</label>
        </div>
    </form>
</div>

<input class="modal-state" id="modal-version" type="checkbox" bind:checked={versionModal} />
<div class="modal">
    <label class="modal-overlay" for="modal-version"></label>
    <form
        method="post"
        class="modal-content flex flex-col gap-5 w-[28rem] max-w-full"
        onsubmit={submitVersion}
        ontransitionend={(ev) => {
            if (ev.target !== ev.currentTarget) return;
            if (!versionModal) {
                $versionForm.reset();
                $versionForm.clearErrors();
                fileInput.value = '';
                tusUpload?.abort(true);
                tusUpload = null;
                uploadProgress = null;
            }
        }}
        action="/projects/{project.nanoid}/versions"
        novalidate
    >
        <label for="modal-version" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
            >✕</label
        >
        <h2 class="text-xl">Create Version</h2>
        <div class="form-group">
            <div class="form-field">
                <label for="name" class="form-label">Name <span class="text-red-10">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="input max-w-full"
                    bind:value={$versionForm.name}
                    class:input-error={$versionForm.errors.name}
                />
                <FormErrors error={$versionForm.errors.name} />
            </div>
            <div class="form-field">
                <span class="form-label">Bundle File <span class="text-red-10">*</span></span>
                <input
                    type="file"
                    id="bundle"
                    class="input-file input-file-xl max-w-full"
                    bind:this={fileInput}
                    class:input-file-error={$versionForm.errors.bundle_file}
                    class:hidden={uploadProgress !== null}
                    accept="application/zip"
                    onchange={() => {
                        const file = fileInput.files?.[0];
                        if (!file) return;
                        uploadProgress = null;
                        tusUpload = new Upload(file, {
                            endpoint: '/uploads',
                            metadata: {
                                name: file.name
                            },
                            chunkSize: 5 * 1024 * 1024,
                            onError: (err) => {
                                console.error(err);
                                tusUpload?.abort(true);
                                tusUpload = null;
                                fileInput.value = '';
                                uploadProgress = null;
                                $versionForm.setError('bundle_file', 'Upload failed');
                            },
                            onProgress: (bytesUploaded, bytesTotal) => {
                                uploadProgress = Math.floor((bytesUploaded / bytesTotal) * 100);
                            },
                            onSuccess: () => {
                                //@ts-ignore Svelte 5 support typescript syntax but phpstorm plugin not support yet.
                                const url = new URL(tusUpload.url);
                                $versionForm.bundle_file = url.pathname.replace('/uploads/', '');
                            }
                        });
                        tusUpload.start();
                        uploadProgress = 0;
                    }}
                />
                <div
                    class="flex flex-row items-center gap-2"
                    class:hidden={uploadProgress === null}
                >
                    <progress
                        class="progress progress-flat-primary w-0 flex-grow"
                        value={uploadProgress}
                        max="100"
                        class:hidden={uploadProgress === 100}
                    ></progress>
                    <div
                        class="w-0 flex-grow px-2 font-mono text-xs text-content2"
                        class:hidden={uploadProgress !== 100}
                    >
                        {$versionForm.bundle_file}.zip
                    </div>
                    <button
                        type="button"
                        class="btn btn-circle"
                        onclick={() => {
                            tusUpload?.abort(true);
                            tusUpload = null;
                            fileInput.value = '';
                            uploadProgress = null;
                            $versionForm.bundle_file = null;
                        }}
                    >
                        <MultiplicationSignIcon />
                    </button>
                </div>
                <FormErrors error={$versionForm.errors.bundle_file} />
            </div>
            <div class="form-field">
                <label for="android_requirements" class="form-label"
                    >Android Requirements (App Version)</label
                >
                <input
                    type="text"
                    id="android_requirements"
                    name="android_requirements"
                    class="input max-w-full"
                    bind:value={$versionForm.android_requirements}
                    class:input-error={$versionForm.errors.android_requirements}
                    placeholder="N/A"
                />
                <FormErrors error={$versionForm.errors.android_requirements} />
            </div>
            <div class="form-field">
                <label for="ios_requirements" class="form-label"
                    >iOS Requirements (App Version)</label
                >
                <input
                    type="text"
                    id="ios_requirements"
                    name="ios_requirements"
                    class="input max-w-full"
                    bind:value={$versionForm.ios_requirements}
                    class:input-error={$versionForm.errors.ios_requirements}
                    placeholder="N/A"
                />
                <FormErrors error={$versionForm.errors.ios_requirements} />
            </div>
        </div>
        <div class="flex gap-3">
            <button class="btn btn-primary btn-block">Create</button>
            <label for="modal-version" class="btn btn-block">Cancel</label>
        </div>
    </form>
</div>

<input
    class="modal-state"
    id="modal-edit-version"
    type="checkbox"
    bind:checked={editVersionModal}
/>
<div class="modal">
    <label class="modal-overlay" for="modal-edit-version"></label>
    <form
        method="post"
        class="modal-content flex flex-col gap-5 w-[28rem] max-w-full"
        onsubmit={submitEditVersion}
        action="/projects/{project.nanoid}/versions/{editVersion}"
        novalidate
    >
        <label
            for="modal-edit-version"
            class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label
        >
        <h2 class="text-xl">Edit Version</h2>
        <div class="form-group">
            <div class="form-field">
                <label for="edit_name" class="form-label"
                    >Name <span class="text-red-10">*</span></label
                >
                <input
                    type="text"
                    id="edit_name"
                    name="name"
                    class="input max-w-full"
                    bind:value={$editVersionForm.name}
                    class:input-error={$editVersionForm.errors.name}
                />
                <FormErrors error={$editVersionForm.errors.name} />
            </div>
            <div class="form-field">
                <label for="edit_android_requirements" class="form-label"
                    >Android Requirements (App Version)</label
                >
                <input
                    type="text"
                    id="edit_android_requirements"
                    name="android_requirements"
                    class="input max-w-full"
                    bind:value={$editVersionForm.android_requirements}
                    class:input-error={$editVersionForm.errors.android_requirements}
                    placeholder="N/A"
                />
                <FormErrors error={$editVersionForm.errors.android_requirements} />
            </div>
            <div class="form-field">
                <label for="edit_ios_requirements" class="form-label"
                    >iOS Requirements (App Version)</label
                >
                <input
                    type="text"
                    id="edit_ios_requirements"
                    name="ios_requirements"
                    class="input max-w-full"
                    bind:value={$editVersionForm.ios_requirements}
                    class:input-error={$editVersionForm.errors.ios_requirements}
                    placeholder="N/A"
                />
                <FormErrors error={$editVersionForm.errors.ios_requirements} />
            </div>
        </div>
        <div class="flex gap-3">
            <button class="btn btn-primary btn-block">Save</button>
            <label for="modal-edit-version" class="btn btn-block">Cancel</label>
        </div>
    </form>
</div>
