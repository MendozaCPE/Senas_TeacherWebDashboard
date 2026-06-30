@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Publish Lesson Configuration</h1>
            <a href="{{ route('lessons.index') }}" class="text-gray-600 hover:text-gray-800">
                ← Back to Lessons
            </a>
        </div>

        <!-- Lesson Info -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2">{{ $lesson->title }}</h2>
            <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                <span><strong>Type:</strong> {{ ucfirst($lesson->lesson_type) }}</span>
                <span><strong>Difficulty:</strong> {{ ucfirst($lesson->difficulty) }}</span>
                <span><strong>Status:</strong> 
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                        {{ ucfirst($lesson->status) }}
                    </span>
                </span>
            </div>
        </div>

        <!-- Publish Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-semibold text-red-700 mb-2">Please fix the following:</p>
                    <ul class="text-sm text-red-600 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('lessons.publish', $lesson->lesson_id) }}" method="POST" id="publishForm">
                @csrf

                <div class="mb-6 pb-6 border-b">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Which module should this lesson belong to? <span class="text-red-500">*</span>
                    </label>

                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="module_action" value="existing" id="moduleExisting"
                                   {{ old('module_action', ($lesson->module_id || $modules->isNotEmpty()) ? 'existing' : 'new') === 'existing' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ $modules->isEmpty() ? 'disabled' : '' }}>
                            Use an existing module
                        </label>
                        <div id="existingModuleBlock" class="ml-6">
                            <select name="module_id" id="module_id"
                                    class="w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                                <option value="">Select a module</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->module_id }}" {{ (string) old('module_id', $lesson->module_id) === (string) $module->module_id ? 'selected' : '' }}>
                                        {{ $module->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="module_action" value="new" id="moduleNew"
                                   {{ old('module_action', ($lesson->module_id || $modules->isNotEmpty()) ? 'existing' : 'new') === 'new' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            Create a new module
                        </label>
                        <div id="newModuleBlock" class="ml-6 hidden space-y-3 max-w-md">
                            <input type="text" name="new_module[title]" value="{{ old('new_module.title') }}"
                                   placeholder="Module title"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                            <textarea name="new_module[description]" rows="2" placeholder="Optional description"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('new_module.description') }}</textarea>
                        </div>
                    </div>

                    @if($modules->isEmpty())
                        <p class="mt-3 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md p-3">
                            You have no modules yet. Choose “Create a new module” above.
                        </p>
                    @endif
                    <p class="mt-2 text-xs text-gray-500">The lesson is assigned to this module when you publish.</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Who should receive this lesson?
                    </label>

                    <!-- Publish Options -->
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="radio" name="publish_option" value="all" id="publishAll" checked
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="publishAll" class="ml-3 block text-sm text-gray-700">
                                Publish to All Students
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="radio" name="publish_option" value="program" id="publishProgram"
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="publishProgram" class="ml-3 block text-sm text-gray-700">
                                Publish by Program
                            </label>
                            <select name="program" id="programSelect" disabled
                                    class="ml-4 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program }}">{{ ucfirst($program) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center">
                            <input type="radio" name="publish_option" value="mastery" id="publishMastery"
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="publishMastery" class="ml-3 block text-sm text-gray-700">
                                Publish by Mastery Level
                            </label>
                            <select name="mastery_level" id="masterySelect" disabled
                                    class="ml-4 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                                <option value="">Select Mastery Level</option>
                                @foreach($masteryLevels as $level)
                                    <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-start">
                            <input type="radio" name="publish_option" value="selected" id="publishSelected"
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 mt-1">
                            <label for="publishSelected" class="ml-3 block text-sm text-gray-700">
                                Select Specific Students
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Student Selection (shown when "Select Specific Students" is chosen) -->
                <div id="studentSelection" class="mb-6 hidden">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-700">Select Students</h3>
                            <div class="flex items-center space-x-4">
                                <button type="button" onclick="selectAllStudents()" 
                                        class="text-sm text-blue-600 hover:text-blue-800">
                                    Select All
                                </button>
                                <button type="button" onclick="deselectAllStudents()" 
                                        class="text-sm text-gray-600 hover:text-gray-800">
                                    Deselect All
                                </button>
                            </div>
                        </div>

                        <!-- Search/Filter -->
                        <div class="mb-4">
                            <input type="text" id="studentSearch" placeholder="Search students..." 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                        </div>

                        <!-- Student List -->
<div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-200">
    @foreach($students as $student)
    <div class="flex items-center px-4 py-3 hover:bg-gray-50">
        <input type="checkbox" name="students[]" value="{{ $student->student_id }}"
               class="student-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
        <label class="ml-3 flex-1 text-sm text-gray-700">
            <span class="font-medium">{{ $student->first_name }} {{ $student->last_name }}</span>
            @if($student->program || $student->mastery_level)
                <span class="text-gray-500 text-xs ml-2">
                    @if($student->program) {{ ucfirst($student->program) }} @endif
                    @if($student->mastery_level) • {{ ucfirst($student->mastery_level) }} @endif
                    @if($student->grade_level) • Grade {{ $student->grade_level }} @endif
                </span>
            @endif
        </label>
        <span class="text-xs text-gray-400">
            LRN: {{ $student->lrn }}
            @if($student->section)
                • {{ $student->section }}
            @endif
        </span>
    </div>
    @endforeach
</div>
                        
                        <div class="mt-2 text-sm text-gray-500">
                            <span id="selectedCount">0</span> students selected
                        </div>
                    </div>
                </div>

                <!-- Additional Options -->
                <div class="mb-6 mt-6 border-t pt-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Additional Options</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" name="notify_students" id="notifyStudents" value="1"
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="notifyStudents" class="ml-3 block text-sm text-gray-700">
                                Notify students about this lesson
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="send_reminder" id="sendReminder" value="1"
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="sendReminder" class="ml-3 block text-sm text-gray-700">
                                Send reminder notification
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 border-t pt-6">
                    <a href="{{ route('lessons.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            id="publishSubmitBtn"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-sm transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed">
                        Publish Lesson
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const publishOptions = document.querySelectorAll('input[name="publish_option"]');
    const studentSelection = document.getElementById('studentSelection');
    const programSelect = document.getElementById('programSelect');
    const masterySelect = document.getElementById('masterySelect');
    const moduleExisting = document.getElementById('moduleExisting');
    const moduleNew = document.getElementById('moduleNew');
    const existingModuleBlock = document.getElementById('existingModuleBlock');
    const newModuleBlock = document.getElementById('newModuleBlock');
    const moduleSelect = document.getElementById('module_id');

    function toggleModuleBlocks() {
        const useNew = moduleNew && moduleNew.checked;
        if (existingModuleBlock) existingModuleBlock.style.display = useNew ? 'none' : 'block';
        if (newModuleBlock) newModuleBlock.classList.toggle('hidden', !useNew);
        if (moduleSelect) {
            moduleSelect.required = !useNew;
            if (useNew) moduleSelect.value = '';
        }
    }

    if (moduleExisting) moduleExisting.addEventListener('change', toggleModuleBlocks);
    if (moduleNew) moduleNew.addEventListener('change', toggleModuleBlocks);
    toggleModuleBlocks();
    
    publishOptions.forEach(radio => {
        radio.addEventListener('change', function() {
            // Show/hide student selection
            if (this.value === 'selected') {
                studentSelection.classList.remove('hidden');
            } else {
                studentSelection.classList.add('hidden');
            }
            
            // Enable/disable program select
            if (this.value === 'program') {
                programSelect.disabled = false;
            } else {
                programSelect.disabled = true;
                programSelect.value = '';
            }
            
            // Enable/disable mastery select
            if (this.value === 'mastery') {
                masterySelect.disabled = false;
            } else {
                masterySelect.disabled = true;
                masterySelect.value = '';
            }
        });
    });
    
    // Student search functionality
    const searchInput = document.getElementById('studentSearch');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.student-checkbox').forEach((checkbox) => {
            const label = checkbox.nextElementSibling;
            const text = label.textContent.toLowerCase();
            const parent = checkbox.closest('.flex');
            if (text.includes(searchTerm)) {
                parent.style.display = 'flex';
            } else {
                parent.style.display = 'none';
            }
        });
    });
    
    // Update selected count
    const selectedCount = document.getElementById('selectedCount');
    document.querySelectorAll('.student-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.student-checkbox:checked').length;
        selectedCount.textContent = checked;
    }
});

function selectAllStudents() {
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = true);
    document.getElementById('selectedCount').textContent = document.querySelectorAll('.student-checkbox').length;
}

function deselectAllStudents() {
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectedCount').textContent = 0;
}

document.getElementById('publishForm').addEventListener('submit', function(e) {
    const useNew = document.getElementById('moduleNew')?.checked;
    const moduleSelect = document.getElementById('module_id');
    const newTitle = document.querySelector('input[name="new_module[title]"]');

    if (useNew) {
        if (!newTitle || !newTitle.value.trim()) {
            e.preventDefault();
            alert('Please enter a title for the new module.');
            newTitle?.focus();
            return;
        }
    } else if (moduleSelect && !moduleSelect.value) {
        e.preventDefault();
        alert('Please select a module before publishing this lesson.');
        moduleSelect.focus();
        return;
    }

    const selectedOption = document.querySelector('input[name="publish_option"]:checked');
    if (selectedOption && selectedOption.value === 'selected') {
        const checked = document.querySelectorAll('.student-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('Please select at least one student.');
        }
    }
});
</script>
@endsection