<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\FamilyAgenda;
use App\Models\FamilyAgendaParticipant;
use App\Models\FamilyAgendaReminder;
use App\Services\Family\FamilyAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FamilyAgendaController extends Controller
{
    public function __construct(
        private readonly FamilyAccessService $familyAccess
    ) {
    }

    /**
     * Semua agenda dari grup yang dapat diakses pengguna.
     */
    public function index(Request $request)
    {
        $user = $request->user('family');
        $groupIds = $this->visibleGroupIds($user);

        $query = FamilyAgenda::query()
            ->with(['familyGroup', 'creator'])
            ->withCount('participants')
            ->whereIn('family_group_id', $groupIds)
            ->orderBy('starts_at');

        if ($request->filled('group')) {
            $groupId = (int) $request->query('group');

            abort_unless($groupIds->contains($groupId), 404);

            $query->where('family_group_id', $groupId);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('starts_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('starts_at', '<=', $request->query('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $agendas = $query->paginate(15)->withQueryString();

        $groups = DB::table('family_groups')
            ->whereIn('id', $groupIds)
            ->orderBy('name')
            ->get();

        $categories = FamilyAgenda::query()
            ->whereIn('family_group_id', $groupIds)
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('family.agendas.index', [
            'agendas' => $agendas,
            'groups' => $groups,
            'categories' => $categories,
            'selectedGroupId' => $request->query('group'),
            'filters' => $request->only([
                'group',
                'category',
                'date_from',
                'date_to',
                'status',
            ]),
        ]);
    }


/**
 * Detail agenda.
 */
public function show(Request $request, int $agendaId)
{
    $user = $request->user('family');

    $agenda = FamilyAgenda::query()
        ->with([
            'familyGroup',
            'creator',
            'participants.familyMember.person',
            'reminders',
        ])
        ->withCount('participants')
        ->whereKey($agendaId)
        ->firstOrFail();

    abort_unless(
        $this->familyAccess->hasPermission(
            $user,
            'agendas.view',
            $agenda->family_group_id
        ),
        404
    );

    $canUpdate = $this->familyAccess->hasPermission(
        $user,
        'agendas.update',
        $agenda->family_group_id
    );

    $canCancel = $this->familyAccess->hasPermission(
        $user,
        'agendas.cancel',
        $agenda->family_group_id
    );

    return view('family.agendas.show', [
        'agenda' => $agenda,
        'canUpdate' => $canUpdate,
        'canCancel' => $canCancel,
    ]);
}
    /**
     * Agenda untuk satu grup tertentu.
     */
    public function group(Request $request, int $groupId)
    {
        abort_unless(
            $this->familyAccess->hasPermission(
                $request->user('family'),
                'agendas.view',
                $groupId
            ),
            404
        );

        return redirect()->route('family.agendas.index', [
            'group' => $groupId,
        ]);
    }

    /**
     * Form pembuatan agenda.
     */
    public function create(Request $request)
    {
        $user = $request->user('family');
        $groupIds = $this->manageableGroupIds($user, 'agendas.create');

        abort_if($groupIds->isEmpty(), 403);

        $groups = DB::table('family_groups')
            ->whereIn('id', $groupIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedGroupId = $request->query('group');

        if ($selectedGroupId !== null) {
            abort_unless(
                $groupIds->contains((int) $selectedGroupId),
                404
            );
        }

        $members = collect();

        if ($selectedGroupId !== null) {
            $members = $this->activeMembers((int) $selectedGroupId);
        }

        return view('family.agendas.create', [
            'groups' => $groups,
            'members' => $members,
            'selectedGroupId' => $selectedGroupId,
        ]);
    }

    /**
     * Simpan agenda dan daftar peserta awal.
     */
    public function store(Request $request)
    {
        $user = $request->user('family');

        $data = $request->validate([
            'family_group_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_all_day' => ['nullable', 'boolean'],
            'audience_type' => [
                'required',
                Rule::in(['all_members', 'selected']),
            ],
            'participants' => [
                'required_if:audience_type,selected',
                'array',
                'min:1',
            ],
            'participants.*' => ['integer', 'distinct'],
            'reminders' => ['nullable', 'array'],
            'reminders.*' => [
                'integer',
                'distinct',
                Rule::in([15, 60, 1440]),
            ],
        ]);

        $groupId = (int) $data['family_group_id'];

        abort_unless(
            $this->familyAccess->hasPermission(
                $user,
                'agendas.create',
                $groupId
            ),
            403
        );

        abort_unless(
            DB::table('family_groups')
                ->where('id', $groupId)
                ->where('status', 'active')
                ->exists(),
            422
        );

        $members = $this->activeMembers($groupId);

        if ($data['audience_type'] === 'all_members') {
            $participantIds = $members->pluck('id')->all();
        } else {
            $participantIds = collect($data['participants'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $validIds = $members
                ->whereIn('id', $participantIds)
                ->pluck('id')
                ->all();

            if (count($validIds) !== count($participantIds)) {
                abort(422, 'Daftar peserta berisi anggota yang tidak valid.');
            }
        }

        DB::transaction(function () use (
            $data,
            $user,
            $groupId,
            $participantIds
        ) {
            $agenda = FamilyAgenda::create([
                'family_group_id' => $groupId,
                'title' => $data['title'],
                'category' => $data['category'] ?? null,
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'is_all_day' => $data['is_all_day'] ?? false,
                'audience_type' => $data['audience_type'],
                'status' => 'scheduled',
                'created_by' => $user->id,
            ]);

            foreach ($participantIds as $memberId) {
                FamilyAgendaParticipant::create([
                    'family_agenda_id' => $agenda->id,
                    'family_member_id' => $memberId,
                    'attendance_status' => 'pending',
                ]);
            }

            foreach ($data['reminders'] ?? [] as $minutes) {
                FamilyAgendaReminder::create([
                    'family_agenda_id' => $agenda->id,
                    'minutes_before' => $minutes,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()
            ->route('family.agendas.index', ['group' => $groupId])
            ->with('success', 'Agenda berhasil dibuat.');
    }

    /**
     * Form edit agenda.
     */
    public function edit(Request $request, int $agendaId)
    {
        $agenda = $this->findManageableAgenda(
            $request,
            $agendaId,
            'agendas.update'
        );

        $agenda->load([
            'familyGroup',
            'participants.familyMember.person',
            'reminders',
        ]);

        return view('family.agendas.edit', [
            'agenda' => $agenda,
        ]);
    }

    /**
     * Perbarui informasi agenda.
     *
     * Daftar peserta lama tidak otomatis berubah.
     */
    public function update(Request $request, int $agendaId)
    {
        $agenda = $this->findManageableAgenda(
            $request,
            $agendaId,
            'agendas.update'
        );

        abort_if($agenda->status === 'cancelled', 422);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_all_day' => ['nullable', 'boolean'],
        ]);

        $agenda->update([
            ...$data,
            'is_all_day' => $data['is_all_day'] ?? false,
        ]);

        return redirect()
            ->route('family.agendas.index', [
                'group' => $agenda->family_group_id,
            ])
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    /**
     * Batalkan agenda tanpa menghapus riwayat.
     */
    public function cancel(Request $request, int $agendaId)
    {
        $agenda = $this->findManageableAgenda(
            $request,
            $agendaId,
            'agendas.cancel'
        );

        abort_if($agenda->status === 'cancelled', 422);

        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:5000'],
        ]);

        $agenda->update([
            'status' => 'cancelled',
            'cancellation_reason' => $data['cancellation_reason'] ?? null,
        ]);

        return redirect()
            ->route('family.agendas.index', [
                'group' => $agenda->family_group_id,
            ])
            ->with('success', 'Agenda berhasil dibatalkan.');
    }

    /**
     * Konfirmasi kehadiran oleh peserta yang bersangkutan.
     */
    public function respond(Request $request, int $agendaId)
    {
        $user = $request->user('family');

        $data = $request->validate([
            'attendance_status' => [
                'required',
                Rule::in(['attending', 'declined', 'maybe']),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $agenda = FamilyAgenda::query()
            ->whereKey($agendaId)
            ->where('status', 'scheduled')
            ->firstOrFail();

        abort_unless(
            $this->familyAccess->hasPermission(
                $user,
                'agendas.respond',
                $agenda->family_group_id
            ),
            404
        );

        $membership = DB::table('family_members')
            ->join(
                'family_persons',
                'family_persons.id',
                '=',
                'family_members.family_person_id'
            )
            ->where('family_persons.user_id', $user->id)
            ->where('family_members.family_group_id', $agenda->family_group_id)
            ->where('family_members.status', 'active')
            ->where('family_members.role_scope', 'group')
            ->select('family_members.id')
            ->first();

        abort_unless($membership, 403);

        $participant = FamilyAgendaParticipant::query()
            ->where('family_agenda_id', $agenda->id)
            ->where('family_member_id', $membership->id)
            ->firstOrFail();

        $participant->update([
            'attendance_status' => $data['attendance_status'],
            'response_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Konfirmasi kehadiran berhasil disimpan.');
    }

    /**
     * Ambil ID grup yang boleh dilihat pengguna.
     */
    private function visibleGroupIds($user)
    {
        if ($this->familyAccess->hasGlobalPermission($user, 'agendas.view')) {
            return DB::table('family_groups')
                ->where('status', 'active')
                ->pluck('id');
        }

        return DB::table('family_persons')
            ->join(
                'family_members',
                'family_members.family_person_id',
                '=',
                'family_persons.id'
            )
            ->join(
                'family_groups',
                'family_groups.id',
                '=',
                'family_members.family_group_id'
            )
            ->join('roles', 'roles.id', '=', 'family_members.role_id')
            ->join('role_permissions', 'role_permissions.role_id', '=', 'roles.id')
            ->join(
                'permissions',
                'permissions.id',
                '=',
                'role_permissions.permission_id'
            )
            ->where('family_persons.user_id', $user->id)
            ->where('family_members.status', 'active')
            ->where('family_members.role_scope', 'group')
            ->where('family_groups.status', 'active')
            ->where('roles.scope', 'group')
            ->where('permissions.slug', 'agendas.view')
            ->distinct()
            ->pluck('family_groups.id');
    }

    /**
     * Ambil ID grup tempat pengguna memiliki permission tertentu.
     */
    private function manageableGroupIds($user, string $permission)
    {
        if ($this->familyAccess->hasGlobalPermission($user, $permission)) {
            return DB::table('family_groups')
                ->where('status', 'active')
                ->pluck('id');
        }

        return DB::table('family_persons')
            ->join(
                'family_members',
                'family_members.family_person_id',
                '=',
                'family_persons.id'
            )
            ->join(
                'family_groups',
                'family_groups.id',
                '=',
                'family_members.family_group_id'
            )
            ->join('roles', 'roles.id', '=', 'family_members.role_id')
            ->join('role_permissions', 'role_permissions.role_id', '=', 'roles.id')
            ->join(
                'permissions',
                'permissions.id',
                '=',
                'role_permissions.permission_id'
            )
            ->where('family_persons.user_id', $user->id)
            ->where('family_members.status', 'active')
            ->where('family_members.role_scope', 'group')
            ->where('family_groups.status', 'active')
            ->where('roles.scope', 'group')
            ->where('permissions.slug', $permission)
            ->distinct()
            ->pluck('family_groups.id');
    }

    /**
     * Daftar anggota aktif dari satu grup.
     */
    private function activeMembers(int $groupId)
    {
        return DB::table('family_members')
            ->join(
                'family_persons',
                'family_persons.id',
                '=',
                'family_members.family_person_id'
            )
            ->where('family_members.family_group_id', $groupId)
            ->where('family_members.status', 'active')
            ->where('family_members.role_scope', 'group')
            ->select(
                'family_members.id',
                'family_members.family_person_id',
                'family_persons.full_name',
                'family_persons.nickname'
            )
            ->orderBy('family_persons.full_name')
            ->get();
    }

    /**
     * Cari agenda yang berada dalam grup yang boleh dikelola.
     */
    private function findManageableAgenda(
        Request $request,
        int $agendaId,
        string $permission
    ): FamilyAgenda {
        $agenda = FamilyAgenda::query()
            ->whereKey($agendaId)
            ->firstOrFail();

        abort_unless(
            $this->familyAccess->hasPermission(
                $request->user('family'),
                $permission,
                $agenda->family_group_id
            ),
            404
        );

        return $agenda;
    }
}