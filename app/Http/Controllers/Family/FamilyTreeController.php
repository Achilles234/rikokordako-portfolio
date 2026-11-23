<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\Family\FamilyGroup;
use App\Models\Family\FamilyPerson;
use App\Models\Family\FamilyRelationship;
use App\Services\Family\FamilyAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyTreeController extends Controller
{
    public function __construct(
        private FamilyAccessService $familyAccess
    ) {}

    public function index(Request $request, int $groupId): View
    {
        $user = auth('family')->user();

        $group = FamilyGroup::query()->findOrFail($groupId);

        abort_unless(
            $this->familyAccess->hasPermission(
                $user,
                'persons.view',
                $group->id
            ),
            403
        );

        $activePersonIds = FamilyPerson::query()
            ->whereHas('memberships', function ($query) use ($group) {
                $query->where('family_group_id', $group->id)
                    ->where('status', 'active');
            })
            ->pluck('id');

        $people = FamilyPerson::query()
            ->whereIn('id', $activePersonIds)
         ->orderBy('full_name')
            ->get([
                'id',
                'full_name',
                'sex',
                'life_status',
            ]);


    $relationships = FamilyRelationship::query()
        ->whereIn('person_id', $activePersonIds)
        ->whereIn('related_person_id', $activePersonIds)
        ->where(function ($query) {
            $query->whereNull('started_at')
                ->orWhereDate('started_at', '<=', today());
        })
        ->where(function ($query) {
            $query->whereNull('ended_at')
                ->orWhereDate('ended_at', '>=', today());
        })
        ->orderBy('relationship_type')
        ->orderBy('relationship_subtype')
        ->get([
            'id',
            'person_id',
            'related_person_id',
            'relationship_type',
            'relationship_subtype',
            'parent_role',
            'started_at',
            'ended_at',
        ]);

    $biologicalRelationships = $relationships
    ->where('relationship_type', 'parent')
    ->where('relationship_subtype', 'biological');

$parentsByChild = [];

foreach ($biologicalRelationships as $relationship) {
    $parentId = (int) $relationship->person_id;
    $childId = (int) $relationship->related_person_id;

    $parentsByChild[$childId][] = $parentId;
}


$generationById = [];
$remaining = $people->pluck('id')
    ->map(fn ($id) => (int) $id)
    ->all();

$maxAttempts = count($remaining) + 1;

for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
    $progress = false;

    foreach ($remaining as $index => $personId) {
        $parentIds = $parentsByChild[$personId] ?? [];

                if (empty($parentIds)) {
                    $generationById[$personId] = 0;
                } else {
                $parentsResolved = collect($parentIds)->every(
            fn ($parentId) => array_key_exists(
                $parentId,
                $generationById
            )
        );

        if (! $parentsResolved) {
            continue;
        }

        $resolvedParentIds = $parentIds;

            $parentGenerations = array_map(
                fn ($parentId) => $generationById[$parentId],
                $resolvedParentIds
            );

            $generationById[$personId] =
                max($parentGenerations) + 1;
        }

        unset($remaining[$index]);
        $progress = true;
    }

    $remaining = array_values($remaining);

    if (! $progress || empty($remaining)) {
        break;
    }
}

// Data yang belum terselesaikan tidak dipaksakan menjadi generasi pertama.
foreach ($remaining as $personId) {
    $generationById[$personId] = 0;
}


$generationGroups = $people
    ->groupBy(fn ($person) => $generationById[$person->id] ?? 0)
    ->sortKeys();

        $centerPerson = null;

        if ($request->filled('person')) {
            $centerPerson = $people->firstWhere(
                'id',
                (int) $request->query('person')
            );
        }

        if (! $centerPerson) {
            $centerPerson = $people->firstWhere(
                'id',
                $user->familyPerson?->id
            );
        }

        $centerPerson ??= $people->first();

        return view('family.tree.index', [
            'group' => $group,
            'people' => $people,
            'relationships' => $relationships,
            'centerPerson' => $centerPerson,
            'generationGroups' => $generationGroups,
            'parentsByChild' => $parentsByChild,
        ]);
    }
}
