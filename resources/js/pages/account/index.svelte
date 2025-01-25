<script lang="ts" module>
    export { default as layout } from '@/layouts/dashboard.svelte';
</script>

<script lang="ts">
    import { router, useForm } from '@inertiajs/svelte';
    import { config } from '@/lib/config';
    import moment from 'moment';
    import { Delete01Icon, PencilIcon } from 'hugeicons-svelte';
    import { promptAlert } from '@/components/Alert.svelte';
    import FormErrors from '@/components/FormErrors.svelte';

    let {
        accounts,
        can_create
    }: {
        accounts: Pagination<{
            id: number;
            nanoid: string;
            name: string;
            email: string;
            is_2fa_enabled: boolean;
            is_mutable: boolean;
            created_at: string;
        }>;
        can_create: boolean;
    } = $props();

    const createForm = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    });

    const editForm = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        remove_2fa: false
    });

    let createModal = $state(false);
    let editModal = $state(false);

    let editingModal = $state({
        id: '',
        twoFactor: false
    });

    function submitCreate(ev: SubmitEvent) {
        ev.preventDefault();
        $createForm.post('/accounts', {
            preserveState: true,
            preserveScroll: true,
            onError: () => {
                $createForm.reset('password', 'password_confirmation');
            },
            onSuccess: () => {
                createModal = false;
            }
        });
    }

    function submitEdit(ev: SubmitEvent) {
        ev.preventDefault();
        $editForm.post(`/accounts/${editingModal.id}`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                editModal = false;
            }
        });
    }
</script>

<svelte:head>
    <title>Accounts | {config.APP_NAME}</title>
</svelte:head>

