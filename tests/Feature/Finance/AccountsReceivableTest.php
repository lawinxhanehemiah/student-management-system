<?php
// tests/Feature/AccountsReceivableTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\CreditNote;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountsReceivableTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'finance']);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_view_outstanding_invoices()
    {
        Invoice::factory()->count(5)->create([
            'balance' => 100000,
            'payment_status' => 'unpaid'
        ]);

        $response = $this->get(route('finance.accounts-receivable.outstanding'));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function can_create_credit_note()
    {
        $invoice = Invoice::factory()->create([
            'paid_amount' => 500000,
            'balance' => 0
        ]);

        $response = $this->post(route('finance.credit-notes.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 100000,
            'reason' => 'Overpayment',
            'description' => 'Test credit note'
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('credit_notes', [
            'invoice_id' => $invoice->id,
            'amount' => 100000,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function can_apply_credit_note()
    {
        $creditNote = CreditNote::factory()->create([
            'amount' => 100000,
            'remaining_amount' => 100000,
            'status' => 'active'
        ]);

        $targetInvoice = Invoice::factory()->create([
            'balance' => 200000,
            'payment_status' => 'unpaid'
        ]);

        $response = $this->post(route('finance.credit-notes.apply', $creditNote->id), [
            'target_invoice_id' => $targetInvoice->id,
            'amount' => 50000
        ]);

        $response->assertJson(['success' => true]);
        
        $this->assertEquals(50000, $creditNote->fresh()->remaining_amount);
        $this->assertEquals(150000, $targetInvoice->fresh()->balance);
    }

    /** @test */
    public function can_request_refund()
    {
        $payment = \App\Models\Payment::factory()->create([
            'amount' => 200000,
            'status' => 'completed'
        ]);

        $response = $this->post(route('finance.refunds.store'), [
            'payment_id' => $payment->id,
            'amount' => 50000,
            'refund_method' => 'bank_transfer',
            'refund_reason' => 'Overpayment',
            'bank_name' => 'NMB',
            'bank_account' => '1234567890'
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('refunds', [
            'payment_id' => $payment->id,
            'amount' => 50000,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function can_approve_refund()
    {
        $refund = Refund::factory()->create(['status' => 'pending']);

        $response = $this->post(route('finance.refunds.approve', $refund->id), [
            'notes' => 'Approved by finance'
        ]);

        $response->assertJson(['success' => true]);
        $this->assertEquals('approved', $refund->fresh()->status);
    }

    /** @test */
    public function aging_report_shows_correct_buckets()
    {
        // Create invoices with different due dates
        Invoice::factory()->create([
            'due_date' => now()->addDays(10),
            'balance' => 100000
        ]);

        Invoice::factory()->create([
            'due_date' => now()->subDays(15),
            'balance' => 200000
        ]);

        Invoice::factory()->create([
            'due_date' => now()->subDays(45),
            'balance' => 300000
        ]);

        $response = $this->get(route('finance.accounts-receivable.aging'));

        $response->assertStatus(200);
        $response->assertViewHas('aging');
    }

    /** @test */
    public function can_send_reminders()
    {
        $invoices = Invoice::factory()->count(3)->create([
            'due_date' => now()->subDays(30),
            'balance' => 100000,
            'payment_status' => 'unpaid'
        ]);

        $response = $this->post(route('finance.accounts-receivable.send-reminders'), [
            'invoice_ids' => $invoices->pluck('id')->toArray(),
            'reminder_type' => 'email'
        ]);

        $response->assertJson(['success' => true]);
        
        foreach ($invoices as $invoice) {
            $this->assertNotNull($invoice->fresh()->last_reminder_sent_at);
            $this->assertEquals(1, $invoice->fresh()->reminder_count);
        }
    }

    /** @test */
    public function can_write_off_invoice()
    {
        $invoice = Invoice::factory()->create([
            'balance' => 500000,
            'payment_status' => 'unpaid'
        ]);

        $response = $this->post(route('finance.accounts-receivable.write-off', $invoice->id), [
            'amount' => 500000,
            'reason' => 'Bad debt'
        ]);

        $response->assertJson(['success' => true]);
        
        $this->assertEquals(500000, $invoice->fresh()->write_off_amount);
        $this->assertEquals(0, $invoice->fresh()->balance);
        $this->assertEquals('written_off', $invoice->fresh()->collection_status);
    }
}