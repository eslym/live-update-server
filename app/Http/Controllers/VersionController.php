<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Version;
use App\Rules\UniqueRule;
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
            'ios_requirements' => ['nullable', 'string'],
            'android_requirements' => ['nullable', 'string'],
            'tus_upload_id' => ['required', 'string'],
        ]);

        $validator->after(function (Validator $validator) {
            $tusUploadId = $validator->getValue('tus_upload_id');
            if (!$tusUploadId) return;
            try {
                $file = TusFile::find($tusUploadId);
                $size = Storage::disk($file->disk)->size($file->path);
                if ($size !== $file->metadata['size']) {
                    $validator->errors()->add('tus_upload_id', 'Incomplete upload');
                }
                $validator->setValue(
                    'bundle',
                    new UploadedFile(Storage::disk($file->disk)->path($file->path), 'bundle.zip')
                );
            } catch (FileNotFoundException) {
                $validator->errors()->add('tus_upload_id', 'Invalid upload');
            }
        });

        $data = $validator->validate();

        $data['path'] = $data['bundle']->store('bundles', 'public');

        $project->versions()->create($data);

        return redirect()->route('project.view', $project)
            ->with('alert', 'Version created');
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
            'ios_requirements' => ['nullable', 'string'],
            'android_requirements' => ['nullable', 'string'],
        ]);

        $version->update($data);

        return redirect()->route('project.view', $project)
            ->with('alert', 'Version updated');
    }

    public function delete(Project $project, Version $version): Response
    {
        Storage::disk('public')->delete($version->path);

        $version->delete();

        return redirect()->route('project.view', $project)
            ->with('alert', 'Version deleted');
    }
}
