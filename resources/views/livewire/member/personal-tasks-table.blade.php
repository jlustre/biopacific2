<div class="space-y-4">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-black text-slate-900">Task list</h2>
            <p class="text-[11px] text-slate-500">System work items and tasks you create or receive from teammates</p>
        </div>
        <button type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-sm font-bold text-white hover:bg-teal-700">
            <i class="fa-solid fa-plus text-xs"></i>
            New task
        </button>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <form wire:submit.prevent class="grid gap-3 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Search</label>
                <input type="search"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Title, description, people..."
                       class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Status</label>
                <select wire:model.live="status" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="open">Open</option>
                    <option value="all">All</option>
                    <option value="confirmed">Confirmed / done</option>
                </select>
            </div>
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Source</label>
                <select wire:model.live="source" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="all">All sources</option>
                    <option value="system">System</option>
                    <option value="manual">My tasks</option>
                </select>
            </div>
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Priority</label>
                <select wire:model.live="priority" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="all">All</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Assignment</label>
                <select wire:model.live="assignment" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="all">All</option>
                    <option value="assigned">Assigned to me</option>
                    <option value="created">Created by me</option>
                </select>
            </div>
            <div class="lg:col-span-6 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                <p class="text-xs text-slate-500">{{ $tasks->total() }} {{ str('task')->plural($tasks->total()) }} found</p>
                <div class="flex items-center gap-2 text-xs">
                    <label for="tasks-per-page" class="font-semibold text-slate-600">Per page</label>
                    <select id="tasks-per-page" wire:model.live="perPage" class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Task</th>
                        <th class="px-4 py-3">Source</th>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Due</th>
                        <th class="px-4 py-3">People</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tasks as $task)
                        <tr wire:key="task-row-{{ $task['key'] }}" class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 align-top">
                                <p class="font-semibold text-slate-900">{{ $task['title'] }}</p>
                                @if(!empty($task['description']))
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $task['description'] }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top">
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ ($task['type'] ?? '') === 'system' ? 'bg-sky-100 text-sky-800' : 'bg-violet-100 text-violet-800' }}">
                                    {{ ($task['type'] ?? '') === 'system' ? 'System' : 'Personal' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @php
                                    $priorityClass = match ($task['priority'] ?? 'medium') {
                                        'high' => 'bg-rose-100 text-rose-800',
                                        'low' => 'bg-slate-100 text-slate-700',
                                        default => 'bg-amber-100 text-amber-800',
                                    };
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $priorityClass }}">
                                    {{ ucfirst($task['priority'] ?? 'medium') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-slate-600">
                                {{ $task['due_at'] ?? '—' }}
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-slate-600">
                                <p><span class="font-semibold text-slate-700">By:</span> {{ $task['created_by_name'] ?? '—' }}</p>
                                <p class="mt-0.5"><span class="font-semibold text-slate-700">To:</span> {{ $task['assigned_to_name'] ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @php
                                    $statusClass = match ($task['status'] ?? '') {
                                        'confirmed' => 'bg-emerald-100 text-emerald-800',
                                        'completed' => 'bg-amber-100 text-amber-900',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusClass }}">
                                    {{ $task['status_label'] ?? 'Open' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap justify-end gap-1.5">
                                    @if(!empty($task['route']))
                                        <a href="{{ $task['route'] }}"
                                           class="rounded-lg border border-teal-200 bg-teal-50 px-2.5 py-1 text-[11px] font-bold text-teal-800 hover:bg-teal-100">
                                            {{ ($task['action'] ?? '') === 'sign' ? 'Sign' : (($task['action'] ?? '') === 'upload' ? 'Upload' : 'Open') }}
                                        </a>
                                    @endif

                                    @if($task['can_complete'] ?? false)
                                        <button type="button"
                                                wire:click="completeTask({{ $task['personal_task_id'] }})"
                                                wire:confirm="Mark this task as complete?"
                                                class="rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-800 hover:bg-emerald-100">
                                            Complete
                                        </button>
                                    @endif

                                    @if($task['can_confirm'] ?? false)
                                        <button type="button"
                                                wire:click="confirmTask({{ $task['personal_task_id'] }})"
                                                class="rounded-lg border border-teal-200 bg-teal-50 px-2.5 py-1 text-[11px] font-bold text-teal-800 hover:bg-teal-100">
                                            Confirm
                                        </button>
                                    @endif

                                    @if($task['can_edit'] ?? false)
                                        <button type="button"
                                                wire:click="openEditModal({{ $task['personal_task_id'] }})"
                                                class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100">
                                            Edit
                                        </button>
                                    @endif

                                    @if($task['can_delete'] ?? false)
                                        <button type="button"
                                                wire:click="deleteTask({{ $task['personal_task_id'] }})"
                                                wire:confirm="Delete this task?"
                                                class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-800 hover:bg-rose-100">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                No tasks match your filters. Create a task or clear filters to see more.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tasks->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:keydown.escape.window="closeModal">
            <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-base font-black text-slate-900">{{ $editingTaskId ? 'Edit task' : 'New task' }}</h3>
                    <p class="mt-1 text-xs text-slate-500">Assign to yourself or a teammate. They can mark it complete; you confirm when assigned to someone else.</p>
                </div>
                <form wire:submit.prevent="saveTask" class="space-y-4 px-5 py-4">
                    <div>
                        <label class="text-xs font-bold text-slate-700">Title</label>
                        <input type="text" wire:model="title" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
                        @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700">Description</label>
                        <textarea wire:model="description" rows="3" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea>
                        @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-bold text-slate-700">Priority</label>
                            <select wire:model="taskPriority" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-700">Due date</label>
                            <input type="date" wire:model="dueAt" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700">Assign to</label>
                        <select wire:model="assignedTo" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            @foreach($assigneeOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->name }}@if($option->id === auth()->id()) (me)@endif</option>
                            @endforeach
                        </select>
                        @error('assignedTo') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" wire:click="closeModal" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-bold text-white hover:bg-teal-700">
                            {{ $editingTaskId ? 'Save changes' : 'Create task' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