<div class="w-full flex flex-row">
    <h1 class="text-3xl font-semibold">Accounts</h1>
    {#if can_create}
        <label for="modal-create" class="ml-auto btn btn-primary">Create Account</label>
    {:else}
        <button class="ml-auto btn btn-primary" disabled>Create Account</button>
    {/if}
</div>

<table class="table table-hover mt-4">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>2FA</th>
            <th>Created At</th>
            <th class="w-0"></th>
        </tr>
    </thead>
    <tbody>
        {#each accounts.data as account (account.id)}
            {@const timestamp = moment(account.created_at).fromNow()}
            <tr>
                <td>{account.name}</td>
                <td>{account.email}</td>
                <td class="min-w-max w-0">
                    {#if account.is_2fa_enabled}
                        <span class="badge badge-success">Enabled</span>
                    {:else}
                        <span class="badge badge-warning">Disabled</span>
                    {/if}
                </td>
                <td class="min-w-max w-0">{timestamp}</td>
                <td class="min-w-max w-0">
                    <div class="btn-group min-w-max">
                        <button
                            class="btn btn-sm"
                            disabled={!account.is_mutable}
                            onclick={() => {
                                $editForm.defaults({
                                    name: account.name,
                                    email: account.email,
                                    password: '',
                                    password_confirmation: '',
                                    remove_2fa: false
                                });
                                $editForm.reset();
                                $editForm.clearErrors();
                                editingModal.id = account.nanoid;
                                editingModal.twoFactor = account.is_2fa_enabled;
                                editModal = true;
                            }}
                        >
                            <PencilIcon size={16} />
                        </button>
                        <button
                            class="btn btn-sm"
                            disabled={!account.is_mutable}
                            onclick={async () => {
                                const res = await promptAlert({
                                    title: 'Delete Account',
                                    content: 'Are you sure you want to delete this account?',
                                    actions: {
                                        primary: 'Delete',
                                        secondary: 'Cancel'
                                    }
                                });
                                if (res) {
                                    router.delete(`/accounts/${account.nanoid}`, {
                                        replace: true,
                                        preserveState: true,
                                        preserveScroll: true
                                    });
                                }
                            }}
                        >
                            <Delete01Icon size={16} />
                        </button>
                    </div>
                </td>
            </tr>
        {:else}
            <tr>
                <td colspan="4" class="!text-center !text-content3 !text-lg"> No Accounts</td>
            </tr>
        {/each}
    </tbody>
</table>

<input class="modal-state" id="modal-create" type="checkbox" bind:checked={createModal} />
<div class="modal">
    <label class="modal-overlay" for="modal-create"></label>
    <form
        onsubmit={submitCreate}
        method="post"
        class="modal-content flex flex-col gap-5 w-[24rem] max-w-full"
        ontransitionend={(ev) => {
            if (ev.target !== ev.currentTarget) return;
            if (!createModal) {
                $createForm.reset();
                $createForm.clearErrors();
            }
        }}
        action="/accounts"
        novalidate
    >
        <label for="modal-create" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
            >✕</label
        >
        <h2 class="text-xl">Create Account</h2>
        <div class="form-group">
            <div class="form-field">
                <label for="name" class="form-label">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    bind:value={$createForm.name}
                    class="input input-block"
                    class:input-error={$createForm.errors.name}
                />
                <FormErrors error={$createForm.errors.name} />
            </div>
        </div>
        <div class="form-group">
            <div class="form-field">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    bind:value={$createForm.email}
                    class="input input-block"
                    class:input-error={$createForm.errors.email}
                />
                <FormErrors error={$createForm.errors.email} />
            </div>
        </div>
        <div class="form-group">
            <div class="form-field">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    bind:value={$createForm.password}
                    class="input input-block"
                    class:input-error={$createForm.errors.password}
                />
                <FormErrors error={$createForm.errors.password} />
            </div>
        </div>
        <div class="form-group">
            <div class="form-field">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    bind:value={$createForm.password_confirmation}
                    class="input input-block"
                    class:input-error={$createForm.errors.password_confirmation}
                />
                <FormErrors error={$createForm.errors.password_confirmation} />
            </div>
        </div>
        <div class="flex gap-3">
            <button class="btn btn-primary btn-block">Create</button>
            <label for="modal-create" class="btn btn-block">Cancel</label>
        </div>
    </form>
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
        action="/accounts/{editingModal.id}"
        novalidate
    >
        <label for="modal-edit" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
            >✕</label
        >
        <h2 class="text-xl">Edit Account</h2>
        <div class="form-group">
            <div class="form-field">
                <label for="name" class="form-label">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    bind:value={$editForm.name}
                    class="input input-block"
                    class:input-error={$editForm.errors.name}
                />
                <FormErrors error={$editForm.errors.name} />
            </div>
        </div>
        <div class="form-group">
            <div class="form-field">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    bind:value={$editForm.email}
                    class="input input-block"
                    class:input-error={$editForm.errors.email}
                />
                <FormErrors error={$editForm.errors.email} />
            </div>
        </div>
        <div class="form-group">
            <div class="form-field">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Leave blank to keep the same password"
                    bind:value={$editForm.password}
                    class="input input-block"
                    class:input-error={$editForm.errors.password}
                />
                <FormErrors error={$editForm.errors.password} />
            </div>
        </div>
        <div class="form-group">
            <div class="form-field">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    bind:value={$editForm.password_confirmation}
                    class="input input-block"
                    class:input-error={$editForm.errors.password_confirmation}
                />
                <FormErrors error={$editForm.errors.password_confirmation} />
            </div>
        </div>
        <div class="form-group">
            <div class="form-field">
                <label class="form-label">
                    Remove 2FA
                    <input
                        type="checkbox"
                        id="remove_2fa"
                        name="remove_2fa"
                        bind:checked={$editForm.remove_2fa}
                        disabled={!editingModal.twoFactor}
                        class="switch switch-primary"
                    />
                </label>
            </div>
        </div>
        <div class="flex gap-3">
            <button class="btn btn-primary btn-block">Update</button>
            <label for="modal-edit" class="btn btn-block">Cancel</label>
        </div>
    </form>
</div>
