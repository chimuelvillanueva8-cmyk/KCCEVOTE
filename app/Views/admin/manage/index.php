<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>
<main class="department-management">
<div class="page-header">
    <div class="page-title-group">
        <h1 class="page-title">Department &amp; Course Management</h1>
        <p class="page-copy">Manage departments and the courses available under each department.</p>
    </div>
</div>

<div class="department-management-grid">
    <section class="card management-card">
        <div class="card-header management-card-header">
            <span class="card-title">Departments</span>
            <button type="button" id="addDeptBtn" class="btn btn-primary" onclick="openDepartmentModal('add')">+ Add Department</button>
        </div>
        <div class="card-body">
            <div style="margin-bottom: 14px;">
                <input id="deptSearch" type="search" class="form-control" placeholder="Search department..." />
            </div>
            <div class="table-responsive">
                <table class="student-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Courses</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="departmentsTableBody"></tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="card management-card">
        <div class="card-header management-card-header">
            <span class="card-title">Courses</span>
            <button type="button" id="addCourseBtn" class="btn btn-primary" onclick="openCourseModal('add')">+ Add Course</button>
        </div>
        <div class="card-body">
            <div class="course-management-filters">
                <input id="courseSearch" type="search" class="form-control" placeholder="Search course..." />
                <select id="courseDepartmentFilter" class="form-control">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="student-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="coursesTableBody"></tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<div id="departmentModal" class="modal" style="display:none;">
    <div class="modal-content card register-modal" data-draggable-modal="true" style="max-width: 640px; width: min(92vw, 640px); margin: 8vh auto; padding: 22px 22px 18px; border-radius: 18px; border: 1px solid rgba(15, 23, 42, 0.08); box-shadow: 0 22px 60px rgba(15, 23, 42, 0.18); background: #fff; position: relative; cursor: default;">
        <h3 id="departmentModalTitle" data-drag-handle="true" style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #1f2937; cursor: move; user-select: none; touch-action: none;">Add Department</h3>
        <form id="departmentForm" method="post" enctype="multipart/form-data" style="margin-top: 16px;">
            <?= csrf_field() ?>
            <div style="margin-top:12px;">
                <label>Department Name</label>
                <input id="department_name" name="department_name" class="form-control" required>
            </div>
            <div style="margin-top:12px;">
                <label>Status</label>
                <select id="department_status" name="status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="management-modal-actions">
                <button type="button" id="departmentCancel" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Department</button>
            </div>
        </form>
    </div>
</div>

<div id="courseModal" class="modal" style="display:none;">
    <div class="modal-content card register-modal" data-draggable-modal="true" style="max-width: 640px; width: min(92vw, 640px); margin: 0 auto; padding: 22px 22px 18px; border-radius: 18px; border: 1px solid rgba(15, 23, 42, 0.08); box-shadow: 0 22px 60px rgba(15, 23, 42, 0.18); background: #fff; position: relative; cursor: default;">
        <h3 id="courseModalTitle" data-drag-handle="true" style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #1f2937; cursor: move; user-select: none; touch-action: none;">Add Course</h3>
        <form id="courseForm" method="post" enctype="multipart/form-data" style="margin-top: 16px;">
            <?= csrf_field() ?>
            <div style="margin-top:12px;">
                <label>Course Name</label>
                <input id="course_name" name="course_name" class="form-control" placeholder="Enter course name" required>
            </div>
            <div style="margin-top:12px;">
                <label>Department</label>
                <select id="course_department" name="department_id" class="form-control" required>
                    <option value="">Select Department</option>
                </select>
            </div>
            <div style="margin-top:12px;">
                <label>Status</label>
                <select id="course_status" name="status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="management-modal-actions">
                <button type="button" id="courseCancel" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Course</button>
            </div>
        </form>
    </div>
</div>

