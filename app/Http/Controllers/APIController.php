<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Project;
use App\Models\Version;
use App\Rules\FindRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class APIController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function query(Request $request, Project $project): Response
    {
        $query = Validator::make($request->query->all(), [
            'platform' => ['required', 'string', 'in:ios,android'],
            'build' => ['required', 'integer'],
            'channel' => ['nullable', FindRule::make(Channel::class, 'name')
                ->where('project_id', $project->id)
            ]
        ])->validate();

        $platform = $query['platform'];

        /** @var Builder $source */
        $source = ($query['channel'] ?? false) ? $query['channel']->versions() :
            $project->versions()
                ->leftJoin(
                    'version_channel',
                    'versions.id', '=', 'version_channel.version_id'
                )
                ->where('version_channel.channel_id', null);

        $source->where($platform . '_available', true);

        $source->where(
            fn(Builder $builder) => $builder->whereNull($platform . '_min')
                ->orWhere($platform . '_min', '<=', $query['build'])
        );

        $source->where(
            fn(Builder $builder) => $builder->whereNull($platform . '_max')
                ->orWhere($platform . '_max', '>', $query['build'])
        );

        $version = $source->orderByDesc('created_at')->first();

        if (!$version) {
            return response()->json(['message' => 'No version found'], 404);
        }

        return response()->json([
            'message' => 'Version found',
            'name' => $version->name,
            'id' => $version->nanoid,
            'url' => route('api.download', [$project, $version]),
            'signature' => $version->signature,
        ]);
    }

    public function download(Project $project, Version $version): Response
    {
        return response()
            ->download(
                Storage::disk('local')
                    ->path($version->path),
                "$version->nanoid.zip",
                [
                    'Cache-Control' => 'public, max-age=604800',
                    'Content-Type' => 'application/zip',
                    'ETag' => json_encode($version->signature),
                    'X-Signature' => $version->signature,
                ]
            );
    }

    public function create(Request $request, Project $project): Response
    {
        app(VersionController::class)->create($request, $project);
        return response()->json(['message' => 'Version created']);
    }
}
