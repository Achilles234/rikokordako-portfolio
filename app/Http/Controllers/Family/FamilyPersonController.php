<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\Family\FamilyGroup;
use App\Models\Family\FamilyPerson;
use App\Models\Family\Role;
use App\Services\Family\FamilyAccessService;
use App\Services\Family\FamilyMembershipService;
use App\Services\Family\FamilyRelationshipService;
use App\Models\Family\FamilyRelationship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;


class FamilyPersonController extends Controller
{
    public function __construct(
         private FamilyAccessService $familyAccess,
         private FamilyMembershipService $membershipService,
         private FamilyRelationshipService $relationshipService
    ) {}

    /**
     * Display people registered in a family group.
     */
    public function index(int $groupId): View
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

        $people = FamilyPerson::query()
            ->whereHas('memberships', function ($query) use ($group) {
                $query->where('family_group_id', $group->id);
            })
            ->with(['memberships' => function ($query) use ($group) {
                $query->where('family_group_id', $group->id)
                    ->with('role');
            }])
            ->orderBy('full_name')
            ->paginate(15);

        return view('family.people.index', [
            'group' => $group,
            'people' => $people,
            'canCreate' => $this->familyAccess->hasPermission(
                $user,
                'persons.create',
                $group->id
            ),
            'canUpdate' => $this->familyAccess->hasPermission(
                $user,
                'persons.update',
                $group->id
            ),
        ]);
    }

    /**
     * Show the create person form.
     */
    public function create(int $groupId): View
    {
        $user = auth('family')->user();
        $group = FamilyGroup::query()->findOrFail($groupId);

        abort_unless(
            $this->familyAccess->hasPermission(
                $user,
                'persons.create',
                $group->id
            ),
            403
        );

        abort_unless($group->status === 'active', 403);

        return view('family.people.create', [
            'group' => $group,
        ]);
    }

    /**
     * Create a person and register pending membership.
     */
    public function store(Request $request, int $groupId): RedirectResponse
    {
        $user = auth('family')->user();
        $group = FamilyGroup::query()->findOrFail($groupId);

        abort_unless(
            $this->familyAccess->hasPermission(
                $user,
                'persons.create',
                $group->id
            ),
            403
        );

        abort_unless($group->status === 'active', 403);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'death_date' => ['nullable', 'date', 'after_or_equal:birth_date'],
            'death_place' => ['nullable', 'string', 'max:255'],
            'life_status' => ['nullable', 'string', 'max:20'],
            'biography' => ['nullable', 'string'],
        ]);

        $role = Role::query()
            ->where('slug', 'family_member')
            ->where('scope', 'group')
            ->firstOrFail();

        DB::transaction(function () use (
            $validated,
            $group,
            $user,
            $role
        ) {
            $person = FamilyPerson::create([
                ...$validated,
                'life_status' => $validated['life_status'] ?? 'unknown',
                'user_id' => null,
            ]);

            $this->membershipService->addPendingMembership(
                $group->id,
                $person->id,
                $role->id,
                $user,
                'Individu baru ditambahkan melalui manajemen individu.'
            );
        });

        return redirect()
            ->route('family.groups.people.index', $group->id)
            ->with('success', 'Data individu berhasil ditambahkan. Keanggotaan menunggu persetujuan.');
    }


    /**
     * Display a person's profile and family relationships.
     */
    public function show(int $groupId, int $personId): View
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

        $person = FamilyPerson::query()
            ->whereKey($personId)
            ->whereHas('memberships', function ($query) use ($group) {
                $query->where('family_group_id', $group->id);
            })
            ->firstOrFail();

        // Only show relationships where both people belong to this group.
        $groupPersonIds = FamilyPerson::query()
            ->whereHas('memberships', function ($query) use ($group) {
                $query->where('family_group_id', $group->id);
            })
            ->select('id');

        $relationships = \App\Models\Family\FamilyRelationship::query()
            ->where(function ($query) use ($person, $groupPersonIds) {
                $query->where('person_id', $person->id)
                    ->whereIn('related_person_id', $groupPersonIds);
            })
            ->orWhere(function ($query) use ($person, $groupPersonIds) {
                $query->where('related_person_id', $person->id)
                    ->whereIn('person_id', $groupPersonIds);
            })
            ->with(['person', 'relatedPerson'])
            ->orderBy('relationship_type')
            ->orderBy('relationship_subtype')
            ->get();

                $availablePeople = FamilyPerson::query()
            ->whereHas('memberships', function ($query) use ($group) {
                $query->where('family_group_id', $group->id);
            })
            ->where('id', '!=', $person->id)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        return view('family.people.show', [
            'group' => $group,
            'person' => $person,
            'relationships' => $relationships,
            'availablePeople' => $availablePeople,
            'canUpdate' => $this->familyAccess->hasPermission(
                $user,
                'persons.update',
                $group->id
            ),
            'canManageRelationships' => $this->familyAccess->hasPermission(
                $user,
                'relationships.manage',
                $group->id
            ),
        ]);
    }

    /**
 * Show the create family relationship form.
 */
