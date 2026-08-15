<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\SecurityAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecurityController extends Controller
{
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $companyId = auth()->user()->company_id;

        $key = Str::slug($request->name, '_');

        // Ensure key is unique
        $originalKey = $key;
        $counter = 1;
        while (Role::where('company_id', $companyId)->where('key', $key)->exists()) {
            $key = $originalKey.'_'.$counter;
            $counter++;
        }

        Role::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'key' => $key,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
        ]);

        return response()->json(['message' => 'Role created successfully']);
    }

    public function assignRole(Request $request, SecurityAuditService $auditService)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_key' => 'required|string',
        ]);

        $companyId = auth()->user()->company_id;

        // Verify user belongs to company
        $user = User::where('company_id', $companyId)->findOrFail($request->user_id);

        // Ensure role exists
        $role = Role::where('company_id', $companyId)->where('key', $request->role_key)->firstOrFail();

        $oldRole = $user->role;

        $user->update([
            'role' => $role->key,
        ]);

        $auditService->logRbac(
            actor: auth()->user(),
            target: $user,
            action: 'role_assigned',
            beforeState: ['role' => $oldRole],
            afterState: ['role' => $role->key],
            ipAddress: $request->ip()
        );

        return response()->json(['message' => 'User role updated successfully']);
    }

    public function revokeRole($userId, Request $request, SecurityAuditService $auditService)
    {
        $companyId = auth()->user()->company_id;
        $user = User::where('company_id', $companyId)->findOrFail($userId);

        $oldRole = $user->role;

        // Optionally, don't allow revoking the last admin role
        if ($oldRole === 'super_admin' && User::where('company_id', $companyId)->where('role', 'super_admin')->count() <= 1) {
            return response()->json(['message' => 'Cannot revoke the last super admin.'], 400);
        }

        $user->update([
            'role' => '',
        ]);

        $auditService->logRbac(
            actor: auth()->user(),
            target: $user,
            action: 'role_revoked',
            beforeState: ['role' => $oldRole],
            afterState: ['role' => ''],
            ipAddress: $request->ip()
        );

        return response()->json(['message' => 'User role revoked successfully']);
    }

    public function getAuditLogs(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $range = $request->get('range', 'all');
        $date = $request->get('date');
        $timeStart = $request->get('time_start');
        $timeEnd = $request->get('time_end');
        $keyword = $request->get('keyword');
        $sort = $request->get('sort', 'desc');

        $rbacQuery = DB::table('audit_logs')
            ->join('users as actor', 'audit_logs.user_id', '=', 'actor.id')
            ->leftJoin('users as target', 'audit_logs.target_user_id', '=', 'target.id')
            ->where('actor.company_id', $companyId)
            ->select(
                'audit_logs.id',
                DB::raw("'rbac' as type"),
                'actor.name as actor_name',
                'actor.profile_picture_path as actor_avatar',
                DB::raw("COALESCE(target.name, '-') as target_name"),
                'audit_logs.action',
                'audit_logs.after_state as details',
                'audit_logs.ip_address',
                'audit_logs.created_at'
            );

        $eventQuery = DB::table('audit_events')
            ->leftJoin('users as actor', 'audit_events.actor_id', '=', 'actor.id')
            ->where('audit_events.company_id', $companyId)
            ->select(
                'audit_events.id',
                DB::raw("'event' as type"),
                DB::raw("COALESCE(actor.name, 'System') as actor_name"),
                'actor.profile_picture_path as actor_avatar',
                DB::raw("CONCAT(COALESCE(audit_events.subject_type, '-'), ' #', COALESCE(audit_events.subject_id, '')) as target_name"),
                'audit_events.event_type as action',
                'audit_events.metadata as details',
                'audit_events.ip_address',
                'audit_events.created_at'
            );

        $applyFilters = function ($query, $column) use ($range, $date, $timeStart, $timeEnd) {
            if ($range === 'today') {
                $query->whereDate($column, now()->toDateString());
            } elseif ($range === 'week') {
                $query->whereBetween($column, [now()->subDays(7), now()]);
            } elseif ($range === 'month') {
                $query->whereBetween($column, [now()->startOfMonth(), now()]);
            } elseif ($range === 'last_month') {
                $query->whereBetween($column, [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
            } elseif ($range === '30_days') {
                $query->whereBetween($column, [now()->subDays(30), now()]);
            } elseif ($date) {
                $query->whereDate($column, $date);
            }

            if ($timeStart) {
                $query->whereTime($column, '>=', $timeStart);
            }
            if ($timeEnd) {
                $query->whereTime($column, '<=', $timeEnd);
            }
        };

        $applyFilters($rbacQuery, 'audit_logs.created_at');
        $applyFilters($eventQuery, 'audit_events.created_at');

        if ($keyword) {
            $keywordStr = '%'.strtolower($keyword).'%';
            $rbacQuery->where(function ($q) use ($keywordStr) {
                $q->whereRaw('LOWER(actor.name) LIKE ?', [$keywordStr])
                    ->orWhereRaw('LOWER(target.name) LIKE ?', [$keywordStr])
                    ->orWhereRaw('LOWER(audit_logs.action) LIKE ?', [$keywordStr]);
            });
            $eventQuery->where(function ($q) use ($keywordStr) {
                $q->whereRaw('LOWER(actor.name) LIKE ?', [$keywordStr])
                    ->orWhereRaw('LOWER(audit_events.subject_type) LIKE ?', [$keywordStr])
                    ->orWhereRaw('LOWER(audit_events.event_type) LIKE ?', [$keywordStr]);
            });
        }

        $unionQuery = $rbacQuery->unionAll($eventQuery);

        $sortDirection = strtolower($sort) === 'asc' ? 'asc' : 'desc';
        $query = DB::table(DB::raw("({$unionQuery->toSql()}) as audit_union"))
            ->mergeBindings($unionQuery)
            ->orderBy('created_at', $sortDirection);

        $paginator = $query->paginate(15);

        $paginator->getCollection()->transform(function ($log) {
            $actionLabel = $log->action;
            if ($log->type === 'rbac') {
                $actionLabel = $log->action === 'role_assigned' ? 'Diberikan Hak Akses' : ($log->action === 'role_revoked' ? 'Dicabut Hak Aksesnya' : $log->action);
            } else {
                $actionLabel = str_replace('_', ' ', strtoupper($log->action));
            }

            $avatar = $log->actor_avatar ? '/storage/'.$log->actor_avatar : null;
            $targetStr = trim(str_replace('App\\Models\\', '', $log->target_name));
            if ($targetStr === '#') {
                $targetStr = '-';
            }

            return [
                'type' => $log->type,
                'id' => $log->type.'_'.$log->id,
                'actor' => $log->actor_name,
                'actor_avatar' => $avatar,
                'target' => $targetStr,
                'action_label' => $actionLabel,
                'action' => $log->action,
                'details' => json_decode($log->details, true),
                'ip_address' => $log->ip_address,
                'created_at' => Carbon::parse($log->created_at)->format('d M Y, H:i'),
                'timestamp' => Carbon::parse($log->created_at)->timestamp,
            ];
        });

        return response()->json($paginator);
    }

    public function clearAuditLogs(Request $request)
    {
        if (! $request->user() || (! $request->user()->isCEO() && ! $request->user()->isPlatformAdmin())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $companyId = $request->user()->company_id;
        $timeframe = $request->input('timeframe', 'all');

        $queryEvents = DB::table('audit_events')->where('company_id', $companyId);

        $userIds = User::where('company_id', $companyId)->pluck('id');
        $queryLogs = DB::table('audit_logs')->whereIn('user_id', $userIds);

        if ($timeframe === 'older_than_7_days') {
            $limitDate = now()->subDays(7);
            $queryEvents->where('created_at', '<', $limitDate);
            $queryLogs->where('created_at', '<', $limitDate);
        } elseif ($timeframe === 'older_than_30_days') {
            $limitDate = now()->subDays(30);
            $queryEvents->where('created_at', '<', $limitDate);
            $queryLogs->where('created_at', '<', $limitDate);
        } elseif ($timeframe === 'older_than_90_days') {
            $limitDate = now()->subDays(90);
            $queryEvents->where('created_at', '<', $limitDate);
            $queryLogs->where('created_at', '<', $limitDate);
        }

        $queryEvents->delete();
        $queryLogs->delete();

        return response()->json(['success' => true, 'message' => 'Audit logs cleared successfully']);
    }
}
