@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-0">
                            <i class="bi bi-list-task"></i> Tarefas
                        </h4>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-inline-block me-3">
                            <select id="projectFilter" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                                <option value="">Todos os Projetos</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#projectModal">
                            <i class="bi bi-folder-plus"></i> Novo Projeto
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal" data-task-id="">
                            <i class="bi bi-plus-circle"></i> Nova Tarefa
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="tasksList" class="list-group">
                    @forelse($tasks as $task)
                        <div class="list-group-item task-item" data-task-id="{{ $task->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-grip-vertical text-muted me-3"></i>
                                    <span class="badge bg-secondary priority-badge me-2">#{{ $task->priority }}</span>
                                    <span class="task-name">{{ $task->name }}</span>
                                    @if($task->project)
                                        <span class="badge bg-info ms-2">{{ $task->project->name }}</span>
                                    @endif
                                </div>
                                <div>
                                    <small class="text-muted me-3">
                                        Criado: {{ $task->created_at->format('d/m/Y H:i') }}
                                    </small>
                                    <button class="btn btn-sm btn-outline-primary edit-task" data-task-id="{{ $task->id }}" data-task-name="{{ $task->name }}" data-project-id="{{ $task->project_id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-task" data-task-id="{{ $task->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Nenhuma tarefa encontrada. Crie uma nova tarefa para começar!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Criar/Editar Tarefa -->
<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalTitle">Nova Tarefa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="taskForm">
                <div class="modal-body">
                    <input type="hidden" id="taskId" name="task_id">
                    <div class="mb-3">
                        <label for="taskName" class="form-label">Nome da Tarefa</label>
                        <input type="text" class="form-control" id="taskName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="taskProject" class="form-label">Projeto</label>
                        <select class="form-select" id="taskProject" name="project_id">
                            <option value="">Sem Projeto</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Criar Projeto -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Projeto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="projectForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="projectName" class="form-label">Nome do Projeto</label>
                        <input type="text" class="form-control" id="projectName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const projectId = @json($projectId);
    
    // Configurar SortableJS para drag and drop
    const tasksList = document.getElementById('tasksList');
    if (tasksList) {
        new Sortable(tasksList, {
            animation: 150,
            handle: '.bi-grip-vertical',
            onEnd: function(evt) {
                const taskItems = Array.from(tasksList.querySelectorAll('.task-item'));
                const taskIds = taskItems.map(item => item.dataset.taskId);
                
                fetch('/tasks/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        task_ids: taskIds,
                        project_id: projectId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao reordenar tarefas');
                });
            }
        });
    }
    
    // Modal de Tarefa
    const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
    const taskForm = document.getElementById('taskForm');
    const taskModalTitle = document.getElementById('taskModalTitle');
    
    // Abrir modal para criar nova tarefa
    document.querySelector('[data-bs-target="#taskModal"]').addEventListener('click', function() {
        taskModalTitle.textContent = 'Nova Tarefa';
        taskForm.reset();
        document.getElementById('taskId').value = '';
        document.getElementById('taskProject').value = projectId || '';
    });
    
    // Abrir modal para editar tarefa
    document.querySelectorAll('.edit-task').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.dataset.taskId;
            const taskName = this.dataset.taskName;
            const taskProjectId = this.dataset.projectId || '';
            
            taskModalTitle.textContent = 'Editar Tarefa';
            document.getElementById('taskId').value = taskId;
            document.getElementById('taskName').value = taskName;
            document.getElementById('taskProject').value = taskProjectId;
            taskModal.show();
        });
    });
    
    // Salvar tarefa
    taskForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const taskId = document.getElementById('taskId').value;
        const formData = {
            name: document.getElementById('taskName').value,
            project_id: document.getElementById('taskProject').value || null
        };
        
        const url = taskId ? `/tasks/${taskId}` : '/tasks';
        const method = taskId ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            taskModal.hide();
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao salvar tarefa');
        });
    });
    
    // Deletar tarefa
    document.querySelectorAll('.delete-task').forEach(button => {
        button.addEventListener('click', function() {
            if (!confirm('Tem certeza que deseja deletar esta tarefa?')) {
                return;
            }
            
            const taskId = this.dataset.taskId;
            
            fetch(`/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao deletar tarefa');
            });
        });
    });
    
    // Criar projeto
    const projectForm = document.getElementById('projectForm');
    projectForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('projectName').value
        };
        
        fetch('/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('projectModal')).hide();
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao criar projeto');
        });
    });
    
    // Filtro de projeto
    const projectFilter = document.getElementById('projectFilter');
    if (projectFilter) {
        projectFilter.addEventListener('change', function() {
            const selectedProjectId = this.value;
            const url = selectedProjectId ? `/?project_id=${selectedProjectId}` : '/';
            window.location.href = url;
        });
    }
});
</script>
@endpush
