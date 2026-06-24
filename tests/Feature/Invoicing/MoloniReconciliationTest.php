<?php

use App\Jobs\GenerateExternalInvoiceJob;
use App\Models\User;
use App\Notifications\MoloniInvoiceFailedNotification;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Invoicing\Models\MoloniInvoice;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    // Create required document type
    DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    PaymentMethod::factory()->create(['handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler']);
});

describe('ReconcileMoloniInvoices Command', function () {

    it('skips when Moloni is disabled', function () {
        config(['invoicing.providers.moloni.enabled' => false]);

        artisan('moloni:reconcile')
            ->expectsOutput('Moloni integration is disabled. Skipping reconciliation.')
            ->assertSuccessful();
    });

    it('skips when Moloni is not configured', function () {
        // Enable Moloni but don't configure it
        config(['invoicing.providers.moloni.enabled' => true]);

        // This will fail at isConfigured() check since no settings exist
        artisan('moloni:reconcile')
            ->expectsOutput('Moloni is not fully configured. Skipping reconciliation.')
            ->assertSuccessful();
    });

    it('supports dry-run option', function () {
        config(['invoicing.providers.moloni.enabled' => false]);

        // Just verify the command accepts the dry-run flag
        artisan('moloni:reconcile --dry-run')
            ->assertSuccessful();
    });

    it('supports hours option', function () {
        config(['invoicing.providers.moloni.enabled' => false]);

        // Just verify the command accepts the hours flag
        artisan('moloni:reconcile --hours=48')
            ->assertSuccessful();
    });

    it('supports limit option', function () {
        config(['invoicing.providers.moloni.enabled' => false]);

        // Just verify the command accepts the limit flag
        artisan('moloni:reconcile --limit=10')
            ->assertSuccessful();
    });

});

describe('MoloniInvoiceFailedNotification', function () {

    it('sends notification when job fails permanently', function () {
        Notification::fake();

        config(['invoicing.providers.moloni.alert_email' => 'admin@example.com']);

        $document = Document::factory()->create([
            'status_class' => PaidDocumentState::class,
            'total_value' => 100.00,
        ]);

        $transaction = PaymentTransaction::factory()->create([
            'document_id' => $document->id,
            'amount' => 100.00,
            'status' => 'success',
        ]);

        $job = new GenerateExternalInvoiceJob($document, $transaction, []);

        // Simulate job failure
        $exception = new \Exception('Moloni API timeout');
        $job->failed($exception);

        // Verify notification was sent to configured email
        Notification::assertSentOnDemand(
            MoloniInvoiceFailedNotification::class,
            function ($notification, $channels, $notifiable) use ($document) {
                return $notifiable->routes['mail'] === 'admin@example.com'
                    && $notification->document->id === $document->id
                    && $notification->errorMessage === 'Moloni API timeout';
            }
        );
    });

    it('notifies admin users with access settings permission', function () {
        Notification::fake();

        // Create admin user with permission
        $admin = User::factory()->create();
        $admin->givePermissionTo('access settings');

        $document = Document::factory()->create([
            'status_class' => PaidDocumentState::class,
            'total_value' => 100.00,
        ]);

        $transaction = PaymentTransaction::factory()->create([
            'document_id' => $document->id,
            'amount' => 100.00,
            'status' => 'success',
        ]);

        $job = new GenerateExternalInvoiceJob($document, $transaction, []);
        $job->failed(new \Exception('API Error'));

        // Verify admin was notified
        Notification::assertSentTo(
            $admin,
            MoloniInvoiceFailedNotification::class,
            function ($notification) use ($document) {
                return $notification->document->id === $document->id;
            }
        );
    });

    it('includes correct data in notification', function () {
        $document = Document::factory()->create([
            'status_class' => PaidDocumentState::class,
            'total_value' => 250.00,
            'number_extended' => '2024/0001',
        ]);

        $notification = new MoloniInvoiceFailedNotification(
            $document,
            'Connection timeout',
            3
        );

        $arrayData = $notification->toArray(new \stdClass);

        expect($arrayData['type'])->toBe('moloni_invoice_failed')
            ->and($arrayData['document_id'])->toBe($document->id)
            ->and($arrayData['document_number'])->toBe('2024/0001')
            ->and($arrayData['error_message'])->toBe('Connection timeout')
            ->and($arrayData['attempts'])->toBe(3);
    });

    it('does not throw when no alert email configured', function () {
        Notification::fake();

        config(['invoicing.providers.moloni.alert_email' => null]);

        $document = Document::factory()->create([
            'status_class' => PaidDocumentState::class,
            'total_value' => 100.00,
        ]);

        $transaction = PaymentTransaction::factory()->create([
            'document_id' => $document->id,
            'amount' => 100.00,
            'status' => 'success',
        ]);

        $job = new GenerateExternalInvoiceJob($document, $transaction, []);

        // Should not throw even without alert email
        expect(fn () => $job->failed(new \Exception('API Error')))->not->toThrow(\Exception::class);
    });

});

describe('GenerateExternalInvoiceJob', function () {

    it('uses the document id as its unique queue key', function () {
        $document = Document::factory()->create([
            'status_class' => PaidDocumentState::class,
            'total_value' => 100.00,
        ]);

        $transaction = PaymentTransaction::factory()->create([
            'document_id' => $document->id,
            'amount' => 100.00,
            'status' => 'success',
        ]);

        $job = new GenerateExternalInvoiceJob($document, $transaction, []);

        expect($job->uniqueId())->toBe($document->id)
            ->and($job->uniqueFor)->toBe(300);
    });

});

describe('MoloniInvoice Model', function () {

    it('detects existing invoice for document', function () {
        $document = Document::factory()->create();

        expect(MoloniInvoice::existsForDocument($document->id))->toBeFalse();

        MoloniInvoice::create([
            'document_id' => $document->id,
            'moloni_document_id' => 12345,
            'moloni_number' => 'FR 2024/1',
            'moloni_status' => 'closed',
            'moloni_total' => 100.00,
            'synced_at' => now(),
        ]);

        expect(MoloniInvoice::existsForDocument($document->id))->toBeTrue();
    });

    it('finds invoice by document id', function () {
        $document = Document::factory()->create();

        MoloniInvoice::create([
            'document_id' => $document->id,
            'moloni_document_id' => 12345,
            'moloni_number' => 'FR 2024/1',
            'moloni_status' => 'closed',
            'moloni_total' => 100.00,
            'synced_at' => now(),
        ]);

        $found = MoloniInvoice::findByDocument($document->id);

        expect($found)->not->toBeNull()
            ->and($found->moloni_document_id)->toBe(12345)
            ->and($found->moloni_number)->toBe('FR 2024/1');
    });

});
