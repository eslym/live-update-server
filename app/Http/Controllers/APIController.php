<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Version;
use App\Rules\SemverRule;
use Composer\Semver\Semver;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $versions = $project->versions();

        if ($query['platform'] === 'ios') {
            $versions->whereNotNull('ios_requirements');
        } else {
            $versions->whereNotNull('android_requirements');
        }
        $versions = $versions->orderByDesc('created_at')->get();

        $version = null;

        /** @var Collection<Version> $versions */
        foreach ($versions as $v) {
            if ($query['platform'] === 'ios' && Semver::satisfies($query['version'], $v->ios_requirements)) {
                $version = $v;
                break;
            } elseif ($query['platform'] === 'android' && Semver::satisfies($query['version'], $v->android_requirements)) {
                $version = $v;
                break;
            }
        }

        if (!$version) {
            return response()->json(['message' => 'No version found'], 404);
        }

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
