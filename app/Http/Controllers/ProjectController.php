<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
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
        ])->paginate(15);

        return Inertia::render('project/index', compact('projects'));
    }

    public function view(Project $project): Responsable
    {
        $versions = $project->versions()->orderByDesc('created_at')->paginate();

        return Inertia::render('project/view', compact('project', 'versions'));
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
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $key = $validator->getValue('private_key');
            if ($key) {
                $privateKey = openssl_pkey_get_private($key);
                if (!$privateKey) {
                    $validator->errors()->add('private_key', 'Invalid private key');
                }
                $publicKey = openssl_pkey_get_details($privateKey)['key'];
                $validator->setValue('public_key', $publicKey);
            }
        });

        $data = $validator->validate();

        $project = Project::query()->create($data);

        return redirect()->route('project.view', $project)
            ->with('alert', 'Project created');
    }

    public function update(Request $request, Project $project): Response
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($data);

        return redirect()->route('project.view', $project)
            ->with('alert', 'Project updated');
    }

    public function delete(Project $project): Response
    {
        $versions = $project->versions()->count();

        if ($versions > 0) {
            return redirect()->back()->with('alert', 'Project has versions');
        }

        return redirect()->route('project.index');
    }
}
