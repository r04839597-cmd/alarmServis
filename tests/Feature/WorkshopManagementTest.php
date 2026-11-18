<?php

namespace Tests\Feature;

use App\Models\QuotaRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WorkshopManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    private int $userNumber = 0;

    public function test_admin_can_create_a_workshop_within_the_default_quota(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL);

        $response = $this->actingAs($admin)->post(route('admin_bengkel.workshops.store'), [
            'name' => 'Bengkel Utama',
            'address' => 'Jl. Malioboro 1',
            'phone_number' => '081234567890',
        ]);

        $response->assertRedirect(route('admin_bengkel.workshops.index'));
        $this->assertDatabaseHas('workshops', [
            'admin_user_id' => $admin->id,
            'name' => 'Bengkel Utama',
            'approval_status' => Workshop::STATUS_PENDING,
        ]);
        $this->assertSame(1, $admin->workshops()->count());
    }

    public function test_admin_cannot_create_a_workshop_after_using_the_default_quota(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL);
        $this->createWorkshop($admin, 'Bengkel Pertama');
        $this->createWorkshop($admin, 'Bengkel Kedua');

        $response = $this->actingAs($admin)->post(route('admin_bengkel.workshops.store'), [
            'name' => 'Bengkel Ketiga',
            'address' => 'Jl. Malioboro 3',
            'phone_number' => '081234567890',
        ]);

        $response->assertRedirect(route('admin_bengkel.workshops.index'));
        $response->assertSessionHas('error', 'Kuota bengkel Anda sudah habis. Ajukan tambahan kuota ke Superadmin.');
        $this->assertDatabaseCount('workshops', 2);
    }

    public function test_admin_cannot_update_another_admins_workshop(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL);
        $otherAdmin = $this->createUser(User::ROLE_ADMIN_BENGKEL);
        $workshop = $this->createWorkshop($otherAdmin, 'Bengkel Milik Orang Lain');

        $response = $this->actingAs($admin)->put(route('admin_bengkel.workshops.update', $workshop), [
            'name' => 'Nama Baru',
            'address' => 'Alamat Baru',
            'phone_number' => '081234567890',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('workshops', [
            'id' => $workshop->id,
            'name' => 'Bengkel Milik Orang Lain',
        ]);
    }

    public function test_admin_can_update_and_resubmit_a_rejected_workshop(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL);
        $workshop = $this->createWorkshop($admin, 'Bengkel Lama');
        $workshop->update([
            'approval_status' => Workshop::STATUS_REJECTED,
            'rejection_reason' => 'Data alamat perlu diperbaiki.',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin_bengkel.workshops.update', $workshop),
            [
                'name' => 'Bengkel Diperbaiki',
                'address' => 'Jl. Malioboro 2',
                'phone_number' => '081234567890',
            ]
        );

        $response->assertRedirect(route('admin_bengkel.workshops.index'));
        $this->assertDatabaseHas('workshops', [
            'id' => $workshop->id,
            'name' => 'Bengkel Diperbaiki',
            'approval_status' => Workshop::STATUS_PENDING,
            'rejection_reason' => null,
        ]);
    }

    public function test_admin_can_delete_its_own_workshop(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL);
        $workshop = $this->createWorkshop($admin, 'Bengkel Dihapus');

        $response = $this->actingAs($admin)->delete(
            route('admin_bengkel.workshops.destroy', $workshop)
        );

        $response->assertRedirect(route('admin_bengkel.workshops.index'));
        $this->assertDatabaseMissing('workshops', ['id' => $workshop->id]);
    }

    public function test_superadmin_can_approve_a_workshop_and_activate_its_owner(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL, 'pending_approval');
        $superadmin = $this->createUser(User::ROLE_SUPERADMIN);
        $workshop = $this->createWorkshop($admin, 'Bengkel Menunggu');

        $response = $this->actingAs($superadmin)->post(
            route('superadmin.workshops.approve', $workshop)
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('workshops', [
            'id' => $workshop->id,
            'approval_status' => Workshop::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'status' => 'active',
        ]);
    }

    public function test_non_superadmin_cannot_access_workshop_moderation(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL);

        $response = $this->actingAs($admin)->get(route('superadmin.workshops.index'));

        $response->assertForbidden();
    }

    public function test_superadmin_can_view_quota_requests(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL);
        $superadmin = $this->createUser(User::ROLE_SUPERADMIN);
        QuotaRequest::create([
            'user_id' => $admin->id,
            'requested_slots' => 1,
            'reason' => 'Permintaan untuk bengkel tambahan.',
            'status' => QuotaRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($superadmin)->get(route('superadmin.quota.index'));

        $response->assertOk();
        $response->assertSee('Permintaan untuk bengkel tambahan.');
    }

    public function test_superadmin_can_review_quota_requests(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL);
        $superadmin = $this->createUser(User::ROLE_SUPERADMIN);
        $quotaRequest = QuotaRequest::create([
            'user_id' => $admin->id,
            'requested_slots' => 1,
            'reason' => 'Permintaan untuk bengkel tambahan.',
            'status' => QuotaRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($superadmin)->post(
            route('superadmin.quota.approve', $quotaRequest)
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('quota_requests', [
            'id' => $quotaRequest->id,
            'status' => QuotaRequest::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'workshop_quota' => 3,
        ]);
    }

    public function test_superadmin_can_reject_a_quota_request_without_increasing_quota(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN_BENGKEL);
        $superadmin = $this->createUser(User::ROLE_SUPERADMIN);
        $quotaRequest = QuotaRequest::create([
            'user_id' => $admin->id,
            'requested_slots' => 1,
            'reason' => 'Permintaan untuk bengkel tambahan.',
            'status' => QuotaRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($superadmin)->post(
            route('superadmin.quota.reject', $quotaRequest)
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('quota_requests', [
            'id' => $quotaRequest->id,
            'status' => QuotaRequest::STATUS_REJECTED,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'workshop_quota' => 2,
        ]);
    }

    private function createUser(int $roleId, string $status = 'active'): User
    {
        return User::create([
            'name' => 'Pengguna Tes',
            'phone_number' => '628000'.str_pad((string) ++$this->userNumber, 8, '0', STR_PAD_LEFT),
            'password' => 'password',
            'role_id' => $roleId,
            'status' => $status,
        ]);
    }

    private function createWorkshop(User $admin, string $name): Workshop
    {
        return Workshop::create([
            'admin_user_id' => $admin->id,
            'name' => $name,
            'address' => 'Jl. Malioboro',
            'phone_number' => '6281234567890',
            'approval_status' => Workshop::STATUS_PENDING,
        ]);
    }
}
