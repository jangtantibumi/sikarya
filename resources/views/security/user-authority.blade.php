<div style="margin-bottom: 12px; border: 1px solid var(--panel-border); border-radius: 8px; overflow: hidden; background: var(--panel-bg);">
    <!-- User Header (Clickable for accordion) -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: rgba(0,0,0,0.02); cursor: pointer;" onclick="const d = document.getElementById('auth-detail-{{ $user->id }}'); d.style.display = d.style.display === 'none' ? 'block' : 'none';">
        <div style="font-size: 13px;">
            <strong>{{ $user->name }}</strong> 
            <span style="color:var(--text-muted); font-size:11px; margin-left: 8px;">
                <i class="fa-solid fa-briefcase"></i> {{ $user->job_title ?? 'Staff' }}
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <form method="POST" action="{{ route('master-demo.security.revoke', $user->id) }}" style="margin: 0; padding: 0;" id="form-revoke-{{ $user->id }}" onclick="event.stopPropagation();">
                @csrf
                @method('DELETE')
                <button type="button" class="file-action-btn remove" style="width: 24px; height: 24px; font-size: 11px; background: rgba(239,68,68,0.1); color: var(--danger); border: none;" title="Cabut Hak Akses" onclick="confirmRevoke('{{ $user->id }}', '{{ addslashes($user->name) }}')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
            @if($user->is_active)
                <span style="background: rgba(12, 53, 39,0.1); color: var(--success); padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;">Active</span>
            @else
                <span style="background: rgba(239,68,68,0.1); color: var(--danger); padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;">Inactive</span>
            @endif
            <i class="fa-solid fa-chevron-down" style="font-size:12px; color:var(--text-muted);"></i>
        </div>
    </div>

    <!-- Full Authority Details -->
    <div id="auth-detail-{{ $user->id }}" style="display: none; padding: 16px; border-top: 1px solid var(--panel-border);">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            
            <!-- Left Column: Scope & Assignment -->
            <div>
                <div style="margin-bottom: 12px;">
                    <span style="display:block; font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">Assigned Role</span>
                    <span style="font-size: 13px; font-weight: 500;">
                        <i class="fa-solid fa-shield-halved" style="color: var(--accent); margin-right: 4px;"></i> {{ $role->name }}
                    </span>
                </div>

                @php
                    $latestLog = $user->latestRoleAssignment()->first();
                    $assignedBy = $latestLog ? \App\Models\User::find($latestLog->user_id)?->name : 'System / Initial Setup';
                    $assignedAt = $latestLog ? $latestLog->created_at->format('d M Y H:i') : 'N/A';
                @endphp
                
                <div style="margin-bottom: 12px;">
                    <span style="display:block; font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">Authority Granted By</span>
                    <span style="font-size: 13px;">{{ $assignedBy }}</span>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <span style="display:block; font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">Assignment Timestamp</span>
                    <span style="font-size: 13px;">{{ $assignedAt }}</span>
                </div>
            </div>

            <!-- Right Column: Permissions & Context -->
            <div>
                <div style="margin-bottom: 12px;">
                    <span style="display:block; font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">Company Scope (Tenant)</span>
                    <span style="font-size: 13px;">{{ $user->company()->first()?->name ?? 'N/A' }} (ID: {{ $user->company_id }})</span>
                </div>

                <div style="margin-bottom: 12px;">
                    <span style="display:block; font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">Division Scope</span>
                    <span style="font-size: 13px;">{{ $user->divisionLabel() }}</span>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <span style="display:block; font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">Effective Permissions</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;">
                        @if($role->permissions && is_array($role->permissions) && count($role->permissions) > 0)
                            @foreach($role->permissions as $perm)
                                <span style="background: var(--panel-secondary); border: 1px solid var(--panel-border); color: var(--text-heading); font-size: 10px; padding: 2px 6px; border-radius: 4px;">
                                    {{ $perm }}
                                </span>
                            @endforeach
                        @else
                            <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">No specific permissions / Root access</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