public function createRelationship(int $groupId, int $personId): View
{
    $user = auth('family')->user();
    $group = FamilyGroup::query()->findOrFail($groupId);

    abort_unless(
        $this->familyAccess->hasPermission(
            $user,
            'relationships.manage',
            $group->id
        ),
        403
    );

    abort_unless($group->status === 'active', 403);

    $person = FamilyPerson::query()
        ->whereKey($personId)
        ->whereHas('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->firstOrFail();

    $availablePeople = FamilyPerson::query()
        ->whereHas('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->where('id', '!=', $person->id)
        ->orderBy('full_name')
        ->get(['id', 'full_name']);

    return view('family.people.relationships.create', [
        'group' => $group,
        'person' => $person,
        'availablePeople' => $availablePeople,
    ]);
}

public function storeRelationship(
    Request $request,
    int $groupId,
    int $personId
): RedirectResponse {
    $user = auth('family')->user();
    $group = FamilyGroup::query()->findOrFail($groupId);

    abort_unless(
        $this->familyAccess->hasPermission(
            $user,
            'relationships.manage',
            $group->id
        ),
        403
    );

    abort_unless($group->status === 'active', 403);

    $person = FamilyPerson::query()
        ->whereKey($personId)
        ->whereHas('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->firstOrFail();

    $validated = $request->validate([
        'related_person_id' => [
            'required',
            'integer',
            'not_in:' . $person->id,
        ],
        'relationship_type' => [
            'required',
            'in:parent,child,spouse,guardian',
        ],
        'relationship_subtype' => [
            'nullable',
            'in:biological,adoptive,step,legal,informal',
        ],
        'parent_role' => [
            'nullable',
            'in:father,mother,unspecified',
        ],
        'started_at' => ['nullable', 'date'],
        'ended_at' => [
            'nullable',
            'date',
            'after_or_equal:started_at',
        ],
        'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    $relatedPerson = FamilyPerson::query()
        ->whereKey($validated['related_person_id'])
        ->whereHas('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->firstOrFail();


$relationshipType = $validated['relationship_type'];

if ($relationshipType === 'parent') {
    // Individu terpilih adalah orang tua dari individu utama.
    $relationshipData = [
        'person_id' => $relatedPerson->id,
        'related_person_id' => $person->id,
        'relationship_type' => 'parent',
        'relationship_subtype' => $validated['relationship_subtype'],
        'parent_role' => $validated['parent_role'] ?? 'unspecified',
    ];
} elseif ($relationshipType === 'child') {
    // Individu utama adalah orang tua dari individu terpilih.
    $relationshipData = [
        'person_id' => $person->id,
        'related_person_id' => $relatedPerson->id,
        'relationship_type' => 'parent',
        'relationship_subtype' => $validated['relationship_subtype'],
        'parent_role' => $validated['parent_role'] ?? 'unspecified',
    ];
} elseif ($relationshipType === 'guardian') {
    $relationshipData = [
        'person_id' => $person->id,
        'related_person_id' => $relatedPerson->id,
        'relationship_type' => 'guardian',
        'relationship_subtype' => $validated['relationship_subtype'],
        'parent_role' => null,
    ];
} else {
    // Pasangan tidak memiliki subtype maupun parent_role.
    $relationshipData = [
        'person_id' => $person->id,
        'related_person_id' => $relatedPerson->id,
        'relationship_type' => 'spouse',
        'relationship_subtype' => null,
        'parent_role' => null,
    ];
}

$this->relationshipService->create(array_merge(
    $relationshipData,
    [
        'started_at' => $validated['started_at'] ?? null,
        'ended_at' => $validated['ended_at'] ?? null,
        'notes' => $validated['notes'] ?? null,
    ]
));


    return redirect()
        ->route('family.groups.people.show', [
            'groupId' => $group->id,
            'personId' => $person->id,
        ])
        ->with('success', 'Hubungan keluarga berhasil ditambahkan.');
}

    
/**
 * Show the edit family relationship form.
 */
public function editRelationship(
    int $groupId,
    int $personId,
    int $relationshipId
): View {
    $user = auth('family')->user();
    $group = FamilyGroup::query()->findOrFail($groupId);

    abort_unless(
        $this->familyAccess->hasPermission(
            $user,
            'relationships.manage',
            $group->id
        ),
        403
    );

    abort_unless($group->status === 'active', 403);

    // Pastikan individu utama merupakan anggota grup.
    $person = FamilyPerson::query()
        ->whereKey($personId)
        ->whereHas('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->firstOrFail();

    // Pastikan hubungan melibatkan individu utama
    // dan kedua individu merupakan anggota grup yang sama.
    $relationship = FamilyRelationship::query()
        ->whereKey($relationshipId)
        ->where(function ($query) use ($person) {
            $query->where('person_id', $person->id)
                ->orWhere('related_person_id', $person->id);
        })
        ->whereHas('person.memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->whereHas('relatedPerson.memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->with(['person', 'relatedPerson'])
        ->firstOrFail();

    // Tentukan individu yang menjadi pasangan relasi
    // dan jenis hubungan berdasarkan sudut pandang profil.
    $isOutgoing = (int) $relationship->person_id === (int) $person->id;

    if ($relationship->relationship_type === 'parent') {
        $formType = $isOutgoing ? 'child' : 'parent';
    } else {
        $formType = $relationship->relationship_type;
    }

    $relatedPersonId = $isOutgoing
        ? $relationship->related_person_id
        : $relationship->person_id;

    $availablePeople = FamilyPerson::query()
        ->whereHas('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->where('id', '!=', $person->id)
        ->orderBy('full_name')
        ->get(['id', 'full_name']);

    return view('family.people.relationships.edit', [
        'group' => $group,
        'person' => $person,
        'relationship' => $relationship,
        'availablePeople' => $availablePeople,
        'formType' => $formType,
        'relatedPersonId' => $relatedPersonId,
        'guardianIsOutgoing' => $isOutgoing,
    ]);
}

/**
 * Update an existing family relationship.
 */
public function updateRelationship(
    Request $request,
    int $groupId,
    int $personId,
    int $relationshipId
): RedirectResponse {
    $user = auth('family')->user();
    $group = FamilyGroup::query()->findOrFail($groupId);

    abort_unless(
        $this->familyAccess->hasPermission(
            $user,
            'relationships.manage',
            $group->id
        ),
        403
    );

    abort_unless($group->status === 'active', 403);

    $person = FamilyPerson::query()
        ->whereKey($personId)
        ->whereHas('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->firstOrFail();

    // Validasi relasi dan keanggotaan kedua individu.
    $relationship = FamilyRelationship::query()
        ->whereKey($relationshipId)
        ->where(function ($query) use ($person) {
            $query->where('person_id', $person->id)
                ->orWhere('related_person_id', $person->id);
        })
        ->whereHas('person.memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->whereHas('relatedPerson.memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->firstOrFail();

    $validated = $request->validate([
        'related_person_id' => [
            'required',
            'integer',
            'not_in:' . $person->id,
        ],
        'relationship_type' => [
            'required',
            'in:parent,child,spouse,guardian',
        ],
        'relationship_subtype' => [
            'nullable',
            'in:biological,adoptive,step,legal,informal',
        ],
        'parent_role' => [
            'nullable',
            'in:father,mother,unspecified',
        ],
        'started_at' => ['nullable', 'date'],
        'ended_at' => [
            'nullable',
            'date',
            'after_or_equal:started_at',
        ],
        'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    $relatedPerson = FamilyPerson::query()
        ->whereKey($validated['related_person_id'])
        ->whereHas('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->firstOrFail();

    $relationshipType = $validated['relationship_type'];

    if ($relationshipType === 'parent') {
        // Individu terpilih adalah orang tua profil utama.
        $relationshipData = [
            'person_id' => $relatedPerson->id,
            'related_person_id' => $person->id,
            'relationship_type' => 'parent',
            'relationship_subtype' => $validated['relationship_subtype'],
            'parent_role' => $validated['parent_role'] ?? 'unspecified',
        ];
    } elseif ($relationshipType === 'child') {
        // Profil utama adalah orang tua individu terpilih.
        $relationshipData = [
            'person_id' => $person->id,
            'related_person_id' => $relatedPerson->id,
            'relationship_type' => 'parent',
            'relationship_subtype' => $validated['relationship_subtype'],
            'parent_role' => $validated['parent_role'] ?? 'unspecified',
        ];
    } elseif ($relationshipType === 'guardian') {
        // Pertahankan arah relasi wali yang sudah ada.
        $isOutgoing = (int) $relationship->person_id === (int) $person->id;

        $relationshipData = [
            'person_id' => $isOutgoing
                ? $person->id
                : $relatedPerson->id,
            'related_person_id' => $isOutgoing
                ? $relatedPerson->id
                : $person->id,
            'relationship_type' => 'guardian',
            'relationship_subtype' => $validated['relationship_subtype'],
            'parent_role' => null,
        ];
    } else {
        // Pasangan: normalisasi ID dilakukan oleh service.
        $relationshipData = [
            'person_id' => $person->id,
            'related_person_id' => $relatedPerson->id,
            'relationship_type' => 'spouse',
            'relationship_subtype' => null,
            'parent_role' => null,
        ];
    }

    $this->relationshipService->update(
        $relationship,
        array_merge($relationshipData, [
            'started_at' => $validated['started_at'] ?? null,
            'ended_at' => $validated['ended_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ])
    );

    return redirect()
        ->route('family.groups.people.show', [
            'groupId' => $group->id,
            'personId' => $person->id,
        ])
        ->with('success', 'Hubungan keluarga berhasil diperbarui.');
}

    public function edit(int $groupId, int $personId): View
    {
        $user = auth('family')->user();
        $group = FamilyGroup::query()->findOrFail($groupId);

        abort_unless(
            $this->familyAccess->hasPermission(
                $user,
                'persons.update',
                $group->id
            ),
            403
        );

        abort_unless($group->status === 'active', 403);

        $person = FamilyPerson::query()
            ->whereKey($personId)
            ->whereHas('memberships', function ($query) use ($group) {
                $query->where('family_group_id', $group->id);
            })
            ->firstOrFail();

        return view('family.people.edit', [
            'group' => $group,
            'person' => $person,
        ]);
    }

    /**
     * Update shared person identity data.
     */
    public function update(
        Request $request,
        int $groupId,
        int $personId
    ): RedirectResponse {
        $user = auth('family')->user();
        $group = FamilyGroup::query()->findOrFail($groupId);

        abort_unless(
            $this->familyAccess->hasPermission(
                $user,
                'persons.update',
                $group->id
            ),
            403
        );

        abort_unless($group->status === 'active', 403);

        $person = FamilyPerson::query()
            ->whereKey($personId)
            ->whereHas('memberships', function ($query) use ($group) {
                $query->where('family_group_id', $group->id);
            })
            ->firstOrFail();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'death_date' => ['nullable', 'date', 'after_or_equal:birth_date'],
            'death_place' => ['nullable', 'string', 'max:255'],
            'life_status' => ['required', 'string', 'max:20'],
            'biography' => ['nullable', 'string'],
        ]);

        $person->update($validated);

        return redirect()
            ->route('family.groups.people.index', $group->id)
            ->with('success', 'Data individu berhasil diperbarui.');
    }
}
