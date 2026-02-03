<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $projectId = $request->get('project_id');
        
        $query = Task::query();
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $tasks = $query->orderBy('priority')->get();
        $projects = Project::all();
        
        return view('tasks.index', compact('tasks', 'projects', 'projectId'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $maxPriority = Task::where('project_id', $validated['project_id'] ?? null)
            ->max('priority') ?? 0;

        $task = Task::create([
            'name' => $validated['name'],
            'priority' => $maxPriority + 1,
            'project_id' => $validated['project_id'] ?? null,
        ]);

        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $projectId = $task->project_id;
        $deletedPriority = $task->priority;
        
        $task->delete();

        // Reordenar prioridades após deletar
        Task::where('project_id', $projectId)
            ->where('priority', '>', $deletedPriority)
            ->decrement('priority');

        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'project_id' => 'nullable',
        ]);

        $projectId = $validated['project_id'] ?: null;
        $taskIds = $validated['task_ids'];

        // Verificar se todas as tarefas pertencem ao mesmo projeto (ou todas sem projeto)
        $tasks = Task::whereIn('id', $taskIds)->get();
        
        foreach ($tasks as $task) {
            $taskProjectId = $task->project_id ?: null;
            if ($taskProjectId != $projectId) {
                return response()->json(['error' => 'Todas as tarefas devem pertencer ao mesmo projeto'], 400);
            }
        }

        // Atualizar prioridades baseado na ordem do array
        foreach ($taskIds as $index => $taskId) {
            Task::where('id', $taskId)->update(['priority' => $index + 1]);
        }

        return response()->json(['message' => 'Tasks reordered successfully']);
    }
}
