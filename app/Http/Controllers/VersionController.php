<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Version;
use App\Models\VersionResolution;
use App\Rules\SemverConstraintRule;
use App\Rules\UniqueRule;
use Composer\Semver\Semver;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use KalynaSolutions\Tus\Exceptions\FileNotFoundException;
use KalynaSolutions\Tus\Helpers\TusFile;
use Symfony\Component\HttpFoundation\Response;

class VersionController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function create(Request $request, Project $project): Response
    {
        $validator = validator($request->input(), [
            'name' => [
                'required',
                'string',
                UniqueRule::make(Version::class, 'name')
                    ->where('project_id', $project->id)
            ],
            'ios_requirements' => [
                'required_without:android_requirements',
                'nullable', 'string',
                new SemverConstraintRule()
            ],
            'android_requirements' => [
                'required_without:ios_requirements',
                'nullable', 'string',
                new SemverConstraintRule()
            ],
            'bundle_file' => ['required', 'string'],
            'bundle' => [],
        ]);

        $validator->after(function (Validator $validator) {
            $tusUploadId = $validator->getValue('bundle_file');
            if (!$tusUploadId) return;
            try {
                $file = TusFile::find($tusUploadId);
                $size = Storage::disk($file->disk)->size($file->path);
                if ($size !== $file->metadata['size']) {
                    $validator->errors()->add('bundle_file', 'Incomplete upload');
                }
                $validator->setValue(
                    'bundle',
                    new UploadedFile(Storage::disk($file->disk)->path($file->path), 'bundle.zip')
                );
            } catch (FileNotFoundException) {
                $validator->errors()->add('bundle_file', 'Invalid upload');
            }
        });

        $data = $validator->validate();

        /** @var UploadedFile $file */
        $file = $data['bundle'];

        $data['path'] = $file->store('bundles', 'local');

        openssl_sign(
            Storage::disk('local')->get($data['path']),
            $signature,
            $project->private_key,
            OPENSSL_ALGO_SHA256
        );

        $data['signature'] = base64_encode($signature);

        /** @var Version $version */
        $version = $project->versions()->create($data);

        if ($data['ios_requirements']) {
            /** @var Collection<VersionResolution> $resolutions */
            $resolutions = $project->version_resolutions()
                ->where('platform', 'ios')
                ->get(['id', 'app_version']);
            foreach ($resolutions as $resolution) {
                if (Semver::satisfies($resolution->app_version, $data['ios_requirements'])) {
                    $resolution->update([
                        'version_id' => $version->id,
                        'needs_reindex' => false,
                    ]);
                }
            }
        }
        if ($data['android_requirements']) {
            /** @var Collection<VersionResolution> $resolutions */
            $resolutions = $project->version_resolutions()
                ->where('platform', 'android')
                ->get(['id', 'app_version']);
            foreach ($resolutions as $resolution) {
                if (Semver::satisfies($resolution->app_version, $data['android_requirements'])) {
                    $resolution->update([
                        'version_id' => $version->id,
                        'needs_reindex' => false,
                    ]);
                }
            }
        }

        return redirect()->route('project.view', $project)
            ->with('alert', [
                'title' => 'Success',
                'content' => 'Version created successfully',
            ]);
    }

    public function update(Request $request, Project $project, Version $version): Response
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                UniqueRule::make(Version::class, 'name')
                    ->where('project_id', $project->id)
                    ->where('id', '!=', $version->id)
            ],
            'ios_requirements' => [
                'required_without:android_requirements',
                'nullable', 'string',
                new SemverConstraintRule()
            ],
            'android_requirements' => [
                'required_without:ios_requirements',
                'nullable', 'string',
                new SemverConstraintRule()
            ],
        ]);

        if ($version->ios_requirements !== $data['ios_requirements']) {
            $project->version_resolutions()
                ->where('platform', 'ios')
                ->update(['needs_reindex' => true]);
        }

        if ($version->android_requirements !== $data['android_requirements']) {
            $project->version_resolutions()
                ->where('platform', 'android')
                ->update(['needs_reindex' => true]);
        }

        $version->update($data);

        return redirect()->back()
            ->with('alert', [
                'title' => 'Success',
                'content' => 'Version updated successfully',
            ]);
    }

    public function delete(Project $project, Version $version): Response
    {
        Storage::disk('local')->delete($version->path);

        $version->delete();

        return redirect()->back()
            ->with('alert', [
                'title' => 'Success',
                'content' => 'Version deleted successfully',
            ]);
    }
}
