<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Version;
use App\Models\VersionResolution;
use App\Rules\SemverRule;
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
            'version' => ['required', 'string', new SemverRule()],
        ])->validate();

        $resolution = $project->version_resolutions()
            ->where('platform', $query['platform'])
            ->where('app_version', $query['version'])
            ->first();

        if (!$resolution) {
            $resolution = new VersionResolution([
                'project_id' => $project->id,
                'platform' => $query['platform'],
                'app_version' => $query['version'],
                'needs_reindex' => true,
            ]);
        }

        if ($resolution->needs_reindex) {
            $resolution->reIndex();
        }

        if (!$resolution->version_id) {
            return response()->json(['message' => 'No version found'], 404);
        }

        $version = $resolution->version()->first([
            'id', 'name', 'signature',
        ]);

        return response()->json([
            'message' => 'Version found',
            'name' => $version->name,
            'id' => $version->id,
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
