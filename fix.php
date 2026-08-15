<?php
$lines = file('resources/views/master-portal.blade.php');
// lines[2449] is line 2450 (<!-- HIERARKI ORGANISASI VIEW -->)
// lines[2642] is line 2643 (which is empty after </section>)
// Total lines to remove: 2643 - 2450 + 1 = 194.
array_splice($lines, 2449, 194);
file_put_contents('resources/views/master-portal.blade.php', implode('', $lines));
echo "Fixed!";
