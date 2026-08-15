$file = 'D:\suba-erp-master-local-latest\app\Http\Controllers\SecurityController.php'
$content = Get-Content $file -Raw

$replace = @"
    public function getAuditLogs(Request `$request)
    {
        `$companyId = auth()->user()->company_id;
        `$range = `$request->get('range', 'all');
        `$date = `$request->get('date');
        `$timeStart = `$request->get('time_start');
        `$timeEnd = `$request->get('time_end');

        `$users = User::where('company_id', `$companyId)->pluck('id');
        
        `$rbacQuery = \App\Models\AuditLog::with(['actor', 'target'])
            ->where(function(`$q) use (`$users) {
                `$q->whereIn('user_id', `$users)->orWhereIn('target_user_id', `$users);
            });
            
        `$eventQuery = \App\Models\AuditEvent::with('actor')
            ->where('company_id', `$companyId);
            
        `$applyDateFilter = function(`$query, `$column) use (`$range, `$date, `$timeStart, `$timeEnd) {
            if (`$range === 'today') {
                `$query->whereDate(`$column, now()->toDateString());
            } elseif (`$range === 'week') {
                `$query->whereBetween(`$column, [now()->subDays(7), now()]);
            } elseif (`$range === 'month') {
                `$query->whereBetween(`$column, [now()->startOfMonth(), now()]);
            } elseif (`$date) {
                `$query->whereDate(`$column, `$date);
            }
            
            if (`$timeStart) {
                `$query->whereTime(`$column, '>=', `$timeStart);
            }
            if (`$timeEnd) {
                `$query->whereTime(`$column, '<=', `$timeEnd);
            }
        };

        `$applyDateFilter(`$rbacQuery, 'created_at');
        `$applyDateFilter(`$eventQuery, 'created_at');

        `$rbacLogs = `$rbacQuery->get()->map(function(`$log) {
            `$actionStr = `$log->action === 'role_assigned' ? 'Diberikan Hak Akses' : (`$log->action === 'role_revoked' ? 'Dicabut Hak Aksesnya' : `$log->action);
            return [
                'type' => 'rbac',
                'id' => 'rbac_'.`$log->id,
                'actor' => `$log->actor ? `$log->actor->name : 'System',
                'actor_avatar' => `$log->actor && `$log->actor->profile_picture_path ? '/storage/'.`$log->actor->profile_picture_path : null,
                'target' => `$log->target ? `$log->target->name : '-',
                'action_label' => `$actionStr,
                'action' => `$log->action,
                'details' => `$log->after_state,
                'ip_address' => `$log->ip_address,
                'created_at' => `$log->created_at->format('d M Y, H:i'),
                'timestamp' => `$log->created_at->timestamp,
            ];
        });

        `$eventLogs = `$eventQuery->get()->map(function(`$event) {
            return [
                'type' => 'event',
                'id' => 'evt_'.`$event->id,
                'actor' => `$event->actor ? `$event->actor->name : 'System',
                'actor_avatar' => `$event->actor && `$event->actor->profile_picture_path ? '/storage/'.`$event->actor->profile_picture_path : null,
                'target' => `$event->subject_type ? class_basename(`$event->subject_type) . ' #' . `$event->subject_id : '-',
                'action_label' => str_replace('_', ' ', strtoupper(`$event->event_type)),
                'action' => `$event->event_type,
                'details' => `$event->metadata,
                'ip_address' => `$event->ip_address,
                'created_at' => `$event->created_at->format('d M Y, H:i'),
                'timestamp' => `$event->created_at->timestamp,
            ];
        });

        `$unified = `$rbacLogs->concat(`$eventLogs)->sortByDesc('timestamp')->values();

        return response()->json(`$unified);
    }
"@

$search = @"
    public function getAuditLogs(Request `$request)
    {
        `$companyId = auth()->user()->company_id;
        `$range = `$request->get('range', 'all');
        `$date = `$request->get('date');
        `$timeStart = `$request->get('time_start');
        `$timeEnd = `$request->get('time_end');
        `$keyword = `$request->get('keyword');
        `$sort = `$request->get('sort', 'desc');

        `$rbacQuery = \Illuminate\Support\Facades\DB::table('audit_logs')
            ->join('users as actor', 'audit_logs.user_id', '=', 'actor.id')
            ->leftJoin('users as target', 'audit_logs.target_user_id', '=', 'target.id')
            ->where('actor.company_id', `$companyId)
            ->select(
                'audit_logs.id',
                \Illuminate\Support\Facades\DB::raw("'rbac' as type"),
                'actor.name as actor_name',
                'actor.profile_picture_path as actor_avatar',
                \Illuminate\Support\Facades\DB::raw("COALESCE(target.name, '-') as target_name"),
                'audit_logs.action',
                'audit_logs.after_state as details',
                'audit_logs.ip_address',
                'audit_logs.created_at'
            );

        `$eventQuery = \Illuminate\Support\Facades\DB::table('audit_events')
            ->leftJoin('users as actor', 'audit_events.actor_id', '=', 'actor.id')
            ->where('audit_events.company_id', `$companyId)
            ->select(
                'audit_events.id',
                \Illuminate\Support\Facades\DB::raw("'event' as type"),
                \Illuminate\Support\Facades\DB::raw("COALESCE(actor.name, 'System') as actor_name"),
                'actor.profile_picture_path as actor_avatar',
                \Illuminate\Support\Facades\DB::raw("CONCAT(COALESCE(audit_events.subject_type, '-'), ' #', COALESCE(audit_events.subject_id, '')) as target_name"),
                'audit_events.event_type as action',
                'audit_events.metadata as details',
                'audit_events.ip_address',
                'audit_events.created_at'
            );

        `$applyFilters = function(`$query, `$column) use (`$range, `$date, `$timeStart, `$timeEnd, `$keyword) {
            if (`$range === 'today') {
                `$query->whereDate(`$column, now()->toDateString());
            } elseif (`$range === 'week') {
                `$query->whereBetween(`$column, [now()->subDays(7), now()]);
            } elseif (`$range === 'month') {
                `$query->whereBetween(`$column, [now()->startOfMonth(), now()]);
            } elseif (`$range === 'last_month') {
                `$query->whereBetween(`$column, [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
            } elseif (`$range === '30_days') {
                `$query->whereBetween(`$column, [now()->subDays(30), now()]);
            } elseif (`$date) {
                `$query->whereDate(`$column, `$date);
            }
            
            if (`$timeStart) {
                `$query->whereTime(`$column, '>=', `$timeStart);
            }
            if (`$timeEnd) {
                `$query->whereTime(`$column, '<=', `$timeEnd);
            }
        };

        `$applyFilters(`$rbacQuery, 'audit_logs.created_at');
        `$applyFilters(`$eventQuery, 'audit_events.created_at');

        if (`$keyword) {
            `$keywordStr = '%' . strtolower(`$keyword) . '%';
            `$rbacQuery->where(function(`$q) use (`$keywordStr) {
                `$q->whereRaw('LOWER(actor.name) LIKE ?', [`$keywordStr])
                  ->orWhereRaw('LOWER(target.name) LIKE ?', [`$keywordStr])
                  ->orWhereRaw('LOWER(audit_logs.action) LIKE ?', [`$keywordStr]);
            });
            `$eventQuery->where(function(`$q) use (`$keywordStr) {
                `$q->whereRaw('LOWER(actor.name) LIKE ?', [`$keywordStr])
                  ->orWhereRaw('LOWER(audit_events.subject_type) LIKE ?', [`$keywordStr])
                  ->orWhereRaw('LOWER(audit_events.event_type) LIKE ?', [`$keywordStr]);
            });
        }

        `$unionQuery = `$rbacQuery->unionAll(`$eventQuery);
        
        `$sortDirection = strtolower(`$sort) === 'asc' ? 'asc' : 'desc';
        `$query = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({`$unionQuery->toSql()}) as audit_union"))
            ->mergeBindings(`$unionQuery)
            ->orderBy('created_at', `$sortDirection);

        `$paginator = `$query->paginate(15);
        
        `$paginator->getCollection()->transform(function(`$log) {
            `$actionLabel = `$log->action;
            if (`$log->type === 'rbac') {
                `$actionLabel = `$log->action === 'role_assigned' ? 'Diberikan Hak Akses' : (`$log->action === 'role_revoked' ? 'Dicabut Hak Aksesnya' : `$log->action);
            } else {
                `$actionLabel = str_replace('_', ' ', strtoupper(`$log->action));
            }
            
            `$avatar = `$log->actor_avatar ? '/storage/' . `$log->actor_avatar : null;
            `$targetStr = trim(str_replace('App\\Models\\', '', `$log->target_name));
            if (`$targetStr === '#') `$targetStr = '-';
            
            return [
                'type' => `$log->type,
                'id' => `$log->type . '_' . `$log->id,
                'actor' => `$log->actor_name,
                'actor_avatar' => `$avatar,
                'target' => `$targetStr,
                'action_label' => `$actionLabel,
                'action' => `$log->action,
                'details' => json_decode(`$log->details, true),
                'ip_address' => `$log->ip_address,
                'created_at' => \Carbon\Carbon::parse(`$log->created_at)->format('d M Y, H:i'),
                'timestamp' => \Carbon\Carbon::parse(`$log->created_at)->timestamp,
            ];
        });

        return response()->json(`$paginator);
    }
"@

$content = $content.Replace($search, $replace)
Set-Content -Path $file -Value $content -NoNewline
Write-Host "Reverted Backend Logic"
