<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED);

        $this->authorize('viewAny', Project::class);

        $projects = Project::accessibleTo($user)
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        $this->authorize('create', Project::class);

        $project = Project::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'challenge_run_id' => $data['challenge_run_id'] ?? null,
            'user_id' => $user->id,
        ]);

        return new ProjectResource($project);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->fresh());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $data = $request->validated();

        $this->authorize('update', $project);

        $project->update($data);

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->noContent();
    }
}
