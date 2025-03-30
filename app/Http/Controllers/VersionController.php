<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Version;
use App\Rules\UniqueRule;
use App\Rules\VersionRequirementsRule;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
            'reqs' => ['required', new VersionRequirementsRule()],
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

        $data = collect($validator->validate());

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

        $project->versions()->create([
            ...$validator->validate(),
            'android_available' => is_array($data->get('reqs.android')),
            'android_min' => $data->get('reqs.android.min'),
            'android_max' => $data->get('reqs.android.max'),
            'ios_available' => is_array($data->get('reqs.ios')),
            'ios_min' => $data->get('reqs.ios.min'),
            'ios_max' => $data->get('reqs.ios.max'),
        ]);

        return redirect()->route('project.view', $project)
            ->with('toast', [
                'type' => 'success',
                'title' => 'Version created successfully',
            ]);
    }

    public function update(Request $request, Project $project, Version $version): Response
    {
        $data = collect($request->validate([
            'name' => [
                'required',
                'string',
                UniqueRule::make(Version::class, 'name')
                    ->where('project_id', $project->id)
                    ->where('id', '!=', $version->id)
            ],
            'reqs' => ['required', new VersionRequirementsRule()],
        ]));

        $version->update([
            ...$data,
            'android_available' => is_array($data->get('reqs.android')),
            'android_min' => $data->get('reqs.android.min'),
            'android_max' => $data->get('reqs.android.max'),
            'ios_available' => is_array($data->get('reqs.ios')),
            'ios_min' => $data->get('reqs.ios.min'),
            'ios_max' => $data->get('reqs.ios.max'),
        ]);

        return redirect()->back()
            ->with('toast', [
                'type' => 'success',
                'title' => 'Version updated successfully',
            ]);
    }

    public function delete(Project $project, Version $version): Response
    {
        Storage::disk('local')->delete($version->path);

        $version->delete();

        return redirect()->back()
            ->with('toast', [
                'type' => 'success',
                'title' => 'Version deleted successfully',
            ]);
    }
}
