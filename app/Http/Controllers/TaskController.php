<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Request $request)
    {
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED);

        $this->authorize('view', $project);

        $tasks = Task::accessibleTo($user)
            ->where('project_id', $project->id)
            ->latest()
            ->paginate($request->get('per_page', 15));

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, Project $project)
    {
        $data = $request->validated();

        $this->authorize('create', [Task::class, $project]);

        $task = $project->tasks()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'user_id' => $request->user()->id,
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
        ]);

        return new TaskResource($task->load('project'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $data = $request->validated();

        $this->authorize('update', $task);

        $task->update($data);

        return new TaskResource($task->load('project'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }
}
