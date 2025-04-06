<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Lib\Utils;
use App\Models\Project;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Responsable
    {
        $query = Project::query()->with([
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
        ]);

        if ($search = $request->query->getString('q')) {
            $keywords = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->where(
                        fn($query) => $query->where('name', 'like', $keyword)
                            ->orWhere('description', 'like', $keyword)
                    );
                }
            });
        }

        $sort = Utils::makeSort($query, ['name', 'created_at']);

        $projects = ProjectResource::collection(
            $query->paginate(15)
        )->additional([
            'meta' => [
                'params' => [
                    'sort' => $sort,
                    'q' => $search,
                ]
            ]
        ]);

        return inertia('project/index', compact('projects'));
    }

    public function view(Project $project): Responsable
    {
        $versions = $project->versions()->count();
        $channels = $project->channels()->get();

        return inertia('project/view', [
            ...compact('versions', 'channels'),
            'project' => $project->only([
                'id',
                'nanoid',
                'name',
                'description',
                'public_key',
                'created_at',
            ]),
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
            ->with('toast', [
                'type' => 'success',
                'title' => 'Project created successfully',
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
            ->with('toast', [
                'type' => 'success',
                'title' => 'Project updated successfully',
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

        return redirect()->route('project.index')
            ->with('alert', [
                'title' => 'Success',
                'content' => 'Project deleted successfully',
            ]);
    }
}
