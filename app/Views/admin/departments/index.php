<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-title-group">
        <h1 class="page-title">Department Management</h1>
        <p class="page-copy">Manage your school's departments and their courses.</p>
    </div>
    <div>
        <input id="searchDept" type="search" class="form-control" placeholder="Search Department..." style="display:inline-block;width:320px;margin-right:10px;" />
        <button id="addDeptBtn" class="btn btn-primary">+ Add Department</button>
    </div>
</div>

<section class="table-card" style="margin-top:18px;">
    <div class="table-inner">
        <table class="student-table" id="departmentsTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Courses</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="departmentsBody">
            </tbody>
        </table>
    </div>
</section>

<!-- Add/Edit Modal -->
<div id="deptModal" class="modal" style="display:none;">
    <div class="modal-content card register-modal" style="max-width:680px;padding:20px;">
        <h3 id="deptModalTitle">Add Department</h3>
        <div style="margin-top:12px;">
            <label>Department Name *</label>
            <input id="dept_name" class="form-control" />
        </div>
        <div style="margin-top:12px;">
            <label>Status</label>
            <select id="dept_status" class="form-control"><option value="Active">Active</option><option value="Inactive">Inactive</option></select>
        </div>
        <div style="margin-top:16px;text-align:right;">
            <button id="deptCancel" class="btn btn-secondary">Cancel</button>
            <button id="deptSave" class="btn btn-primary">Save Department</button>
        </div>
    </div>
</div>

<script>
async function refreshDepartments() {
    const res = await fetch('<?= base_url('admin/departments') ?>');
    // The index route returns an HTML page when browsed; use list endpoint for JSON
    const listRes = await fetch('<?= base_url('admin/departments/list') ?>');
    const deps = await listRes.json();
    const tbody = document.getElementById('departmentsBody');
    tbody.innerHTML = deps.map(d => `
        <tr>
            <td>${d.department_name}</td>
            <td>—</td>
            <td>${d.status}</td>
            <td>${d.created_at ? new Date(String(d.created_at).replace(' ', 'T')).toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true }) : ''}</td>
            <td><button class="btn btn-secondary" data-id="${d.id}" data-action="edit">Edit</button> <button class="btn btn-danger" data-id="${d.id}" data-action="delete">Delete</button></td>
        </tr>
    `).join('');
}

document.addEventListener('DOMContentLoaded', function(){
    const addBtn = document.getElementById('addDeptBtn');
    const modal = document.getElementById('deptModal');
    const cancel = document.getElementById('deptCancel');
    const save = document.getElementById('deptSave');
    let editingId = null;

    addBtn.addEventListener('click', function(){
        editingId = null; document.getElementById('dept_name').value = ''; document.getElementById('dept_status').value = 'Active'; modal.style.display='block'; document.getElementById('deptModalTitle').innerText='Add Department';
    });
    cancel.addEventListener('click', function(){ modal.style.display='none'; });
    save.addEventListener('click', async function(){
        const payload = { department_name: document.getElementById('dept_name').value, status: document.getElementById('dept_status').value };
        const url = editingId ? '<?= base_url('admin/departments/update') ?>/'+editingId : '<?= base_url('admin/departments/store') ?>';
        const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(payload)});
        const data = await res.json();
        if (data.success) { modal.style.display='none'; refreshDepartments(); loadDepartments(); }
        else alert('Error: '+(data.message || JSON.stringify(data.errors)));
    });

    document.getElementById('departmentsBody').addEventListener('click', async function(e){
        const btn = e.target.closest('button'); if (!btn) return; const id = btn.dataset.id; const action = btn.dataset.action;
        if (action === 'edit') {
            const res = await fetch('<?= base_url('admin/departments') ?>/'+id);
            const d = await res.json(); editingId = id; document.getElementById('dept_name').value = d.department_name; document.getElementById('dept_status').value = d.status; document.getElementById('deptModalTitle').innerText='Edit Department'; modal.style.display='block';
        }
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete this department?')) return;
            const res = await fetch('<?= base_url('admin/departments/delete') ?>/'+id, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); const data = await res.json(); if (!data.success) alert(data.message || 'Failed to delete'); else refreshDepartments();
        }
    });

    refreshDepartments();
});
</script>

<?= $this->endSection() ?>
