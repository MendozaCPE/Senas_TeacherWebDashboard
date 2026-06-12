@extends('layouts.app')
@section('title', 'Students')
@section('content')
<!-- Header Section -->
            <div class="flex items-start justify-between mb-8">
                <div>
                    <h3 class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-2">Overview</h3>
                    <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Student Management</h2>
                </div>
                
                <!-- Quick Stats Pill -->
                <div class="bg-white rounded-full py-4 px-8 shadow-sm flex items-center divide-x divide-slate-200 border border-slate-100">
                    <div class="flex flex-col items-center px-8">
                        <span class="text-[28px] font-medium text-[#0d326b] leading-none mb-1">{{ $totalStudents }}</span>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Total Students</span>
                    </div>
                    <div class="flex flex-col items-center px-8">
                        <span class="text-[28px] font-medium text-[#857a26] leading-none mb-1">{{ $newThisWeek }}</span>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">New This Week</span>
                    </div>
                </div>
            </div>

            <!-- Filters & Actions -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <!-- Segmented Control -->
                    <div class="bg-[#f1f5f9] p-1.5 rounded-full flex items-center shadow-inner">
                        <button class="px-6 py-2.5 bg-white text-[#0d326b] text-[13px] font-bold rounded-full shadow-sm">All Students</button>
                        <button class="px-6 py-2.5 text-slate-500 hover:text-[#0d326b] text-[13px] font-medium rounded-full transition-colors">Active</button>
                        <button class="px-6 py-2.5 text-slate-500 hover:text-[#0d326b] text-[13px] font-medium rounded-full transition-colors">Inactive</button>
                    </div>
                    
                    <!-- Select Dropdowns -->
                    <div class="relative">
                        <select class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[13px] font-semibold py-3 pl-5 pr-10 rounded-full outline-none hover:bg-slate-200 transition-colors cursor-pointer border border-transparent">
                            <option>Learning Level</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                    
                    <div class="relative">
                        <select class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[13px] font-semibold py-3 pl-5 pr-10 rounded-full outline-none hover:bg-slate-200 transition-colors cursor-pointer border border-transparent">
                            <option>Performance: High to Low</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                </div>
                
                <button id="open-modal-btn" class="bg-[#0d326b] hover:bg-[#154188] text-white px-6 py-3 rounded-xl text-[14px] font-semibold transition-colors flex items-center space-x-2 shadow-sm">
                    <span class="material-symbols-outlined icon-outline text-[20px]">person_add</span>
                    <span>Add New Student</span>
                </button>
            </div>

            <!-- Student List Table -->
            <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Student Name</th>
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Level</th>
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Completed Lessons</th>
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Grades</th>
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Last Activity</th>
                            <th class="py-5 px-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($students as $student)
                        <!-- Student Row -->
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name . ' ' . $student->last_name) }}&background=random&color=fff&rounded=true" class="w-[42px] h-[42px] rounded-full shadow-sm"/>
                                    <div>
                                        <p class="text-[15px] font-bold text-[#0d326b]">{{ $student->first_name }} {{ $student->last_name }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 font-medium">LRN: {{ $student->lrn ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="px-3 py-1.5 bg-[#e0e7ff] text-[#4f46e5] text-[10px] font-bold rounded-full uppercase tracking-wider">{{ $student->grade_level ?? 'Beginner' }}</span>
                            </td>
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <span class="text-[14px] font-bold text-[#1e293b]">--/60</span>
                                    <div class="w-24 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#0d326b] rounded-full" style="width: 0%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="text-[15px] font-bold text-[#0d326b]">--%</span>
                            </td>
                            <td class="py-5 px-8">
                                <p class="text-[13px] font-medium text-[#1e293b]">{{ $student->created_at ? $student->created_at->diffForHumans() : 'Never' }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Enrolled</p>
                            </td>
                            <td class="py-5 px-8 text-right">
                                <button class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-[#0d326b] transition-colors">
                                    <span class="material-symbols-outlined icon-outline text-[20px]">chevron_right</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">No students found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <!-- Pagination Footer -->
                <div class="px-8 py-5 border-t border-slate-100">
                    {{ $students->links('pagination::tailwind') }}
                </div>
            </div>

            <!-- Add Student Modal Overlay -->
            <div id="add-student-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
                <!-- Modal Card -->
                <div class="bg-white rounded-[32px] w-[620px] max-w-full p-8 shadow-2xl relative transform scale-95 transition-transform duration-300">
                    <!-- Close Button -->
                    <button id="close-modal-btn" class="absolute top-8 right-8 text-slate-400 hover:text-slate-600 cursor-pointer select-none outline-none">
                        <span class="material-symbols-outlined text-[24px]">close</span>
                    </button>

                    <!-- Header -->
                    <div class="mb-6">
                        <h2 class="text-[26px] font-bold text-[#0d326b] mb-1">Add New Students</h2>
                        <p class="text-[14px] text-slate-500 font-medium">Populate your classroom</p>
                    </div>

                    <!-- Tabs -->
                    <div class="flex space-x-6 border-b border-slate-100 mb-6 text-[13px] font-bold tracking-wider uppercase">
                        <button id="tab-single" class="text-[#0d326b] border-b-2 border-[#0d326b] pb-3 cursor-pointer outline-none transition-all">
                            SINGLE STUDENT
                        </button>
                        <button id="tab-bulk" class="text-slate-400 border-b-2 border-transparent pb-3 cursor-pointer hover:text-slate-600 outline-none transition-all">
                            BULK ADD (EXCEL)
                        </button>
                    </div>

                    <!-- Alert Box for Feedback -->
                    <div id="modal-alert" class="hidden mb-5 p-4 rounded-xl text-sm font-medium flex items-start space-x-2 border">
                        <span id="modal-alert-icon" class="material-symbols-outlined text-[20px] mt-0.5"></span>
                        <div id="modal-alert-message" class="flex-1"></div>
                    </div>

                    <!-- Tab Content: Single Student -->
                    <form id="form-single" class="block" onsubmit="submitSingleStudent(event)">
                        @csrf
                        <div class="grid grid-cols-2 gap-x-6 gap-y-5 mb-6">
                            <!-- LRN -->
                            <div class="flex flex-col space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">LEARNER REFERENCE NUMBER (LRN)</label>
                                <input type="text" name="lrn" required placeholder="12-digit LRN" pattern="\d{12}" maxlength="12" title="LRN must be exactly 12 digits" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                            </div>
                            <!-- Full Name -->
                            <div class="flex flex-col space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">FULL NAME</label>
                                <input type="text" name="full_name" required placeholder="Last Name, First Name" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                            </div>
                            <!-- Grade Level -->
                            <div class="flex flex-col space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">GRADE LEVEL</label>
                                <div class="relative">
                                    <select name="grade_level" class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
                                        <option value="">Select Grade</option>
                                        <option value="Grade 1">Grade 1</option>
                                        <option value="Grade 2">Grade 2</option>
                                        <option value="Grade 3">Grade 3</option>
                                        <option value="Grade 4">Grade 4</option>
                                        <option value="Grade 5">Grade 5</option>
                                        <option value="Grade 6">Grade 6</option>
                                        <option value="SPED A">SPED A</option>
                                        <option value="SPED B">SPED B</option>
                                    </select>
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                                </div>
                            </div>
                            <!-- Age -->
                            <div class="flex flex-col space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">AGE</label>
                                <input type="number" name="age" required min="1" max="100" placeholder="Enter age" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                            </div>
                            <!-- Section -->
                            <div class="flex flex-col space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SECTION</label>
                                <input type="text" name="section" placeholder="e.g. SPED-A" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                            </div>
                            <!-- FSL Mastery Level -->
                            <div class="flex flex-col space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">FSL MASTERY LEVEL</label>
                                <div class="relative">
                                    <select name="fsl_mastery_level" class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
                                        <option value="Beginner">Beginner</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Advanced">Advanced</option>
                                    </select>
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                                </div>
                            </div>
                        </div>

                        <!-- Auto-generate PIN Panel -->
                        <div class="bg-[#f1f5f9] p-4 rounded-[20px] flex items-center justify-between mb-8 shadow-sm">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#0d326b] shadow-sm">
                                    <span class="material-symbols-outlined text-[20px]">lock</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[14px] font-bold text-[#0d326b]">Auto-generate Student PIN</span>
                                    <span class="text-[11px] text-slate-400 font-medium mt-0.5">Default: Student LRN</span>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="auto_pin" value="1" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <button type="button" class="btn-cancel px-6 py-3 text-slate-500 hover:text-slate-800 font-semibold text-[14px] transition-colors cursor-pointer select-none">
                                Cancel
                            </button>
                            <button type="submit" id="btn-single-submit" class="bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-colors cursor-pointer flex items-center justify-center">
                                Save Student
                            </button>
                        </div>
                    </form>

                    <!-- Tab Content: Bulk Add -->
                    <div id="container-bulk" class="hidden">
                        <!-- Drop Zone -->
                        <div id="drop-zone" class="border-2 border-dashed border-slate-300 hover:border-[#0d326b] rounded-[24px] p-10 flex flex-col items-center justify-center space-y-4 mb-6 transition-all cursor-pointer relative bg-slate-50/50">
                            <input type="file" id="excel-file" accept=".xlsx, .xls, .csv" class="absolute inset-0 opacity-0 cursor-pointer" />
                            <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 shadow-sm" id="upload-icon-container">
                                <span class="material-symbols-outlined text-[28px]" id="upload-icon">article</span>
                            </div>
                            <div class="text-center" id="upload-text-container">
                                <p class="text-[15px] font-bold text-slate-700" id="upload-primary-text">Drag and drop your student roster file here</p>
                                <p class="text-[12px] text-slate-400 mt-1" id="upload-secondary-text">.xlsx only, max 5MB</p>
                            </div>
                            <button type="button" id="browse-btn" class="border border-[#0d326b] text-[#0d326b] hover:bg-[#0d326b]/5 font-bold text-[13px] px-6 py-2.5 rounded-xl transition-colors pointer-events-none">
                                Browse Files
                            </button>
                        </div>

                        <!-- Auto-generate PINs Panel -->
                        <div class="bg-[#f1f5f9] p-4 rounded-[20px] flex items-center justify-between mb-8 shadow-sm">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#0d326b] shadow-sm">
                                    <span class="material-symbols-outlined text-[20px]">lock</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[14px] font-bold text-[#0d326b]">Auto-generate Student PINs</span>
                                    <span class="text-[11px] text-slate-400 font-medium mt-0.5">Apply to all imported students (6-digit code derived from LRN)</span>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="bulk-auto-pin" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <button type="button" class="btn-cancel px-6 py-3 text-slate-500 hover:text-slate-800 font-semibold text-[14px] transition-colors cursor-pointer select-none">
                                Cancel
                            </button>
                            <button type="button" id="btn-import-submit" disabled class="bg-slate-300 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-not-allowed flex items-center justify-center">
                                Confirm Import
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load Axios and SheetJS for client-side AJAX & Excel Parsing -->
            <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

            <script>
                // Modal elements
                const openModalBtn = document.getElementById('open-modal-btn');
                const closeModalBtn = document.getElementById('close-modal-btn');
                const cancelModalBtns = document.querySelectorAll('.btn-cancel');
                const addStudentModal = document.getElementById('add-student-modal');
                const modalCard = addStudentModal.querySelector('.bg-white');

                // Tabs
                const tabSingle = document.getElementById('tab-single');
                const tabBulk = document.getElementById('tab-bulk');
                const formSingle = document.getElementById('form-single');
                const containerBulk = document.getElementById('container-bulk');

                // Alert
                const modalAlert = document.getElementById('modal-alert');
                const modalAlertIcon = document.getElementById('modal-alert-icon');
                const modalAlertMessage = document.getElementById('modal-alert-message');

                // File Upload elements
                const dropZone = document.getElementById('drop-zone');
                const excelFileInput = document.getElementById('excel-file');
                const btnImportSubmit = document.getElementById('btn-import-submit');
                const uploadIcon = document.getElementById('upload-icon');
                const uploadIconContainer = document.getElementById('upload-icon-container');
                const uploadPrimaryText = document.getElementById('upload-primary-text');
                const uploadSecondaryText = document.getElementById('upload-secondary-text');
                const browseBtn = document.getElementById('browse-btn');

                let parsedStudents = [];

                // Open Modal
                openModalBtn.addEventListener('click', () => {
                    addStudentModal.classList.remove('hidden');
                    setTimeout(() => {
                        addStudentModal.classList.remove('opacity-0');
                        modalCard.classList.remove('scale-95');
                    }, 50);
                });

                // Close Modal
                function closeModal() {
                    addStudentModal.classList.add('opacity-0');
                    modalCard.classList.add('scale-95');
                    setTimeout(() => {
                        addStudentModal.classList.add('hidden');
                        resetModal();
                    }, 300);
                }

                closeModalBtn.addEventListener('click', closeModal);
                cancelModalBtns.forEach(btn => btn.addEventListener('click', closeModal));

                // Switch tabs
                tabSingle.addEventListener('click', () => {
                    // Styles
                    tabSingle.className = "text-[#0d326b] border-b-2 border-[#0d326b] pb-3 cursor-pointer outline-none transition-all";
                    tabBulk.className = "text-slate-400 border-b-2 border-transparent pb-3 cursor-pointer hover:text-slate-600 outline-none transition-all";
                    // Display
                    formSingle.classList.remove('hidden');
                    containerBulk.classList.add('hidden');
                });

                tabBulk.addEventListener('click', () => {
                    // Styles
                    tabBulk.className = "text-[#0d326b] border-b-2 border-[#0d326b] pb-3 cursor-pointer outline-none transition-all";
                    tabSingle.className = "text-slate-400 border-b-2 border-transparent pb-3 cursor-pointer hover:text-slate-600 outline-none transition-all";
                    // Display
                    containerBulk.classList.remove('hidden');
                    formSingle.classList.add('hidden');
                });

                // Reset forms, inputs, alert states
                function resetModal() {
                    formSingle.reset();
                    parsedStudents = [];
                    resetUploadArea();
                    hideAlert();
                    // Default to single student tab
                    tabSingle.click();
                }

                // Alert feedback helper
                function showAlert(message, type = 'error') {
                    modalAlert.classList.remove('hidden', 'bg-red-50', 'border-red-200', 'text-red-800', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800');
                    modalAlertIcon.innerText = type === 'error' ? 'error' : 'check_circle';
                    
                    if (type === 'error') {
                        modalAlert.classList.add('bg-red-50', 'border-red-200', 'text-red-800');
                    } else {
                        modalAlert.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-800');
                    }
                    modalAlertMessage.innerHTML = message;
                }

                function hideAlert() {
                    modalAlert.classList.add('hidden');
                }

                // Drag and Drop behavior
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        dropZone.classList.add('border-[#0d326b]', 'bg-[#0d326b]/5');
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        dropZone.classList.remove('border-[#0d326b]', 'bg-[#0d326b]/5');
                    }, false);
                });

                dropZone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    const file = dt.files[0];
                    if (file) {
                        excelFileInput.files = dt.files;
                        handleExcelFile(file);
                    }
                });

                excelFileInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        handleExcelFile(file);
                    }
                });

                // Parse Excel using SheetJS
                function handleExcelFile(file) {
                    hideAlert();
                    if (!file.name.endsWith('.xlsx') && !file.name.endsWith('.xls') && !file.name.endsWith('.csv')) {
                        showAlert('Invalid file format. Please upload an Excel or CSV file (.xlsx, .xls, or .csv).');
                        resetUploadArea();
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        try {
                            const data = new Uint8Array(e.target.result);
                            const workbook = XLSX.read(data, { type: 'array' });
                            const firstSheetName = workbook.SheetNames[0];
                            const worksheet = workbook.Sheets[firstSheetName];
                            const json = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

                            parsedStudents = mapExcelData(json);
                            
                            if (parsedStudents.length === 0) {
                                showAlert('The Excel file is empty or contains no student rows.');
                                resetUploadArea();
                                return;
                            }

                            // Show success upload view
                            showUploadedFile(file.name, parsedStudents.length);
                        } catch (err) {
                            showAlert(err.message || 'Failed to parse Excel file. Make sure columns match expectations.');
                            resetUploadArea();
                        }
                    };
                    reader.readAsArrayBuffer(file);
                }

                function showUploadedFile(filename, count) {
                    // Update upload container visual to show loaded state
                    uploadIcon.innerText = "check";
                    uploadIconContainer.className = "w-14 h-14 bg-emerald-500 rounded-full flex items-center justify-center text-white shadow-sm";
                    uploadPrimaryText.innerText = filename;
                    uploadSecondaryText.innerText = `${count} students detected. Ready to import.`;
                    
                    // Enable confirm import button
                    btnImportSubmit.removeAttribute('disabled');
                    btnImportSubmit.className = "bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-pointer flex items-center justify-center";
                }

                function resetUploadArea() {
                    excelFileInput.value = '';
                    uploadIcon.innerText = "article";
                    uploadIconContainer.className = "w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 shadow-sm";
                    uploadPrimaryText.innerText = "Drag and drop your student roster file here";
                    uploadSecondaryText.innerText = ".xlsx or .csv only, max 5MB";
                    
                    // Disable confirm import button
                    btnImportSubmit.setAttribute('disabled', 'true');
                    btnImportSubmit.className = "bg-slate-300 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-not-allowed flex items-center justify-center";
                    parsedStudents = [];
                }

                // Map Excel data to structure
                function mapExcelData(rows) {
                    if (!rows || rows.length < 2) return [];
                    
                    // Find headers (clean row 0)
                    const headers = rows[0].map(h => String(h || '').trim().toLowerCase());
                    
                    const lrnIdx = headers.findIndex(h => h.includes('lrn') || h.includes('reference') || h.includes('learner'));
                    const nameIdx = headers.findIndex(h => h.includes('name') || h.includes('student') || h.includes('full'));
                    const firstNameIdx = headers.findIndex(h => h.includes('first'));
                    const lastNameIdx = headers.findIndex(h => h.includes('last'));
                    const gradeIdx = headers.findIndex(h => h.includes('grade') || h.includes('level') || h.includes('class'));
                    const ageIdx = headers.findIndex(h => h.includes('age'));
                    const sectionIdx = headers.findIndex(h => h.includes('section'));
                    const masteryIdx = headers.findIndex(h => h.includes('fsl') || h.includes('mastery') || h.includes('skill'));

                    if (lrnIdx === -1) {
                        throw new Error("Missing LRN column. Make sure your Excel sheet contains a header named 'LRN' or 'Learner Reference Number'.");
                    }
                    if (nameIdx === -1 && (firstNameIdx === -1 || lastNameIdx === -1)) {
                        throw new Error("Missing Name column. Make sure your Excel sheet contains a header named 'Full Name' (formatted 'Last Name, First Name') or split 'First Name' and 'Last Name' columns.");
                    }
                    if (ageIdx === -1) {
                        throw new Error("Missing Age column. Make sure your Excel sheet contains a header named 'Age'.");
                    }

                    const students = [];
                    for (let i = 1; i < rows.length; i++) {
                        const row = rows[i];
                        if (!row || row.length === 0) continue;
                        
                        // Check if row is mostly empty
                        if (!row[lrnIdx] && !row[nameIdx] && !row[firstNameIdx]) continue;

                        const lrn = String(row[lrnIdx] || '').trim();
                        let fullName = '';
                        if (nameIdx !== -1) {
                            fullName = String(row[nameIdx] || '').trim();
                        } else {
                            const fName = String(row[firstNameIdx] || '').trim();
                            const lName = String(row[lastNameIdx] || '').trim();
                            fullName = `${lName}, ${fName}`;
                        }

                        const age = parseInt(row[ageIdx], 10);
                        const grade_level = gradeIdx !== -1 ? String(row[gradeIdx] || '').trim() : '';
                        const section = sectionIdx !== -1 ? String(row[sectionIdx] || '').trim() : '';
                        
                        let fsl_mastery_level = 'Beginner';
                        if (masteryIdx !== -1) {
                            const rawMastery = String(row[masteryIdx] || '').trim().toLowerCase();
                            if (rawMastery.includes('inter')) fsl_mastery_level = 'Intermediate';
                            else if (rawMastery.includes('adv')) fsl_mastery_level = 'Advanced';
                        }

                        if (!lrn || lrn.length !== 12 || isNaN(lrn)) {
                            throw new Error(`Row ${i + 1}: LRN "${lrn}" must be exactly a 12-digit number.`);
                        }
                        if (!fullName) {
                            throw new Error(`Row ${i + 1}: Student name is required.`);
                        }
                        if (isNaN(age) || age < 1) {
                            throw new Error(`Row ${i + 1}: Valid Age is required.`);
                        }

                        students.push({
                            lrn: lrn,
                            full_name: fullName,
                            grade_level: grade_level || null,
                            age: age,
                            section: section || null,
                            fsl_mastery_level: fsl_mastery_level
                        });
                    }

                    return students;
                }

                // AJAX: Save Single Student Manual Form
                async function submitSingleStudent(event) {
                    event.preventDefault();
                    hideAlert();
                    const submitBtn = document.getElementById('btn-single-submit');
                    
                    // Validate name format: must contain a comma
                    const fullNameInput = formSingle.querySelector('input[name="full_name"]').value;
                    if (!fullNameInput.includes(',')) {
                        showAlert('Full Name must be formatted as "Last Name, First Name" (separated by a comma).');
                        return;
                    }

                    const originalText = submitBtn.innerText;
                    submitBtn.innerText = 'Saving...';
                    submitBtn.disabled = true;

                    const formData = new FormData(formSingle);
                    const payload = {
                        lrn: formData.get('lrn'),
                        full_name: formData.get('full_name'),
                        grade_level: formData.get('grade_level'),
                        age: formData.get('age'),
                        section: formData.get('section'),
                        fsl_mastery_level: formData.get('fsl_mastery_level'),
                        auto_pin: formData.get('auto_pin') ? 1 : 0
                    };

                    const token = formSingle.querySelector('input[name="_token"]').value;

                    try {
                        const response = await axios.post("{{ route('students.store') }}", payload, {
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            }
                        });
                        if (response.data.success) {
                            showAlert(response.data.message, 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showAlert(response.data.message || 'An error occurred while saving the student.');
                            submitBtn.innerText = originalText;
                            submitBtn.disabled = false;
                        }
                    } catch (error) {
                        console.error('Save error details:', error);
                        let msg = 'An error occurred while saving.';
                        if (error.response) {
                            if (error.response.data) {
                                if (error.response.data.errors) {
                                    msg = Object.values(error.response.data.errors).flat().join('<br>');
                                } else if (error.response.data.message) {
                                    msg = error.response.data.message;
                                } else {
                                    msg = `Server Error (${error.response.status}): ${error.message}`;
                                }
                            } else {
                                msg = `Server Response Error: ${error.response.statusText} (${error.response.status})`;
                            }
                        } else if (error.request) {
                            msg = `Network Connection Failed. Status: ${error.message}. Please check if the web server is running.`;
                        } else {
                            msg = `Request Error: ${error.message}`;
                        }
                        showAlert(msg);
                        submitBtn.innerText = originalText;
                        submitBtn.disabled = false;
                    }
                }

                // AJAX: Bulk Import Confirm
                btnImportSubmit.addEventListener('click', async () => {
                    hideAlert();
                    const originalText = btnImportSubmit.innerText;
                    btnImportSubmit.innerText = 'Importing...';
                    btnImportSubmit.disabled = true;

                    const autoPinChecked = document.getElementById('bulk-auto-pin').checked;

                    const payload = {
                        students: parsedStudents,
                        auto_pin: autoPinChecked ? 1 : 0
                    };

                    const token = formSingle.querySelector('input[name="_token"]').value;

                    try {
                        const response = await axios.post("{{ route('students.import') }}", payload, {
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            }
                        });
                        if (response.data.success) {
                            showAlert(response.data.message, 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showAlert(response.data.message || 'An error occurred during bulk import.');
                            btnImportSubmit.innerText = originalText;
                            btnImportSubmit.disabled = false;
                        }
                    } catch (error) {
                        console.error('Import error details:', error);
                        let msg = 'An error occurred during import.';
                        if (error.response) {
                            if (error.response.data) {
                                if (error.response.data.errors) {
                                    msg = Object.values(error.response.data.errors).flat().join('<br>');
                                } else if (error.response.data.message) {
                                    msg = error.response.data.message;
                                } else {
                                    msg = `Server Error (${error.response.status}): ${error.message}`;
                                }
                            } else {
                                msg = `Server Response Error: ${error.response.statusText} (${error.response.status})`;
                            }
                        } else if (error.request) {
                            msg = `Network Connection Failed. Status: ${error.message}. Please check if the web server is running.`;
                        } else {
                            msg = `Request Error: ${error.message}`;
                        }
                        showAlert(msg);
                        btnImportSubmit.innerText = originalText;
                        btnImportSubmit.disabled = false;
                    }
                });
            </script>
@endsection
