<?php

namespace App\Services\Family;

use App\Models\Family\FamilyPerson;
use App\Models\Family\FamilyRelationship;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FamilyRelationshipService
{
    public function create(array $data): FamilyRelationship
    {
        return DB::transaction(function () use ($data) {
            $personId = (int) $data['person_id'];
            $relatedPersonId = (int) $data['related_person_id'];
            $type = $data['relationship_type'];
            $subtype = $data['relationship_subtype'] ?? null;
            $parentRole = $data['parent_role'] ?? null;

            if ($personId === $relatedPersonId) {
                throw ValidationException::withMessages([
                    'related_person_id' =>
                        'Seseorang tidak dapat berelasi dengan dirinya sendiri.',
                ]);
            }

            if (! in_array($type, ['parent', 'spouse', 'guardian'], true)) {
                throw ValidationException::withMessages([
                    'relationship_type' => 'Jenis hubungan tidak valid.',
                ]);
            }

            $this->validateSubtype($type, $subtype);
            $parentRole = $this->validateParentRole($type, $parentRole);

            $this->validateDates(
                $data['started_at'] ?? null,
                $data['ended_at'] ?? null
            );

            // Lock both people in a consistent ascending ID order.
            $people = FamilyPerson::query()
                ->whereIn('id', [$personId, $relatedPersonId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($people->count() !== 2) {
                abort(404, 'Data individu tidak ditemukan.');
            }

            $firstPerson = $people->get($personId);
            $secondPerson = $people->get($relatedPersonId);

            if ($type === 'spouse') {
                return $this->createSpouse(
                    $firstPerson,
                    $secondPerson,
                    $data
                );
            }

            if ($type === 'parent') {
                $this->preventParentCycle(
                    $personId,
                    $relatedPersonId
                );
            }

            // Subtype is part of relationship identity.
            $exists = FamilyRelationship::query()
                ->where('person_id', $personId)
                ->where('related_person_id', $relatedPersonId)
                ->where('relationship_type', $type)
                ->where('relationship_subtype', $subtype)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'relationship_subtype' =>
                        'Hubungan dengan jenis dan subjenis yang sama sudah tercatat.',
                ]);
            }

            return FamilyRelationship::create([
                'person_id' => $personId,
                'related_person_id' => $relatedPersonId,
                'relationship_type' => $type,
                'relationship_subtype' => $subtype,
                'parent_role' => $parentRole,
                'started_at' => $data['started_at'] ?? null,
                'ended_at' => $data['ended_at'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    private function createSpouse(
        FamilyPerson $first,
        FamilyPerson $second,
        array $data
    ): FamilyRelationship {
        $personId = min($first->id, $second->id);
        $relatedPersonId = max($first->id, $second->id);

        $startDate = $data['started_at'] ?? null;
        $endDate = $data['ended_at'] ?? null;

        $existing = FamilyRelationship::query()
            ->where('person_id', $personId)
            ->where('related_person_id', $relatedPersonId)
            ->where('relationship_type', 'spouse')
            ->lockForUpdate()
            ->get();

        foreach ($existing as $relationship) {
            if ($this->periodsOverlap(
                $startDate,
                $endDate,
                $relationship->started_at?->format('Y-m-d'),
                $relationship->ended_at?->format('Y-m-d')
            )) {
                throw ValidationException::withMessages([
                    'relationship_type' =>
                        'Periode hubungan pasangan bertumpang tindih dengan riwayat yang sudah ada.',
                ]);
            }
        }

        return FamilyRelationship::create([
            'person_id' => $personId,
            'related_person_id' => $relatedPersonId,
            'relationship_type' => 'spouse',
            'relationship_subtype' => null,
            'parent_role' => null,
            'started_at' => $startDate,
            'ended_at' => $endDate,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function validateSubtype(
        string $type,
        ?string $subtype
    ): void {
        $allowed = [
            'parent' => ['biological', 'adoptive', 'step'],
            'guardian' => ['legal', 'informal'],
            'spouse' => [],
        ];

        if (
            $subtype !== null &&
            ! in_array($subtype, $allowed[$type], true)
        ) {
            throw ValidationException::withMessages([
                'relationship_subtype' => 'Subjenis hubungan tidak valid.',
            ]);
        }

        if (
            in_array($type, ['parent', 'guardian'], true) &&
            $subtype === null
        ) {
            throw ValidationException::withMessages([
                'relationship_subtype' => 'Subjenis hubungan wajib dipilih.',
            ]);
        }
    }

    private function validateParentRole(
        string $type,
        ?string $parentRole
    ): ?string {
        if ($type !== 'parent') {
            if ($parentRole !== null) {
                throw ValidationException::withMessages([
                    'parent_role' =>
                        'Peran orang tua hanya berlaku untuk hubungan parent.',
                ]);
            }

            return null;
        }

        $parentRole ??= 'unspecified';

        if (! in_array(
            $parentRole,
            ['father', 'mother', 'unspecified'],
            true
        )) {
            throw ValidationException::withMessages([
                'parent_role' => 'Peran orang tua tidak valid.',
            ]);
        }

        return $parentRole;
    }

    private function validateDates(
        ?string $startDate,
        ?string $endDate
    ): void {
        foreach ([
            'started_at' => $startDate,
            'ended_at' => $endDate,
        ] as $field => $date) {
            if ($date === null) {
                continue;
            }

            try {
                $parsedDate = Carbon::createFromFormat('!Y-m-d', $date);

                if (
                    $parsedDate === false ||
                    $parsedDate->format('Y-m-d') !== $date
                ) {
                    throw new \InvalidArgumentException();
                }
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    $field =>
                        'Format tanggal tidak valid. Gunakan format YYYY-MM-DD.',
                ]);
            }
        }

        if (
            $startDate !== null &&
            $endDate !== null &&
            $endDate < $startDate
        ) {
            throw ValidationException::withMessages([
                'ended_at' =>
                    'Tanggal akhir tidak boleh sebelum tanggal mulai.',
            ]);
        }
    }

    private function preventParentCycle(
        int $parentId,
        int $childId
    ): void {
        $visited = [];
        $stack = [$childId];

        while (! empty($stack)) {
            $currentId = array_pop($stack);

            if (isset($visited[$currentId])) {
                continue;
            }

            $visited[$currentId] = true;

            if ($currentId === $parentId) {
                throw ValidationException::withMessages([
                    'related_person_id' =>
                        'Hubungan ini akan membentuk siklus silsilah keluarga.',
                ]);
            }

            $children = FamilyRelationship::query()
                ->where('person_id', $currentId)
                ->where('relationship_type', 'parent')
                ->pluck('related_person_id');

            foreach ($children as $nextId) {
                $stack[] = (int) $nextId;
            }
        }
    }

    private function periodsOverlap(
        ?string $startA,
        ?string $endA,
        ?string $startB,
        ?string $endB
    ): bool {
        $startA ??= '0001-01-01';
        $endA ??= '9999-12-31';
        $startB ??= '0001-01-01';
        $endB ??= '9999-12-31';

        return $startA <= $endB && $startB <= $endA;
    }
}
