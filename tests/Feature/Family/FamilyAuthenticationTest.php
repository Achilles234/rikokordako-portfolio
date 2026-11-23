<?php

namespace Tests\Feature\Family;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FamilyAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Authentication Test',
            'email' => $email,
            'password' => 'test-password',
        ]);
    }

    private function createAuthorizedFamilyMember(string $email): User
    {
        $user = $this->createUser($email);

        $groupId = DB::table('family_groups')->insertGetId([
            'name' => 'Authentication Test Family',
            'slug' => 'authentication-test-family',
            'group_type' => 'nuclear',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personId = DB::table('family_persons')->insertGetId([
            'user_id' => $user->id,
            'full_name' => 'Authentication Test Member',
            'life_status' => 'alive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleId = DB::table('roles')
    ->where('slug', 'family_member')
    ->where('scope', 'group')
    ->value('id');

if (!$roleId) {
    $roleId = DB::table('roles')->insertGetId([
        'name' => 'Family Member',
        'slug' => 'family_member',
        'scope' => 'group',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

        DB::table('family_members')->insert([
            'family_group_id' => $groupId,
            'family_person_id' => $personId,
            'role_id' => $roleId,
            'role_scope' => 'group',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    public function test_guest_is_redirected_to_family_login_when_opening_dashboard(): void
    {
        $response = $this->get('/family/dashboard');

        $response->assertRedirect(route('family.login'));
    }

    public function test_guest_can_open_family_login_page(): void
    {
        $response = $this->get('/family/login');

        $response->assertOk();
        $response->assertViewIs('family.auth.login');
    }

    public function test_invalid_credentials_cannot_login(): void
    {
        $user = $this->createUser('invalid@example.test');

        $response = $this->from('/family/login')
            ->post('/family/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect('/family/login');
        $response->assertSessionHasErrors('login');

        $this->assertGuest('family');
    }

    public function test_user_without_family_access_cannot_login(): void
    {
        $user = $this->createUser('unauthorized@example.test');

        $response = $this->from('/family/login')
            ->post('/family/login', [
                'email' => $user->email,
                'password' => 'test-password',
            ]);

        $response->assertRedirect('/family/login');
        $response->assertSessionHasErrors('login');

        $this->assertGuest('family');
    }

    public function test_authorized_family_member_can_login(): void
    {
        $user = $this->createAuthorizedFamilyMember(
            'authorized@example.test'
        );

        $response = $this->post('/family/login', [
            'email' => $user->email,
            'password' => 'test-password',
        ]);

        $response->assertRedirect(route('family.dashboard'));

        $this->assertAuthenticatedAs($user, 'family');
    }

    public function test_authenticated_family_member_can_open_dashboard(): void
    {
        $user = $this->createAuthorizedFamilyMember(
            'dashboard@example.test'
        );

        $response = $this->actingAs($user, 'family')
            ->get('/family/dashboard');

        $response->assertOk();
        $response->assertViewIs('family.dashboard');
    }

    public function test_family_logout_does_not_log_out_web_guard(): void
    {
        $user = $this->createAuthorizedFamilyMember(
            'dual-guard@example.test'
        );

        $this->actingAs($user, 'web');

        $loginResponse = $this->post('/family/login', [
            'email' => $user->email,
            'password' => 'test-password',
        ]);

        $loginResponse->assertRedirect(route('family.dashboard'));

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertAuthenticatedAs($user, 'family');

        $logoutResponse = $this->post('/family/logout');

        $logoutResponse->assertRedirect(route('family.login'));

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertGuest('family');
    }


    public function test_login_does_not_redirect_to_portfolio_intended_url(): void
    {
        $user = $this->createAuthorizedFamilyMember(
            'intended-redirect@example.test'
        );

        $response = $this
            ->withSession([
                'url.intended' => '/en',
            ])
            ->post('/family/login', [
                'email' => $user->email,
                'password' => 'test-password',
            ]);

        $response->assertRedirect(route('family.dashboard'));
    }

}
