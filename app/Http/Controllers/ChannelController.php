<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Project;
use App\Rules\UniqueRule;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelController extends Controller
{
    public function create(Request $request, Project $project): Response
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'regex:/^[a-z0-9\-]+$/',
                UniqueRule::make($project->channels(), 'name')
            ],
        ]);

        $project->channels()->create($data);

        return redirect()->route('project.view', $project->nanoid)
            ->with('toast', [
                'type' => 'success',
                'title' => 'Channel created successfully',
            ]);
    }

    public function delete(Request $request, Project $project, Channel $channel): Response
    {
        if ($channel->versions()->count()) {
            return redirect()->back()
                ->with('alert', [
                    'title' => 'Cannot delete channel',
                    'content' => 'This channel has versions associated with it. Please delete the versions first.',
                ]);
        }

        $channel->delete();

        return redirect()->route('project.view', $project->nanoid)
            ->with('toast', [
                'type' => 'success',
                'title' => 'Channel deleted successfully',
            ]);
    }
}
