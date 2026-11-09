<div class="page-subheader">
    <div>
        <input id="searchCourse" type="search" class="form-control" placeholder="Search Course..." style="display:inline-block;width:280px;margin-right:8px;" />
        <select id="filterDeptSelect" class="form-control" style="display:inline-block;width:260px;margin-right:8px;"></select>
        <button id="addCourseBtn" class="btn btn-primary">+ Add Course</button>
    </div>
</div>

<section class="table-card">
    <div class="table-inner">
        <table class="student-table" id="coursesTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="coursesBody"></tbody>
        </table>
    </div>
</section>

<!-- Course Modal -->
<div id="courseModal" class="modal" style="display:none;">
    <div class="modal-content card register-modal" style="max-width:720px;padding:20px;">
        <h3 id="courseModalTitle">Add Course</h3>
        <div style="margin-top:12px;">
            <label>Course Name *</label>
            <input id="course_name" class="form-control" />
        </div>
        <div style="margin-top:12px;">
            <label>Department *</label>
            <select id="course_department" class="form-control"></select>
        </div>
        <div style="margin-top:12px;">
            <label>Status</label>
            <select id="course_status" class="form-control"><option value="Active">Active</option><option value="Inactive">Inactive</option></select>
        </div>
        <div style="margin-top:16px;text-align:right;">
            <button id="courseCancel" class="btn btn-secondary">Cancel</button>
            <button id="courseSave" class="btn btn-primary">Save Course</button>
        </div>
    </div>
</div>

<script>
async function loadCoursePage() {
    const deptRes = await fetch('<?= base_url('admin/departments/list') ?>');
    const deps = await deptRes.json();
    const sel = document.getElementById('filterDeptSelect');
    sel.innerHTML = '<option value="">All Departments</option>' + deps.map(d=>`<option value="${d.id}">${d.department_code} - ${d.department_name}</option>`).join('');
    refreshCourses();
}

async function refreshCourses(departmentId) {
    let url = '<?= base_url('courses/by-department') ?>';
    if (departmentId) url += '/' + departmentId;
    const res = await fetch(url);
    const data = await res.json().catch(()=>[]);
    const tbody = document.getElementById('coursesBody');
    if (!Array.isArray(data) || data.length === 0) { tbody.innerHTML = '<tr><td colspan="5">No courses</td></tr>'; return; }
    tbody.innerHTML = data.map(c=>`<tr><td>${c.course_name}</td><td>${c.department_name||c.department_id}</td><td>${c.status}</td><td>${c.created_at ? new Date(String(c.created_at).replace(' ', 'T')).toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true }) : ''}</td><td><button data-id="${c.id}" data-action="edit" class="btn btn-secondary">Edit</button> <button data-id="${c.id}" data-action="delete" class="btn btn-danger">Delete</button></td></tr>`).join('');
}

document.addEventListener('DOMContentLoaded', function(){
    loadCoursePage();
    const addBtn = document.getElementById('addCourseBtn');
    const modal = document.getElementById('courseModal');
    const cancel = document.getElementById('courseCancel');
    const save = document.getElementById('courseSave');
    let editing = null;

    document.getElementById('filterDeptSelect').addEventListener('change', function(){ refreshCourses(this.value); });

    addBtn.addEventListener('click', async function(){
        const deps = await (await fetch('<?= base_url('admin/departments/list') ?>')).json();
        document.getElementById('course_department').innerHTML = deps.map(d=>`<option value="${d.id}">${d.department_code} - ${d.department_name}</option>`).join('');
        editing = null; document.getElementById('course_name').value=''; document.getElementById('course_status').value='Active'; modal.style.display='block';
    });
    cancel.addEventListener('click', function(){ modal.style.display='none'; });
    save.addEventListener('click', async function(){
        const payload = { course_name: document.getElementById('course_name').value, department_id: document.getElementById('course_department').value, status: document.getElementById('course_status').value };
        const url = editing ? '<?= base_url('admin/courses/update') ?>/'+editing : '<?= base_url('admin/courses/store') ?>';
        const res = await fetch(url, { method: 'POST', headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify(payload)});
        const data = await res.json(); if (data.success) { modal.style.display='none'; loadCoursePage(); } else alert('Error: '+JSON.stringify(data.errors||data.message));
    });

    document.getElementById('coursesBody').addEventListener('click', async function(e){
        const btn = e.target.closest('button'); if (!btn) return; const id = btn.dataset.id; const action = btn.dataset.action;
        if (action==='edit') {
            const res = await fetch('<?= base_url('admin/courses') ?>/'+id); const c = await res.json(); editing = id; document.getElementById('course_name').value=c.course_name; const deps = await (await fetch('<?= base_url('admin/departments/list') ?>')).json(); document.getElementById('course_department').innerHTML = deps.map(d=>`<option value="${d.id}" ${d.id==c.department_id? 'selected':''}>${d.department_code} - ${d.department_name}</option>`).join(''); document.getElementById('course_status').value=c.status; modal.style.display='block';
        }
        if (action==='delete') { if (!confirm('Delete course?')) return; const res = await fetch('<?= base_url('admin/courses/delete') ?>/'+id, { method:'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content} }); const data = await res.json(); if (!data.success) alert('Failed to delete'); else loadCoursePage(); }
    });
});
</script>
