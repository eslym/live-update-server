<?php

namespace App\Http\Controllers;

use App\Http\Resources\VersionResource;
use App\Lib\Utils;
use App\Models\Project;
use App\Models\Version;
use App\Models\VersionChannel;
use App\Rules\ExistsRule;
use App\Rules\FindRule;
use App\Rules\UniqueRule;
use App\Rules\VersionRequirementsRule;
use Illuminate\Contracts\Support\Responsable;
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
    public function list(Request $request, Project $project): Responsable
    {
        $channels = $project->channels()->get();
        $query = $project->versions()
            ->with(['channels', 'channels.channel:id,name'])
            ->select([
                'id',
                'nanoid',
                'project_id',
                'name',
                'created_at',
                'android_available',
                'android_min',
                'android_max',
                'ios_available',
                'ios_min',
                'ios_max',
            ]);

        if ($search = $request->query->getString('q')) {
            $keywords = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->where('name', 'like', $keyword);
                }
            });
        }

        $sort = Utils::makeSort($query, ['name', 'created_at'], '-created_at');

        $versions = VersionResource::collection($query->paginate())
            ->additional([
                'meta' => [
                    'params' => [
                        'sort' => $sort,
                        'q' => $search,
                    ]
                ],
            ]);

        return inertia('project/versions', [
            'channels' => $channels,
            'project' => $project->only([
                'id', 'nanoid', 'name'
            ]),
            'versions' => $versions
        ]);
    }

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
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => [
                'nullable', 'string', 'regex:/^[a-z0-9\-]+$/',
                FindRule::make($project->channels(), 'name')
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

        $version = $project->versions()->create([
            ...$data->except(['channels', 'bundle_files', 'reqs'])->toArray(),
            'android_available' => is_array(data_get($data, 'reqs.android')),
            'android_min' => data_get($data, 'reqs.android.min'),
            'android_max' => data_get($data, 'reqs.android.max'),
            'ios_available' => is_array(data_get($data, 'reqs.ios')),
            'ios_min' => data_get($data, 'reqs.ios.min'),
            'ios_max' => data_get($data, 'reqs.ios.max'),
        ]);

        $channels = collect($data->get('channels'))->map(fn($ch) => [
            'version_id' => $version->id,
            'channel_id' => $ch?->id,
        ])->toArray();

        VersionChannel::insert($channels);

        return redirect()->back()
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
            'channels' => ['nullable', 'array', 'min:1'],
            'channels.*' => [
                'nullable', 'string', 'regex:/^[a-z0-9\-]+$/',
                FindRule::make($project->channels(), 'name')
            ],
            'reqs' => ['required', new VersionRequirementsRule()],
        ]));

        $version->update([
            ...$data->except(['channels', 'reqs'])->toArray(),
            'android_available' => is_array(data_get($data, 'reqs.android')),
            'android_min' => data_get($data, 'reqs.android.min'),
            'android_max' => data_get($data, 'reqs.android.max'),
            'ios_available' => is_array(data_get($data, 'reqs.ios')),
            'ios_min' => data_get($data, 'reqs.ios.min'),
            'ios_max' => data_get($data, 'reqs.ios.max'),
        ]);

        if ($data->has('channels') && $data->get('channels')) {
            $version->channels()->delete();
            $channels = collect($data->get('channels'))->map(fn($ch) => [
                'version_id' => $version->id,
                'channel_id' => $ch?->id,
            ])->toArray();
            VersionChannel::insert($channels);
        }

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