<div id="managementNotice" class="management-notice" hidden>
    <section class="management-notice-dialog" role="alertdialog" aria-modal="true" aria-labelledby="managementNoticeTitle" aria-describedby="managementNoticeMessage">
        <div class="management-notice-icon" aria-hidden="true"><i class="bi bi-info-circle-fill"></i></div>
        <h2 id="managementNoticeTitle">Unable to complete action</h2>
        <p id="managementNoticeMessage"></p>
        <div class="management-notice-actions">
            <button type="button" id="managementNoticeCancel" class="btn btn-secondary" hidden>Cancel</button>
            <button type="button" id="managementNoticeClose" class="btn btn-primary">Got it</button>
        </div>
    </section>
</div>

<script>
const deptApi = '<?= base_url('admin/departments/all') ?>';
const deptListApi = '<?= base_url('admin/departments/list') ?>';
const courseApi = '<?= base_url('admin/courses/all') ?>';
let departmentList = [];
let courseList = [];

function showManagementNotice(message, title = 'Unable to complete action') {
    const notice = document.getElementById('managementNotice');
    const noticeTitle = document.getElementById('managementNoticeTitle');
    const noticeMessage = document.getElementById('managementNoticeMessage');
    const cancelButton = document.getElementById('managementNoticeCancel');
    const closeButton = document.getElementById('managementNoticeClose');
    if (!notice || !noticeTitle || !noticeMessage || !cancelButton || !closeButton) return;

    noticeTitle.textContent = title;
    noticeMessage.textContent = message;
    cancelButton.hidden = true;
    closeButton.textContent = 'Got it';
    closeButton.classList.remove('is-danger');
    notice.hidden = false;
    const previousFocus = document.activeElement;
    closeButton.focus();

    const close = () => {
        notice.hidden = true;
        closeButton.removeEventListener('click', close);
        cancelButton.removeEventListener('click', close);
        notice.removeEventListener('click', onBackdropClick);
        document.removeEventListener('keydown', onKeyDown);
        if (previousFocus instanceof HTMLElement) previousFocus.focus();
    };
    const onBackdropClick = event => {
        if (event.target === notice) close();
    };
    const onKeyDown = event => {
        if (event.key === 'Escape') {
            event.preventDefault();
            close();
        } else if (event.key === 'Tab') {
            event.preventDefault();
            closeButton.focus();
        }
    };

    closeButton.addEventListener('click', close);
    cancelButton.addEventListener('click', close);
    notice.addEventListener('click', onBackdropClick);
    document.addEventListener('keydown', onKeyDown);
}

function confirmManagementAction(title, message, confirmLabel = 'Delete') {
    const notice = document.getElementById('managementNotice');
    const noticeTitle = document.getElementById('managementNoticeTitle');
    const noticeMessage = document.getElementById('managementNoticeMessage');
    const cancelButton = document.getElementById('managementNoticeCancel');
    const confirmButton = document.getElementById('managementNoticeClose');
    const icon = document.querySelector('.management-notice-icon');
    if (!notice || !noticeTitle || !noticeMessage || !cancelButton || !confirmButton || !icon) {
        return Promise.resolve(false);
    }

    noticeTitle.textContent = title;
    noticeMessage.textContent = message;
    cancelButton.hidden = false;
    confirmButton.textContent = confirmLabel;
    confirmButton.classList.add('is-danger');
    icon.classList.add('is-danger');
    notice.hidden = false;
    const previousFocus = document.activeElement;
    cancelButton.focus();

    return new Promise(resolve => {
        let resolved = false;
        const finish = confirmed => {
            if (resolved) return;
            resolved = true;
            notice.hidden = true;
            cancelButton.removeEventListener('click', onCancel);
            confirmButton.removeEventListener('click', onConfirm);
            notice.removeEventListener('click', onBackdropClick);
            document.removeEventListener('keydown', onKeyDown);
            cancelButton.hidden = true;
            confirmButton.textContent = 'Got it';
            confirmButton.classList.remove('is-danger');
            icon.classList.remove('is-danger');
            if (previousFocus instanceof HTMLElement) previousFocus.focus();
            resolve(confirmed);
        };
        const onCancel = () => finish(false);
        const onConfirm = () => finish(true);
        const onBackdropClick = event => {
            if (event.target === notice) finish(false);
        };
        const onKeyDown = event => {
            if (event.key === 'Escape') {
                event.preventDefault();
                finish(false);
            } else if (event.key === 'Tab') {
                event.preventDefault();
                (document.activeElement === cancelButton ? confirmButton : cancelButton).focus();
            }
        };

        cancelButton.addEventListener('click', onCancel);
        confirmButton.addEventListener('click', onConfirm);
        notice.addEventListener('click', onBackdropClick);
        document.addEventListener('keydown', onKeyDown);
    });
}

