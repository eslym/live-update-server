<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function index(): Responsable
    {
        $projects = Project::query()->with([
            'versions' => fn($query) => $query->orderByDesc('created_at')->limit(1)
                ->select([
                    'id',
                    'project_id',
                    'name',
                ]),
        ])->select([
            'id',
            'nanoid',
            'name',
            'created_at',
        ])->paginate(15);

        return inertia('project/index', compact('projects'));
    }

    public function view(Project $project): Responsable
    {
        $versions = $project->versions()
            ->orderByDesc('created_at')
            ->select([
                'id',
                'nanoid',
                'project_id',
                'name',
                'created_at',
                'android_requirements',
                'ios_requirements',
            ])
            ->paginate();

        $latestRequirements = $project->versions()->orderByDesc('created_at')->first([
            'android_requirements',
            'ios_requirements',
        ]);

        return inertia('project/view', [
            'project' => $project->only([
                'id',
                'nanoid',
                'name',
                'description',
                'public_key',
                'created_at',
            ]),
            'versions' => $versions,
            'latestRequirements' => $latestRequirements ?? [
                    'android_requirements' => null,
                    'ios_requirements' => null,
                ],
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function create(Request $request): Response
    {
        $validator = Validator::make($request->input(), [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'private_key' => ['nullable', 'string'],
            'public_key' => [],
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $key = $validator->getValue('private_key');
            if ($key) {
                $privateKey = openssl_pkey_get_private($key);
                if (!$privateKey) {
                    $validator->errors()->add('private_key', 'Invalid private key');
                    return;
                }
                $publicKey = openssl_pkey_get_details($privateKey)['key'];
            } else {
                $privateKey = openssl_pkey_new([
                    'private_key_bits' => 1024,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                ]);
                openssl_pkey_export($privateKey, $key);
                $publicKey = openssl_pkey_get_details($privateKey)['key'];
                $validator->setValue('private_key', $key);
            }
            $validator->setValue('public_key', $publicKey);
        });

        $data = $validator->validate();

        $project = Project::query()->create($data);

        return redirect()->route('project.view', $project)
            ->with('alert', [
                'title' => 'Success',
                'content' => 'Project created successfully',
            ]);
    }

    public function update(Request $request, Project $project): Response
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($data);

        return redirect()->route('project.view', $project)
            ->with('alert', [
                'title' => 'Success',
                'content' => 'Project updated successfully',
            ]);
    }

    public function delete(Project $project): Response
    {
        $versions = $project->versions()->count();

        if ($versions > 0) {
            return redirect()->back()->with('alert', [
                'title' => 'Error',
                'content' => 'Project has versions, delete them first',
            ]);
        }

        $project->delete();

        return redirect()->back()
            ->with('alert', [
                'title' => 'Success',
                'content' => 'Project deleted successfully',
            ]);
    }
}
