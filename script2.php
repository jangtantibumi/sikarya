<?php
$content = file_get_contents('resources/views/organization/index.blade.php');

$oldFooter = <<<FOOTER
    function renderDrawerFooter(data) {
        const actions = data.allowed_actions || [];
        const btnConfigs = {
            'promote': `<button class="ios-btn ios-btn-primary" style="flex: 1;" onclick="openActionModal('\${data.profile.id}', 'promote')">Promote</button>`,
            'demote': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openActionModal('\${data.profile.id}', 'demote')">Demote</button>`,
            'transfer': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openActionModal('\${data.profile.id}', 'transfer')">Transfer</button>`,
            'suspend': `<button class="ios-btn ios-btn-danger" style="flex: 1;" onclick="openActionModal('\${data.profile.id}', 'suspend')">Suspend</button>`,
            'activate': `<button class="ios-btn ios-btn-primary" style="flex: 1;" onclick="openActionModal('\${data.profile.id}', 'activate')">Activate</button>`,
            'review': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openActionModal('\${data.profile.id}', 'review')">Add Review</button>`,
            'request_promotion': `<button class="ios-btn ios-btn-primary" style="flex: 1;" onclick="openActionModal('\${data.profile.id}', 'request_promotion')">Req. Promotion</button>`,
            'request_transfer': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openActionModal('\${data.profile.id}', 'request_transfer')">Req. Transfer</button>`
        };
FOOTER;

$newFooter = <<<FOOTER
    function renderDrawerFooter(data) {
        const actions = data.allowed_actions || [];
        const btnConfigs = {
            'edit': `<button class="ios-btn ios-btn-primary" style="flex: 1;" onclick="openEditDrawer('\${data.profile.id}')">Edit Full Profile</button>`,
            'review': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openPerfDrawer('\${data.profile.id}')">Performance</button>`,
            'request_promotion': `<button class="ios-btn ios-btn-primary" style="flex: 1;" onclick="openEditDrawer('\${data.profile.id}')">Request Promotion</button>`,
            'request_transfer': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openEditDrawer('\${data.profile.id}')">Request Transfer</button>`,
            'assign_task': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openAssignDrawer('\${data.profile.id}')">Assign Task</button>`,
        };
FOOTER;

$content = str_replace($oldFooter, $newFooter, $content);
file_put_contents('resources/views/organization/index.blade.php', $content);
echo "Successfully updated renderDrawerFooter\n";
?>
