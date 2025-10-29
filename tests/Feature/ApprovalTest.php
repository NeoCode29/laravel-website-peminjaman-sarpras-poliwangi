<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Peminjaman;
use App\Models\Sarana;
use App\Models\Prasarana;
use App\Models\GlobalApprover;
use App\Models\SaranaApprover;
use App\Models\PrasaranaApprover;
use App\Models\PeminjamanApprovalWorkflow;
use App\Models\PeminjamanApprovalStatus;
use App\Services\PeminjamanApprovalService;

class ApprovalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $approver;
    protected $peminjaman;
    protected $sarana;
    protected $prasarana;
    protected $approvalService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test approver
        $this->approver = User::factory()->create();
        
        // Create test sarana
        $this->sarana = Sarana::factory()->create();
        
        // Create test prasarana
        $this->prasarana = Prasarana::factory()->create();
        
        // Create test peminjaman
        $this->peminjaman = Peminjaman::factory()->create([
            'user_id' => $this->user->id,
            'prasarana_id' => $this->prasarana->id,
            'status' => Peminjaman::STATUS_PENDING
        ]);
        
        // Create peminjaman items
        $this->peminjaman->items()->create([
            'sarana_id' => $this->sarana->id,
            'qty_requested' => 2,
            'qty_approved' => 0
        ]);
        
        $this->approvalService = new PeminjamanApprovalService();
    }

    /** @test */
    public function can_create_approval_workflow()
    {
        // Create global approver
        GlobalApprover::create([
            'approver_id' => $this->approver->id,
            'approval_level' => 1,
            'is_active' => true
        ]);
        
        // Create sarana approver
        SaranaApprover::create([
            'sarana_id' => $this->sarana->id,
            'approver_id' => $this->approver->id,
            'approval_level' => 1,
            'is_active' => true
        ]);
        
        // Create prasarana approver
        PrasaranaApprover::create([
            'prasarana_id' => $this->prasarana->id,
            'approver_id' => $this->approver->id,
            'approval_level' => 1,
            'is_active' => true
        ]);
        
        // Create approval workflow
        $result = $this->approvalService->createApprovalWorkflow($this->peminjaman->id);
        
        $this->assertTrue($result);
        
        // Check if approval status was created
        $this->assertDatabaseHas('peminjaman_approval_status', [
            'peminjaman_id' => $this->peminjaman->id,
            'overall_status' => 'pending',
            'global_approval_status' => 'pending'
        ]);
        
        // Check if workflows were created
        $this->assertDatabaseHas('peminjaman_approval_workflow', [
            'peminjaman_id' => $this->peminjaman->id,
            'approver_id' => $this->approver->id,
            'approval_type' => 'global',
            'status' => 'pending'
        ]);
        
        $this->assertDatabaseHas('peminjaman_approval_workflow', [
            'peminjaman_id' => $this->peminjaman->id,
            'approver_id' => $this->approver->id,
            'approval_type' => 'sarana',
            'sarana_id' => $this->sarana->id,
            'status' => 'pending'
        ]);
        
        $this->assertDatabaseHas('peminjaman_approval_workflow', [
            'peminjaman_id' => $this->peminjaman->id,
            'approver_id' => $this->approver->id,
            'approval_type' => 'prasarana',
            'prasarana_id' => $this->prasarana->id,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function can_approve_global_approval()
    {
        // Setup approval workflow
        $this->setupApprovalWorkflow();
        
        // Approve global
        $result = $this->approvalService->approveGlobal(
            $this->peminjaman->id, 
            $this->approver->id, 
            'Test approval notes'
        );
        
        $this->assertTrue($result);
        
        // Check if workflow was approved
        $this->assertDatabaseHas('peminjaman_approval_workflow', [
            'peminjaman_id' => $this->peminjaman->id,
            'approver_id' => $this->approver->id,
            'approval_type' => 'global',
            'status' => 'approved'
        ]);
        
        // Check if approval status was updated
        $this->assertDatabaseHas('peminjaman_approval_status', [
            'peminjaman_id' => $this->peminjaman->id,
            'global_approval_status' => 'approved'
        ]);
    }

    /** @test */
    public function can_reject_global_approval()
    {
        // Setup approval workflow
        $this->setupApprovalWorkflow();
        
        // Reject global
        $result = $this->approvalService->rejectGlobal(
            $this->peminjaman->id, 
            $this->approver->id, 
            'Test rejection reason'
        );
        
        $this->assertTrue($result);
        
        // Check if workflow was rejected
        $this->assertDatabaseHas('peminjaman_approval_workflow', [
            'peminjaman_id' => $this->peminjaman->id,
            'approver_id' => $this->approver->id,
            'approval_type' => 'global',
            'status' => 'rejected'
        ]);
        
        // Check if approval status was updated
        $this->assertDatabaseHas('peminjaman_approval_status', [
            'peminjaman_id' => $this->peminjaman->id,
            'global_approval_status' => 'rejected',
            'overall_status' => 'rejected'
        ]);
    }

    /** @test */
    public function can_approve_specific_sarana()
    {
        // Setup approval workflow
        $this->setupApprovalWorkflow();
        
        // Approve specific sarana
        $result = $this->approvalService->approveSpecificSarana(
            $this->peminjaman->id, 
            $this->sarana->id, 
            $this->approver->id, 
            'Test sarana approval'
        );
        
        $this->assertTrue($result);
        
        // Check if workflow was approved
        $this->assertDatabaseHas('peminjaman_approval_workflow', [
            'peminjaman_id' => $this->peminjaman->id,
            'approver_id' => $this->approver->id,
            'approval_type' => 'sarana',
            'sarana_id' => $this->sarana->id,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function can_approve_specific_prasarana()
    {
        // Setup approval workflow
        $this->setupApprovalWorkflow();
        
        // Approve specific prasarana
        $result = $this->approvalService->approveSpecificPrasarana(
            $this->peminjaman->id, 
            $this->prasarana->id, 
            $this->approver->id, 
            'Test prasarana approval'
        );
        
        $this->assertTrue($result);
        
        // Check if workflow was approved
        $this->assertDatabaseHas('peminjaman_approval_workflow', [
            'peminjaman_id' => $this->peminjaman->id,
            'approver_id' => $this->approver->id,
            'approval_type' => 'prasarana',
            'prasarana_id' => $this->prasarana->id,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function can_get_approval_status()
    {
        // Setup approval workflow
        $this->setupApprovalWorkflow();
        
        // Get approval status
        $status = $this->approvalService->getApprovalStatus($this->peminjaman->id);
        
        $this->assertNotNull($status);
        $this->assertArrayHasKey('approval_status', $status);
        $this->assertArrayHasKey('workflows', $status);
        $this->assertArrayHasKey('global_workflows', $status);
        $this->assertArrayHasKey('sarana_workflows', $status);
        $this->assertArrayHasKey('prasarana_workflows', $status);
    }

    /** @test */
    public function can_get_pending_approvals()
    {
        // Setup approval workflow
        $this->setupApprovalWorkflow();
        
        // Get pending approvals
        $workflows = $this->approvalService->getPendingApprovals($this->approver->id);
        
        $this->assertCount(3, $workflows); // global, sarana, prasarana
        $this->assertTrue($workflows->every(fn($workflow) => $workflow->status === 'pending'));
    }

    private function setupApprovalWorkflow()
    {
        // Create global approver
        GlobalApprover::create([
            'approver_id' => $this->approver->id,
            'approval_level' => 1,
            'is_active' => true
        ]);
        
        // Create sarana approver
        SaranaApprover::create([
            'sarana_id' => $this->sarana->id,
            'approver_id' => $this->approver->id,
            'approval_level' => 1,
            'is_active' => true
        ]);
        
        // Create prasarana approver
        PrasaranaApprover::create([
            'prasarana_id' => $this->prasarana->id,
            'approver_id' => $this->approver->id,
            'approval_level' => 1,
            'is_active' => true
        ]);
        
        // Create approval workflow
        $this->approvalService->createApprovalWorkflow($this->peminjaman->id);
    }
}

