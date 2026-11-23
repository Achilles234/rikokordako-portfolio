<?php

namespace Tests\Feature\Family;

use App\Models\Family\FamilyPerson;
use App\Models\Family\FamilyRelationship;
use App\Services\Family\FamilyRelationshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FamilyRelationshipTest extends TestCase
{
    use RefreshDatabase;

    private FamilyRelationshipService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FamilyRelationshipService::class);
    }

    private function createPerson(string $name): FamilyPerson
    {
        return FamilyPerson::create([
            'full_name' => $name,
            'life_status' => 'alive',
        ]);
    }

    public function test_it_creates_parent_child_relationship(): void
    {
        $parent = $this->createPerson('Ayah');
        $child = $this->createPerson('Anak');

        $relationship = $this->service->create([
            'person_id' => $parent->id,
            'related_person_id' => $child->id,
            'relationship_type' => 'parent',
            'relationship_subtype' => 'biological',
        ]);

        $this->assertDatabaseHas('family_relationships', [
            'id' => $relationship->id,
            'person_id' => $parent->id,
            'related_person_id' => $child->id,
            'relationship_type' => 'parent',
        ]);
    }

    public function test_it_rejects_relationship_with_same_person(): void
    {
        $person = $this->createPerson('Individu');

        try {
            $this->service->create([
                'person_id' => $person->id,
                'related_person_id' => $person->id,
                'relationship_type' => 'parent',
                'relationship_subtype' => 'biological',
            ]);

            $this->fail('Seharusnya hubungan dengan diri sendiri ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'related_person_id',
                $exception->errors()
            );
        }
    }

    public function test_it_rejects_duplicate_parent_relationship(): void
    {
        $parent = $this->createPerson('Ayah');
        $child = $this->createPerson('Anak');

        $data = [
            'person_id' => $parent->id,
            'related_person_id' => $child->id,
            'relationship_type' => 'parent',
            'relationship_subtype' => 'biological',
        ];

        $this->service->create($data);

        $this->expectException(ValidationException::class);

        $this->service->create($data);
    }

    public function test_it_prevents_parent_child_cycles(): void
    {
        $grandparent = $this->createPerson('Kakek');
        $parent = $this->createPerson('Ayah');
        $child = $this->createPerson('Anak');

        $this->service->create([
            'person_id' => $grandparent->id,
            'related_person_id' => $parent->id,
            'relationship_type' => 'parent',
            'relationship_subtype' => 'biological',
        ]);

        $this->service->create([
            'person_id' => $parent->id,
            'related_person_id' => $child->id,
            'relationship_type' => 'parent',
            'relationship_subtype' => 'biological',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->create([
            'person_id' => $child->id,
            'related_person_id' => $grandparent->id,
            'relationship_type' => 'parent',
            'relationship_subtype' => 'biological',
        ]);
    }

    public function test_it_stores_spouse_relationship_in_canonical_id_order(): void
    {
        $first = $this->createPerson('Individu A');
        $second = $this->createPerson('Individu B');

        $relationship = $this->service->create([
            'person_id' => $second->id,
            'related_person_id' => $first->id,
            'relationship_type' => 'spouse',
            'started_at' => '2020-01-01',
        ]);

        $this->assertSame(
            min($first->id, $second->id),
            $relationship->person_id
        );

        $this->assertSame(
            max($first->id, $second->id),
            $relationship->related_person_id
        );
    }

    public function test_it_allows_non_overlapping_spouse_history(): void
    {
        $first = $this->createPerson('Individu A');
        $second = $this->createPerson('Individu B');

        $base = [
            'person_id' => $first->id,
            'related_person_id' => $second->id,
            'relationship_type' => 'spouse',
        ];

        $this->service->create(array_merge($base, [
            'started_at' => '2010-01-01',
            'ended_at' => '2015-12-31',
        ]));

        $this->service->create(array_merge($base, [
            'started_at' => '2016-01-01',
            'ended_at' => '2020-12-31',
        ]));

        $this->assertDatabaseCount('family_relationships', 2);
    }

    public function test_it_rejects_overlapping_spouse_history(): void
    {
        $first = $this->createPerson('Individu A');
        $second = $this->createPerson('Individu B');

        $base = [
            'person_id' => $first->id,
            'related_person_id' => $second->id,
            'relationship_type' => 'spouse',
        ];

        $this->service->create(array_merge($base, [
            'started_at' => '2010-01-01',
            'ended_at' => '2018-12-31',
        ]));

        $this->expectException(ValidationException::class);

        $this->service->create(array_merge($base, [
            'started_at' => '2015-01-01',
            'ended_at' => '2020-12-31',
        ]));
    }


public function test_it_rejects_invalid_relationship_type(): void
{
    $first = $this->createPerson('Individu A');
    $second = $this->createPerson('Individu B');

    try {
        $this->service->create([
            'person_id' => $first->id,
            'related_person_id' => $second->id,
            'relationship_type' => 'sibling',
        ]);

        $this->fail('Jenis hubungan yang tidak dikenal seharusnya ditolak.');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey(
            'relationship_type',
            $exception->errors()
        );
    }
}

public function test_it_rejects_invalid_relationship_subtype(): void
{
    $parent = $this->createPerson('Orang Tua');
    $child = $this->createPerson('Anak');

    try {
        $this->service->create([
            'person_id' => $parent->id,
            'related_person_id' => $child->id,
            'relationship_type' => 'parent',
            'relationship_subtype' => 'informal',
        ]);

        $this->fail('Subtype yang tidak sesuai seharusnya ditolak.');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey(
            'relationship_subtype',
            $exception->errors()
        );
    }
}

public function test_it_rejects_end_date_before_start_date(): void
{
    $first = $this->createPerson('Individu A');
    $second = $this->createPerson('Individu B');

    try {
        $this->service->create([
            'person_id' => $first->id,
            'related_person_id' => $second->id,
            'relationship_type' => 'spouse',
            'started_at' => '2020-01-01',
            'ended_at' => '2019-01-01',
        ]);

        $this->fail('Tanggal akhir sebelum tanggal mulai seharusnya ditolak.');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey(
            'ended_at',
            $exception->errors()
        );
    }
}


public function test_it_rejects_invalid_date_format(): void
{
    $first = $this->createPerson('Individu A');
    $second = $this->createPerson('Individu B');

    try {
        $this->service->create([
            'person_id' => $first->id,
            'related_person_id' => $second->id,
            'relationship_type' => 'spouse',
            'started_at' => '2020/01/01',
        ]);

        $this->fail('Format tanggal yang salah seharusnya ditolak.');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey('started_at', $exception->errors());
    }
}

public function test_it_rejects_impossible_calendar_date(): void
{
    $first = $this->createPerson('Individu A');
    $second = $this->createPerson('Individu B');

    try {
        $this->service->create([
            'person_id' => $first->id,
            'related_person_id' => $second->id,
            'relationship_type' => 'spouse',
            'started_at' => '2020-02-30',
        ]);

        $this->fail('Tanggal yang tidak ada di kalender seharusnya ditolak.');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey('started_at', $exception->errors());
    }
}


public function test_it_allows_multiple_parent_subtypes_for_same_people(): void
{
    $parent = $this->createPerson('Orang Tua');
    $child = $this->createPerson('Anak');

    foreach (['biological', 'adoptive', 'step'] as $subtype) {
        $this->service->create([
            'person_id' => $parent->id,
            'related_person_id' => $child->id,
            'relationship_type' => 'parent',
            'relationship_subtype' => $subtype,
        ]);
    }

    $this->assertDatabaseCount('family_relationships', 3);
}

public function test_it_rejects_duplicate_parent_with_same_subtype(): void
{
    $parent = $this->createPerson('Orang Tua');
    $child = $this->createPerson('Anak');

    $data = [
        'person_id' => $parent->id,
        'related_person_id' => $child->id,
        'relationship_type' => 'parent',
        'relationship_subtype' => 'biological',
    ];

    $this->service->create($data);

    $this->expectException(ValidationException::class);

    $this->service->create($data);
}

public function test_it_stores_parent_role_and_defaults_to_unspecified(): void
{
    $parent = $this->createPerson('Orang Tua');
    $child = $this->createPerson('Anak');

    $father = $this->service->create([
        'person_id' => $parent->id,
        'related_person_id' => $child->id,
        'relationship_type' => 'parent',
        'relationship_subtype' => 'biological',
        'parent_role' => 'father',
    ]);

    $this->assertSame('father', $father->parent_role);

    $anotherParent = $this->createPerson('Orang Tua Lain');

    $unspecified = $this->service->create([
        'person_id' => $anotherParent->id,
        'related_person_id' => $child->id,
        'relationship_type' => 'parent',
        'relationship_subtype' => 'biological',
    ]);

    $this->assertSame('unspecified', $unspecified->parent_role);
}

public function test_it_rejects_invalid_parent_role(): void
{
    $parent = $this->createPerson('Orang Tua');
    $child = $this->createPerson('Anak');

    $this->expectException(ValidationException::class);

    $this->service->create([
        'person_id' => $parent->id,
        'related_person_id' => $child->id,
        'relationship_type' => 'parent',
        'relationship_subtype' => 'biological',
        'parent_role' => 'grandparent',
    ]);
}

public function test_it_allows_overlapping_spouse_periods_with_different_partners(): void
{
    $person = $this->createPerson('Individu A');
    $partnerOne = $this->createPerson('Individu B');
    $partnerTwo = $this->createPerson('Individu C');

    $this->service->create([
        'person_id' => $person->id,
        'related_person_id' => $partnerOne->id,
        'relationship_type' => 'spouse',
        'started_at' => '2010-01-01',
        'ended_at' => '2020-12-31',
    ]);

    $this->service->create([
        'person_id' => $person->id,
        'related_person_id' => $partnerTwo->id,
        'relationship_type' => 'spouse',
        'started_at' => '2015-01-01',
        'ended_at' => '2025-12-31',
    ]);

    $this->assertDatabaseCount('family_relationships', 2);
}

public function test_it_rejects_overlapping_spouse_period_even_when_input_order_is_reversed(): void
{
    $first = $this->createPerson('Individu A');
    $second = $this->createPerson('Individu B');

    $this->service->create([
        'person_id' => $first->id,
        'related_person_id' => $second->id,
        'relationship_type' => 'spouse',
        'started_at' => '2010-01-01',
        'ended_at' => '2020-12-31',
    ]);

    $this->expectException(ValidationException::class);

    $this->service->create([
        'person_id' => $second->id,
        'related_person_id' => $first->id,
        'relationship_type' => 'spouse',
        'started_at' => '2015-01-01',
        'ended_at' => '2025-12-31',
    ]);
}

public function test_it_rejects_parent_role_for_non_parent_relationship(): void
{
    $first = $this->createPerson('Individu A');
    $second = $this->createPerson('Individu B');

    $this->expectException(ValidationException::class);

    $this->service->create([
        'person_id' => $first->id,
        'related_person_id' => $second->id,
        'relationship_type' => 'guardian',
        'relationship_subtype' => 'legal',
        'parent_role' => 'father',
    ]);
}

}
