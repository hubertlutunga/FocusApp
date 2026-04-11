<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\NumberSequence;
use App\Models\Payment;
use Throwable;

final class PaymentController extends Controller
{
    public function index(): void
    {
        $this->render('payments.index', [
            'pageTitle' => 'Paiements',
            'payments' => (new Payment())->all(),
            'openInvoices' => (new Invoice())->payableOptions(),
        ]);
    }

    public function create(): void
    {
        $this->render('payments.form', [
            'pageTitle' => 'Nouveau paiement',
            'openInvoices' => (new Invoice())->payableOptions(),
            'formAction' => url('/payments/store'),
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $redirectTo = (string) ($_POST['redirect_to'] ?? '');
        if ($redirectTo === '' || $redirectTo[0] !== '/') {
            $redirectTo = '/payments';
        }
        $expectsJson = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
            || str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json');

        $payload = [
            'invoice_id' => (int) ($_POST['invoice_id'] ?? 0),
            'payment_number' => '',
            'payment_date' => (string) ($_POST['payment_date'] ?? date('Y-m-d')),
            'amount' => (float) ($_POST['amount'] ?? 0),
            'method' => (string) ($_POST['method'] ?? 'cash'),
            'reference' => trim((string) ($_POST['reference'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'received_by' => (int) (Auth::id() ?? 0),
        ];

        Session::set('old_input', $payload);

        if ($payload['invoice_id'] <= 0 || $payload['amount'] <= 0) {
            $this->respondPaymentError($expectsJson, $redirectTo, 'Veuillez choisir une facture et un montant valide.', 422);
        }

        try {
            $invoiceModel = new Invoice();
            $payload['payment_number'] = (new NumberSequence())->next('payment');
            $paymentId = (new Payment())->createForInvoice($payload);
            $invoice = $invoiceModel->find($payload['invoice_id']);
            (new ActivityLog())->log('create', 'Enregistrement du paiement ' . $payload['payment_number'], 'paiements', Auth::id());
            Session::forget('old_input');

            if ($expectsJson) {
                $this->respondJson([
                    'success' => true,
                    'message' => 'Le paiement a été enregistré avec succès.',
                    'payment' => [
                        'id' => $paymentId,
                        'number' => $payload['payment_number'],
                        'amount' => $payload['amount'],
                    ],
                    'invoice' => [
                        'id' => (int) ($invoice['id'] ?? 0),
                        'status' => (string) ($invoice['status'] ?? ''),
                        'status_label' => status_label((string) ($invoice['status'] ?? '')),
                        'status_class' => status_badge_class((string) ($invoice['status'] ?? '')),
                        'amount_paid' => (float) ($invoice['amount_paid'] ?? 0),
                        'balance_due' => (float) ($invoice['balance_due'] ?? 0),
                        'amount_paid_label' => number_format((float) ($invoice['amount_paid'] ?? 0), 2, ',', ' '),
                        'balance_due_label' => number_format((float) ($invoice['balance_due'] ?? 0), 2, ',', ' '),
                    ],
                ]);
            }

            Session::flash('alert', ['icon' => 'success', 'title' => 'Paiement enregistré', 'text' => 'Le paiement a été enregistré avec succès.']);
            $this->redirect($redirectTo);
        } catch (Throwable $throwable) {
            $this->respondPaymentError($expectsJson, $redirectTo, $throwable->getMessage() ?: 'Impossible d’enregistrer ce paiement.', 422);
        }
    }

    private function respondPaymentError(bool $expectsJson, string $redirectTo, string $message, int $statusCode): never
    {
        if ($expectsJson) {
            $this->respondJson([
                'success' => false,
                'message' => $message,
            ], $statusCode);
        }

        Session::flash('alert', ['icon' => 'error', 'title' => 'Paiement impossible', 'text' => $message]);
        $this->redirect($redirectTo);
    }

    private function respondJson(array $payload, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
