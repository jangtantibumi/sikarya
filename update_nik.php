<?php
use App\Models\User;

$users = User::whereNull('employee_code')->orWhere('employee_code', '')->get();

foreach ($users as $user) {
    $divCode = strtolower(substr($user->division ?? 'CORP', 0, 3));
    if (str_contains(strtolower($user->division ?? ''), 'marketing')) $divCode = 'mkt';
    elseif (str_contains(strtolower($user->division ?? ''), 'operasional')) $divCode = 'ops';
    elseif (str_contains(strtolower($user->division ?? ''), 'finance')) $divCode = 'fin';
    elseif (str_contains(strtolower($user->division ?? ''), 'hr')) $divCode = 'hrd';

    $isManager = str_contains(strtolower($user->role), 'mgr') || str_contains(strtolower($user->role), 'manager');
    $isCEO = str_contains(strtolower($user->role), 'ceo');
    $isSuperadmin = str_contains(strtolower($user->role), 'superadmin') || str_contains(strtolower($user->role), 'admin');
    
    $lvlCode = 'STF';
    if ($isManager) $lvlCode = 'MGR';
    if ($isCEO) $lvlCode = 'CEO';
    if ($isSuperadmin) $lvlCode = 'ADM';

    $randNum = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    $user->employee_code = strtoupper("SA-{$divCode}-{$lvlCode}-{$randNum}");
    $user->save();
}
echo "Updated " . $users->count() . " users.\n";
