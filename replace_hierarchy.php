<?php
$file = 'D:\suba-erp-master-local-latest\resources\views\employee-portal.blade.php';
$content = file_get_contents($file);

$oldContent = file_get_contents('D:\suba-erp-master-local-latest\resources\views\employee-portal-hierarchy-backup.txt');

$newContent = <<<'HTML'
        <div id="view-hierarchy" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h2>Struktur Organisasi</h2>
                    <p class="desc" style="margin-top: 4px;">Informasi struktur departemen dan divisi karyawan.</p>
                </div>
                @if($user->isManager())
                <button class="user-pill" style="background: var(--accent); color: white; border: none; padding: 10px 20px; font-weight: bold; cursor: pointer;" onclick="document.getElementById('modal-hire-hierarchy-staff').style.display='flex'">
                    <i class="fa-solid fa-user-plus"></i> Tambah Staf Baru
                </button>
                @endif
            </div>

            @php
                $allUsersHierarchyStaff = \App\Models\User::where('company_id', $user->company_id)->where('is_active', true)->get();
                $divisionsStaff = $allUsersHierarchyStaff->groupBy(function($u) {
                    return $u->division ?? $u->divisionLabel();
                });
                
                // Urutkan: Divisi user sendiri di paling atas
                $myDivision = $user->division ?? $user->divisionLabel();
                $myDivData = $divisionsStaff->pull($myDivision);
                if ($myDivData) {
                    $divisionsStaff->prepend($myDivData, $myDivision);
                }
                
                // Helper render card untuk meminimalisir kode berulang
                if (!function_exists('renderOrgCard')) {
                    function renderOrgCard($u, $currentUser) {
                        $initials = $u->getInitials();
                        $name = $u->name;
                        $title = $u->job_title ?? $u->role;
                        $type = $u->employment_type ?? 'Full-Time';
                        $tasks = $u->tasks()->where('status', 'pending')->count();
                        
                        $html = '<div class="org-card" style="border-color: rgba(16,185,129,0.3); box-shadow: 0 0 10px rgba(16,185,129,0.05);">';
                        $html .= '<div class="org-avatar">'.$initials.'</div>';
                        $html .= '<strong style="font-size: 15px; color: var(--text-heading); display: block;">'.$name.'</strong>';
                        $html .= '<span style="font-size: 12px; color: var(--text-muted);">'.$title.'</span>';
                        $html .= '<div class="org-badges">';
                        $html .= '<span class="org-badge badge-role">'.strtoupper(explode("_", $u->role)[0]).'</span>';
                        $html .= '<span class="org-badge badge-type">'.$type.'</span>';
                        if ($tasks > 0) {
                            $html .= '<span class="org-badge badge-task-active">'.$tasks.' tugas aktif</span>';
                        } else {
                            $html .= '<span class="org-badge badge-task">On Track</span>';
                        }
                        $html .= '</div>';
                        
                        if ($currentUser->isCEO() || $currentUser->isManagerOf($u) || $currentUser->id == $u->id) {
                            $html .= '<div class="org-actions">';
                            $html .= '<button onclick="openEditProfileModal('.$u->id.', \''.addslashes($name).'\', \''.addslashes($title).'\', \''.$type.'\', \''.($u->target_hours_per_month ?? 160).'\')"><i class="fa-solid fa-pen"></i> Edit</button>';
                            if ($currentUser->isCEO() || $currentUser->isManagerOf($u)) {
                                $html .= '<button class="btn-delete" onclick="confirmDeleteUser('.$u->id.')"><i class="fa-solid fa-trash"></i> Hapus</button>';
                            }
                            $html .= '</div>';
                        }
                        $html .= '</div>';
                        return $html;
                    }
                }
            @endphp

            @foreach($divisionsStaff as $divName => $divUsers)
                @if($loop->index == 1)
                <div style="text-align: center; margin: 32px 0;">
                    <button class="user-pill" onclick="document.getElementById('org-other-divisions').style.display='block'; this.style.display='none';" style="background: var(--panel-secondary); color: var(--text-heading); border: 1px solid var(--panel-border); padding: 10px 24px; font-weight: bold; cursor: pointer; transition: 0.2s;">
                        Tampilkan Divisi Lainnya <i class="fa-solid fa-chevron-down" style="margin-left: 8px;"></i>
                    </button>
                </div>
                <div id="org-other-divisions" style="display: none;">
                @endif
                
                <div class="org-division-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <h4 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-layer-group" style="color: var(--accent);"></i> {{ $divName }}
                        </h4>
                        <span class="user-pill" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; font-size: 11px;">{{ $divUsers->count() }} aktif</span>
                    </div>

                    @php
                        $managers = collect();
                        $spvs = collect();
                        $pics = collect();
                        $staffs = collect();
                        
                        foreach($divUsers as $u) {
                            $title = strtolower($u->job_title ?? '');
                            $role = strtolower($u->role ?? '');
                            
                            if ((str_starts_with($role, 'mgr_') || $role == 'ceo' || $role == 'manager') && !str_contains($title, 'supervisor') && !str_contains($title, 'spv') && !str_contains($title, 'pic')) {
                                $managers->push($u);
                            } elseif (str_contains($title, 'supervisor') || str_contains($title, 'spv')) {
                                $spvs->push($u);
                            } elseif (str_contains($title, 'pic') || str_contains($title, 'coordinator') || str_contains($title, 'lead')) {
                                $pics->push($u);
                            } else {
                                $staffs->push($u);
                            }
                        }
                        
                        // Fallback logic if there is no manager
                        if ($managers->isEmpty() && $spvs->isNotEmpty()) { $managers = $spvs; $spvs = collect(); }
                        elseif ($managers->isEmpty() && $pics->isNotEmpty()) { $managers = $pics; $pics = collect(); }
                        elseif ($managers->isEmpty() && $staffs->isNotEmpty()) { $managers = collect([$staffs->shift()]); } 
                    @endphp

                    <div class="org-tree">
                        <ul>
                            @if($managers->isNotEmpty())
                            <li>
                                <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                                    @foreach($managers as $mgr)
                                        {!! renderOrgCard($mgr, $user) !!}
                                    @endforeach
                                </div>
                                
                                @if($spvs->isNotEmpty())
                                <ul>
                                    <li>
                                        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                                            @foreach($spvs as $spv)
                                                {!! renderOrgCard($spv, $user) !!}
                                            @endforeach
                                        </div>
                                        
                                        @if($pics->isNotEmpty())
                                        <ul>
                                            <li>
                                                <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                                                    @foreach($pics as $pic)
                                                        {!! renderOrgCard($pic, $user) !!}
                                                    @endforeach
                                                </div>
                                                
                                                @if($staffs->isNotEmpty())
                                                <ul>
                                                    <li>
                                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin: 0 auto; max-width: 550px; justify-items: center;">
                                                            @foreach($staffs as $stf)
                                                                {!! renderOrgCard($stf, $user) !!}
                                                            @endforeach
                                                        </div>
                                                    </li>
                                                </ul>
                                                @endif
                                            </li>
                                        </ul>
                                        @elseif($staffs->isNotEmpty())
                                        <ul>
                                            <li>
                                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin: 0 auto; max-width: 550px; justify-items: center;">
                                                    @foreach($staffs as $stf)
                                                        {!! renderOrgCard($stf, $user) !!}
                                                    @endforeach
                                                </div>
                                            </li>
                                        </ul>
                                        @endif
                                    </li>
                                </ul>
                                @elseif($pics->isNotEmpty())
                                <ul>
                                    <li>
                                        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                                            @foreach($pics as $pic)
                                                {!! renderOrgCard($pic, $user) !!}
                                            @endforeach
                                        </div>
                                        
                                        @if($staffs->isNotEmpty())
                                        <ul>
                                            <li>
                                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin: 0 auto; max-width: 550px; justify-items: center;">
                                                    @foreach($staffs as $stf)
                                                        {!! renderOrgCard($stf, $user) !!}
                                                    @endforeach
                                                </div>
                                            </li>
                                        </ul>
                                        @endif
                                    </li>
                                </ul>
                                @elseif($staffs->isNotEmpty())
                                <ul>
                                    <li>
                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin: 0 auto; max-width: 550px; justify-items: center;">
                                            @foreach($staffs as $stf)
                                                {!! renderOrgCard($stf, $user) !!}
                                            @endforeach
                                        </div>
                                    </li>
                                </ul>
                                @endif
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endforeach
            @if(count($divisionsStaff) > 1)
            </div> <!-- End org-other-divisions -->
            @endif
            
            @if($user->isManager())
            <!-- Modal Hire Employee Khusus Manager -->
            <div id="modal-hire-hierarchy-staff" class="modal-overlay" style="display: none;">
                <div class="modal-content" style="max-width: 600px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0;">Tambah Staf / Rekrutmen</h3>
                        <i class="fa-solid fa-times" style="cursor: pointer; color: var(--text-muted);" onclick="document.getElementById('modal-hire-hierarchy-staff').style.display='none'"></i>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; color: #f59e0b;">
                        <i class="fa-solid fa-circle-info"></i> Penambahan staf oleh Manager akan berstatus <strong>Pending</strong> dan membutuhkan persetujuan (ACC) dari CEO sebelum dapat digunakan.
                    </div>
                    <form method="POST" action="{{ route('master-demo.employee.hire') }}">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Jabatan (Job Title)</label>
                                <input type="text" name="job_title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Tipe Pekerjaan</label>
                                <select name="employment_type" class="form-control" required>
                                    <option value="Full-Time">Full-Time</option>
                                    <option value="Part-Time">Part-Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Paid Internship">Paid Internship</option>
                                </select>
                            </div>
                            <input type="hidden" name="division" value="{{ $user->division ?? $user->divisionLabel() }}">
                            <input type="hidden" name="reports_to_id" value="{{ $user->id }}">
                        </div>
                        <button type="submit" class="btn" style="width: 100%;"><i class="fa-solid fa-paper-plane"></i> Ajukan Perekrutan ke CEO</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
HTML;

$replaced = str_replace($oldContent, $newContent, $content);
if ($replaced === $content) {
    echo "REPLACEMENT FAILED: String not found.\n";
    $p1 = strpos($content, '<div id="view-hierarchy"');
    $p2 = strpos($content, '<div id="view-pos"', $p1);
    if ($p1 !== false && $p2 !== false) {
        $replaced = substr($content, 0, $p1) . $newContent . "\n        " . substr($content, $p2);
        file_put_contents($file, $replaced);
        echo "SUCCESS with boundary fallback.\n";
    } else {
        echo "Boundary fallback failed.";
    }
} else {
    file_put_contents($file, $replaced);
    echo "SUCCESS with strict replace.\n";
}
?>
