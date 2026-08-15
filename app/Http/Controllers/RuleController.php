<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use App\Models\User;
use App\Services\DataDeletionRequestService;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    public function __construct(
        private readonly DataDeletionRequestService $deletions,
    ) {
    }

    public function index(Request $request)
    {
        if (!Rule::query()->exists()) {
            foreach ([
                ['condition' => 'Score ≥ 85%', 'reward' => 'Bonus 1.5%', 'type' => 'success'],
                ['condition' => 'Score ≥ 75%', 'reward' => '-D-Point, Bonus 0.5%', 'type' => 'warning'],
                ['condition' => 'Score < 65%', 'reward' => '-D-Point, SP 1, Bonus 0%', 'type' => 'danger'],
            ] as $rule) {
                Rule::query()->create($rule);
            }
        }

        $user = $request->user();
        $query = Rule::query()->with('creator:id,name,username')->orderBy('id');

        if (!$user->isCEO()) {
            $division = $user->divisionKey();
            $query->where(function ($builder) use ($division): void {
                $builder->whereNull('division');

                if ($division) {
                    $builder->orWhere('division', $division);
                }
            });
        }

        return response()->json(
            $query->get()->map(fn (Rule $rule): array => $this->formatRule($rule, $user))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condition' => ['required', 'string', 'max:255'],
            'reward' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:success,warning,danger'],
            'division' => ['nullable', 'string', 'in:marketing,operasional,finance,hrd'],
        ]);

        $user = $request->user();
        abort_unless($user->isCEO() || $user->isManager(), 403);

        $division = $user->isCEO()
            ? ($validated['division'] ?? null)
            : $user->divisionKey();

        $rule = Rule::query()->create([
            'condition' => $validated['condition'],
            'reward' => $validated['reward'],
            'type' => $validated['type'],
            'division' => $division,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Aturan KPI berhasil ditambahkan.',
            'rule' => $this->formatRule($rule->load('creator:id,name,username'), $user),
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $rule = Rule::query()->findOrFail($id);
        abort_unless($this->canDelete($rule, $request->user()), 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
        $result = $this->deletions->request(
            $request->user(),
            'rule',
            $rule->id,
            $validated['reason'],
        );

        return response()->json([
            'success' => true,
            ...$result,
        ], $result['approval'] ? 202 : 200);
    }

    public function update(Request $request, $id)
    {
        $rule = Rule::query()->findOrFail($id);
        abort_unless($this->canDelete($rule, $request->user()), 403);
        $validated = $request->validate([
            'condition' => ['required', 'string', 'max:255'],
            'reward' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:success,warning,danger'],
            'division' => ['nullable', 'string', 'in:marketing,operasional,finance,hrd'],
        ]);
        if (! $request->user()->isCEO()) {
            $validated['division'] = $request->user()->divisionKey();
        }
        $rule->forceFill($validated)->save();

        return response()->json([
            'success' => true,
            'message' => 'Aturan KPI berhasil diperbarui.',
            'rule' => $this->formatRule($rule->fresh('creator:id,name,username'), $request->user()),
        ]);
    }

    private function formatRule(Rule $rule, User $viewer): array
    {
        return [
            'id' => $rule->id,
            'condition' => $rule->condition,
            'reward' => $rule->reward,
            'type' => $rule->type,
            'division' => $rule->division,
            'scope_label' => $rule->division
                ? match ($rule->division) {
                    'operasional' => 'Operasional',
                    'finance' => 'Finance',
                    'hrd' => 'HRD',
                    default => 'Marketing',
                }
                : 'Seluruh perusahaan',
            'creator' => $rule->creator ? [
                'name' => $rule->creator->name,
                'username' => $rule->creator->username,
            ] : null,
            'can_delete' => $this->canDelete($rule, $viewer),
            'can_edit' => $this->canDelete($rule, $viewer),
        ];
    }

    private function canDelete(Rule $rule, User $viewer): bool
    {
        return $viewer->isCEO()
            || (
                $viewer->isManager()
                && $rule->division === $viewer->divisionKey()
                && ($rule->created_by === null || (int) $rule->created_by === (int) $viewer->id)
            );
    }
}