async function fetchJson(url) {
    const res = await fetch(url);
    return res.ok ? res.json() : [];
}

function departmentStatusChip(status) {
    const isActive = String(status).toLowerCase() === 'active';
    return `<span class="badge ${isActive ? 'badge-success' : 'badge-warning'}">${status || 'Inactive'}</span>`;
}

function courseStatusChip(status) {
    const isActive = String(status).toLowerCase() === 'active';
    return `<span class="badge ${isActive ? 'badge-success' : 'badge-warning'}">${status || 'Inactive'}</span>`;
}

async function loadDepartmentManagement() {
    departmentList = await fetchJson(deptApi);
    const filter = document.getElementById('deptSearch').value.trim().toLowerCase();
    const tbody = document.getElementById('departmentsTableBody');
    const rows = departmentList.filter(dept => {
        const text = `${dept.department_name || ''}`.toLowerCase();
        return text.includes(filter);
    });

    tbody.innerHTML = rows.length ? rows.map(dept => `
        <tr>
            <td>${dept.department_name || '—'}</td>
            <td>${Number(dept.course_count || 0)} ${Number(dept.course_count || 0) === 1 ? 'Course' : 'Courses'}</td>
            <td>${departmentStatusChip(dept.status)}</td>
            <td>
                <div class="management-actions">
                    <button type="button" class="btn btn-sm btn-secondary" data-action="dept-edit" data-id="${dept.id}">Edit</button>
                    <button type="button" class="btn btn-sm btn-danger" data-action="dept-delete" data-id="${dept.id}">Delete</button>
                </div>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="5">No departments found.</td></tr>';

    const deptSelect = document.getElementById('course_department');
    if (deptSelect) {
        const activeDepartments = departmentList.filter(dept => dept.status === 'Active');
        const selectedDepartment = deptSelect.value || document.getElementById('courseDepartmentFilter')?.value || '';
        deptSelect.innerHTML = '<option value="">Select Department</option>' + activeDepartments.map(dept => `
            <option value="${dept.id}">${dept.department_name}</option>
        `).join('');
        if (selectedDepartment && [...deptSelect.options].some(opt => opt.value === selectedDepartment)) {
            deptSelect.value = selectedDepartment;
        }
    }

    const filterSelect = document.getElementById('courseDepartmentFilter');
    if (filterSelect) {
        const selectedFilter = filterSelect.value || '';
        filterSelect.innerHTML = '<option value="">All Departments</option>' + departmentList.map(dept => `
            <option value="${dept.id}">${dept.department_name}</option>
        `).join('');
        if (selectedFilter && [...filterSelect.options].some(opt => opt.value === selectedFilter)) {
            filterSelect.value = selectedFilter;
        }
    }
}

async function loadCourseManagement() {
    courseList = await fetchJson(courseApi);
    const search = document.getElementById('courseSearch').value.trim().toLowerCase();
    const filterSelect = document.getElementById('courseDepartmentFilter');
    const selectedDepartment = filterSelect ? filterSelect.value : '';
    const tbody = document.getElementById('coursesTableBody');

    const rows = courseList.filter(course => {
        const matchesDepartment = !selectedDepartment || String(course.department_id) === String(selectedDepartment);
        const matchesSearch = `${course.course_name || ''} ${course.department_name || ''}`.toLowerCase().includes(search);
        return matchesDepartment && matchesSearch;
    });

    tbody.innerHTML = rows.length ? rows.map(course => `
        <tr>
            <td>${course.course_name || '—'}</td>
            <td>${course.department_name || 'Unassigned'}</td>
            <td>${courseStatusChip(course.status)}</td>
            <td>
                <div class="management-actions">
                    <button type="button" class="btn btn-sm btn-secondary" data-action="course-edit" data-id="${course.id}">Edit</button>
                    <button type="button" class="btn btn-sm btn-danger" data-action="course-delete" data-id="${course.id}">Delete</button>
                </div>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="4">No courses found.</td></tr>';
}

async function submitFormWithJson(form, apiUrl) {
    const formData = new FormData(form);
    const response = await fetch(apiUrl, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    return response.ok ? response.json() : { success: false, message: 'Request failed.' };
}

async function handleDepartmentSubmit(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const apiUrl = form.dataset.action || '<?= base_url('admin/departments/store') ?>';
    const result = await submitFormWithJson(form, apiUrl);

    if (!result.success) {
        const text = result.message || (result.errors ? JSON.stringify(result.errors) : 'Department save failed.');
        alert(text);
        return;
    }

    form.reset();
    const deptModal = document.getElementById('departmentModal');
    deptModal.classList.remove('active');
    deptModal.style.setProperty('display', 'none', 'important');
    deptModal.style.visibility = 'hidden';
    deptModal.style.opacity = '0';
    await loadDepartmentManagement();
    await loadCourseManagement();
}

async function handleCourseSubmit(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const apiUrl = form.dataset.action || '<?= base_url('admin/courses/store') ?>';
    const result = await submitFormWithJson(form, apiUrl);

    if (!result.success) {
        const text = result.message || (result.errors ? JSON.stringify(result.errors) : 'Course save failed.');
        alert(text);
        return;
    }

    form.reset();
    const courseModal = document.getElementById('courseModal');
    courseModal.classList.remove('active');
    courseModal.style.setProperty('display', 'none', 'important');
    courseModal.style.visibility = 'hidden';
    courseModal.style.opacity = '0';
    await loadDepartmentManagement();
    await loadCourseManagement();
}

async function openDepartmentModal(mode, departmentId = null) {
    const modal = document.getElementById('departmentModal');
    const form = document.getElementById('departmentForm');
    const title = document.getElementById('departmentModalTitle');
    const inputs = {
        name: document.getElementById('department_name'),
        status: document.getElementById('department_status')
    };

    if (!modal || !form || !title || !inputs.name || !inputs.status) {
        return;
    }

    if (mode === 'edit' && departmentId) {
        const dept = departmentList.find(item => String(item.id) === String(departmentId));
        if (!dept) return;
        title.textContent = 'Edit Department';
        form.dataset.action = `<?= base_url('admin/departments/update') ?>/${dept.id}`;
        inputs.name.value = dept.department_name || '';
        inputs.status.value = dept.status || 'Active';
    } else {
        title.textContent = 'Add Department';
        form.dataset.action = '<?= base_url('admin/departments/store') ?>';
        form.reset();
        inputs.name.value = '';
        inputs.status.value = 'Active';
    }

    modal.classList.add('active');
    modal.style.setProperty('display', 'flex', 'important');
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
}

async function openCourseModal(mode, courseId = null) {
    const modal = document.getElementById('courseModal');
    const form = document.getElementById('courseForm');
    const title = document.getElementById('courseModalTitle');
    const inputs = {
        name: document.getElementById('course_name'),
        department: document.getElementById('course_department'),
        status: document.getElementById('course_status')
    };

    if (!modal || !form || !title || !inputs.name || !inputs.department || !inputs.status) {
        return;
    }

    await loadDepartmentManagement();

    const selectedDepartment = document.getElementById('courseDepartmentFilter')?.value || '';

    if (mode === 'edit' && courseId) {
        const course = courseList.find(item => String(item.id) === String(courseId));
        if (!course) return;
        title.textContent = 'Edit Course';
        form.dataset.action = `<?= base_url('admin/courses/update') ?>/${course.id}`;
        inputs.name.value = course.course_name || '';
        inputs.department.value = course.department_id || selectedDepartment || '';
        inputs.status.value = course.status || 'Active';
    } else {
        title.textContent = 'Add Course';
        form.dataset.action = '<?= base_url('admin/courses/store') ?>';
        form.reset();
        inputs.name.value = '';
        inputs.department.value = selectedDepartment || '';
        inputs.status.value = 'Active';
    }

    modal.classList.add('active');
    modal.style.setProperty('display', 'flex', 'important');
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
}

async function deleteDepartment(departmentId) {
    const department = departmentList.find(item => String(item.id) === String(departmentId));
    if (!department) return;
    if (Number(department.course_count || 0) > 0) {
        const courseCount = Number(department.course_count);
        showManagementNotice(
            `This department has ${courseCount} ${courseCount === 1 ? 'course' : 'courses'} assigned. Reassign or delete ${courseCount === 1 ? 'it' : 'them'} before deleting the department.`,
            "Department can't be deleted yet"
        );
        return;
    }

    if (!await confirmManagementAction(
        'Delete this department?',
        `Delete "${department.department_name}"? This action cannot be undone.`
    )) return;

    const response = await fetch(`<?= base_url('admin/departments/delete') ?>/${departmentId}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const result = await response.json();

    if (!result.success) {
        showManagementNotice(result.message || 'Unable to delete department.', 'Department not deleted');
        return;
    }

    await loadDepartmentManagement();
    await loadCourseManagement();
}

async function deleteCourse(courseId) {
    const course = courseList.find(item => String(item.id) === String(courseId));
    const courseName = course?.course_name || 'this course';
    if (!await confirmManagementAction(
        'Delete this course?',
        `Delete "${courseName}"? This action cannot be undone.`
    )) return;

    const response = await fetch(`<?= base_url('admin/courses/delete') ?>/${courseId}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const result = await response.json();

    if (!result.success) {
        showManagementNotice(result.message || 'Unable to delete course.', 'Course not deleted');
        return;
    }

    await loadDepartmentManagement();
    await loadCourseManagement();
}

window.openDepartmentModal = openDepartmentModal;
window.openCourseModal = openCourseModal;

function enableDraggableModal(modalElement) {
    const handle = modalElement.querySelector('[data-drag-handle="true"]');
    if (!handle) return;

    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let startLeft = 0;
    let startTop = 0;

    const stopDragging = () => {
        isDragging = false;
        handle.style.cursor = 'move';
        modalElement.style.cursor = 'default';
    };

    handle.addEventListener('pointerdown', function (event) {
        if (event.target.closest('button, input, select, textarea, label')) {
            return;
        }

        isDragging = true;
        const rect = modalElement.getBoundingClientRect();
        startX = event.clientX;
        startY = event.clientY;
        startLeft = rect.left;
        startTop = rect.top;

        modalElement.style.position = 'absolute';
        modalElement.style.margin = '0';
        modalElement.style.left = `${startLeft}px`;
        modalElement.style.top = `${startTop}px`;
        modalElement.style.transform = 'none';
        modalElement.style.cursor = 'grabbing';
        handle.style.cursor = 'grabbing';
        handle.setPointerCapture(event.pointerId);
    });

    handle.addEventListener('pointermove', function (event) {
        if (!isDragging) return;

        const deltaX = event.clientX - startX;
        const deltaY = event.clientY - startY;
        const maxLeft = Math.max(0, window.innerWidth - modalElement.offsetWidth);
        const maxTop = Math.max(0, window.innerHeight - modalElement.offsetHeight);

        const nextLeft = Math.min(Math.max(0, startLeft + deltaX), maxLeft);
        const nextTop = Math.min(Math.max(0, startTop + deltaY), maxTop);

        modalElement.style.left = `${nextLeft}px`;
        modalElement.style.top = `${nextTop}px`;
    });

    handle.addEventListener('pointerup', stopDragging);
    handle.addEventListener('pointercancel', stopDragging);
    handle.addEventListener('lostpointercapture', stopDragging);
}

document.addEventListener('DOMContentLoaded', async function () {
    document.querySelectorAll('[data-draggable-modal="true"]').forEach(enableDraggableModal);
    document.getElementById('departmentForm').addEventListener('submit', handleDepartmentSubmit);
    document.getElementById('courseForm').addEventListener('submit', handleCourseSubmit);

    document.getElementById('addDeptBtn').addEventListener('click', () => openDepartmentModal('add'));
    document.getElementById('departmentCancel').addEventListener('click', () => {
        const modal = document.getElementById('departmentModal');
        modal.classList.remove('active');
        modal.style.setProperty('display', 'none', 'important');
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
    });
    document.getElementById('addCourseBtn').addEventListener('click', () => openCourseModal('add'));
    document.getElementById('courseCancel').addEventListener('click', () => {
        const modal = document.getElementById('courseModal');
        modal.classList.remove('active');
        modal.style.setProperty('display', 'none', 'important');
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
    });

    document.getElementById('deptSearch').addEventListener('input', loadDepartmentManagement);
    document.getElementById('courseSearch').addEventListener('input', loadCourseManagement);
    document.getElementById('courseDepartmentFilter').addEventListener('change', loadCourseManagement);

    document.getElementById('departmentsTableBody').addEventListener('click', async function (event) {
        const button = event.target.closest('button');
        if (!button) return;
        const departmentId = button.dataset.id;
        if (!departmentId) return;

        const action = button.dataset.action;
        if (action === 'dept-edit') openDepartmentModal('edit', departmentId);
        if (action === 'dept-delete') deleteDepartment(departmentId);
    });

    document.getElementById('coursesTableBody').addEventListener('click', async function (event) {
        const button = event.target.closest('button');
        if (!button) return;
        const courseId = button.dataset.id;
        if (!courseId) return;

        const action = button.dataset.action;
        if (action === 'course-edit') openCourseModal('edit', courseId);
        if (action === 'course-delete') deleteCourse(courseId);
    });

    await loadDepartmentManagement();
    await loadCourseManagement();
});
</script>

<style>
    .department-management {
        width: 100%;
        max-width: 1440px;
        margin: 0 auto;
        padding: 20px;
    }

    .department-management .page-header {
        margin: 0 0 20px;
    }

    .department-management .page-title-group {
        min-width: 0;
    }

    .department-management .page-title,
    .department-management .page-copy {
        overflow-wrap: anywhere;
    }

    .department-management-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: start;
        gap: 20px;
    }

    .department-management .management-card {
        min-width: 0;
        min-height: 420px;
        overflow: hidden;
    }

    .department-management .management-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .department-management .management-card-header .card-title {
        min-width: 0;
        margin: 0;
    }

    .department-management .btn {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 9px 16px;
        border-radius: 9px;
        line-height: 1.2;
        text-align: center;
        white-space: normal;
    }

    .department-management .management-card-header .btn {
        min-height: 40px;
    }

    .department-management .management-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        min-width: 0;
    }

    .department-management .management-actions .btn {
        min-height: 34px;
        padding: 6px 8px;
        border-radius: 7px;
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .management-notice {
        position: fixed;
        inset: 0;
        z-index: 13000;
        display: grid;
        place-items: center;
        padding: 1rem;
        background: rgba(3, 9, 30, 0.72);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .management-notice[hidden] {
        display: none !important;
    }

    .management-notice-dialog {
        width: min(100%, 440px);
        padding: clamp(1.25rem, 4vw, 1.75rem);
        border: 1px solid rgba(15, 23, 42, 0.1);
        border-radius: 18px;
        background: #FFFFFF;
        color: #111827;
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.3);
    }

    body.dashboard-page .department-management .management-notice-icon,
    body.dashboard-page .department-management .management-notice-icon i {
        display: grid;
        width: 46px;
        height: 46px;
        place-items: center;
        margin-bottom: 1rem;
        border-radius: 14px;
        background: #FEF3C7;
        color: #B45309 !important;
        font-size: 1.25rem;
    }

    body.dashboard-page .department-management .management-notice-icon.is-danger {
        background: #FEE2E2;
        color: #B91C1C !important;
    }

    body.dashboard-page .department-management .management-notice-dialog h2 {
        margin: 0 0 0.5rem;
        color: #111827 !important;
        font-size: 1.2rem;
        line-height: 1.35;
    }

    body.dashboard-page .department-management .management-notice-dialog p {
        margin: 0;
        color: #4B5563 !important;
        line-height: 1.6;
    }

    .management-notice-actions {
        display: flex;
        justify-content: center;
        margin-top: 1.25rem;
    }

    .management-notice-actions .btn {
        min-width: 96px;
        color: #111827 !important;
    }

    .management-notice-actions #managementNoticeCancel[hidden] {
        display: none !important;
    }

    body.dashboard-page .department-management .management-notice-actions #managementNoticeClose.is-danger {
        background: #DC2626 !important;
        border-color: #B91C1C !important;
        color: #FFFFFF !important;
    }

    body.dashboard-page .department-management .management-notice-actions #managementNoticeClose.is-danger:hover {
        background: #B91C1C !important;
        color: #FFFFFF !important;
    }

    body.dashboard-page .department-management .management-actions .btn,
    body.dashboard-page .department-management .management-actions .btn:hover,
    body.dashboard-page .department-management .management-actions .btn:focus,
    body.dashboard-page .department-management .management-actions .btn:active {
        color: #000000 !important;
    }

    .department-management .course-management-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
    }

    .department-management .course-management-filters .form-control {
        flex: 1 1 180px;
        min-width: 0;
    }

    .department-management .table-responsive {
        width: 100%;
        overflow-x: hidden;
    }

    .department-management .student-table {
        width: 100%;
        min-width: 0;
        table-layout: fixed;
    }

    .department-management .student-table th,
    .department-management .student-table td {
        padding-right: 8px;
        padding-left: 8px;
        vertical-align: middle;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .department-management .student-table th:last-child,
    .department-management .student-table td:last-child {
        width: 36%;
    }

    .department-management .management-modal-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    body.dashboard-page .department-management .management-modal-actions #departmentCancel,
    body.dashboard-page .department-management .management-modal-actions #courseCancel,
    body.dashboard-page .department-management .management-modal-actions #departmentCancel:hover,
    body.dashboard-page .department-management .management-modal-actions #courseCancel:hover,
    body.dashboard-page .department-management .management-modal-actions #departmentCancel:focus,
    body.dashboard-page .department-management .management-modal-actions #courseCancel:focus,
    body.dashboard-page .department-management .management-modal-actions #departmentCancel:active,
    body.dashboard-page .department-management .management-modal-actions #courseCancel:active {
        color: #000000 !important;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .badge-success {
        background: rgba(34, 197, 94, 0.14);
        color: #7ef0ac;
        border: 1px solid rgba(34, 197, 94, 0.35);
    }
    .badge-warning {
        background: rgba(250, 204, 21, 0.12);
        color: #ffd66b;
        border: 1px solid rgba(250, 204, 21, 0.28);
    }
    .modal {
        display: none !important;
        overflow-y: auto;
        padding: 20px 0;
    }
    .modal.active {
        display: flex !important;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
    }
    .modal-content {
        width: min(640px, calc(100% - 32px)) !important;
        margin: auto !important;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
    }

    @media (max-width: 1100px) {
        .department-management-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .department-management .management-card {
            min-height: 0;
        }
    }

    @media (max-width: 600px) {
        .department-management {
            padding: 14px;
        }

        .department-management .page-header {
            margin-bottom: 16px;
        }

        .department-management-grid {
            gap: 14px;
        }

        .department-management .management-card-header {
            align-items: stretch;
        }

        .department-management .management-card-header .btn {
            width: 100%;
        }

        .department-management .management-actions {
            min-width: 0;
        }

        .department-management .management-modal-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .department-management .management-modal-actions .btn {
            width: 100%;
            padding-inline: 10px;
        }
    }
</style>

</main>
<?= $this->endSection() ?>
