<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $project = $this->route('project');

        if (! $user) {
            return false;
        }

        if ($project instanceof Project) {
            return Project::accessibleTo($user)->whereKey($project->id)->exists();
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'challenge_run_id' => ['sometimes', 'nullable', 'exists:challenge_runs,id'],
        ];
    }
}
