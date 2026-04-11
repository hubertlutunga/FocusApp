<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\NumberSequence;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Services\DocumentPdfService;
use Throwable;

final class InvoiceController extends Controller
{
    public function index(): void
    {
        $this->render('invoices.index', [
            'pageTitle' => 'Factures',
            'invoices' => (new Invoice())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('invoices.form', [
            'pageTitle' => 'Nouvelle facture',
            'clients' => (new Client())->options(),
            'products' => (new Product())->options(),
            'services' => (new Service())->options(),
            'formAction' => url('/invoices/store'),
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $header = [
            'quote_id' => null,
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'invoice_number' => '',
            'invoice_date' => (string) ($_POST['invoice_date'] ?? date('Y-m-d')),
            'due_date' => ($_POST['due_date'] ?? '') !== '' ? (string) $_POST['due_date'] : null,
            'status' => 'draft',
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 0,
            'amount_paid' => 0,
            'balance_due' => 0,
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'validated_at' => null,
            'cancelled_at' => null,
            'created_by' => (int) (Auth::id() ?? 0),
            'validated_by' => null,
        ];

        $items = $this->normalizeItems($_POST['items'] ?? []);
        Session::set('old_input', [
            'client_id' => $header['client_id'],
            'invoice_date' => $header['invoice_date'],
            'due_date' => $header['due_date'],
            'notes' => $header['notes'],
            'items' => $items,
        ]);

        if ($header['client_id'] <= 0 || $items === []) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Veuillez sélectionner un client et au moins une ligne.']);
            $this->redirect('/invoices/create');
        }

        foreach ($items as &$item) {
            $item['line_total'] = $item['quantity'] * $item['unit_price'];
            $header['subtotal'] += $item['line_total'];
        }
        unset($item);
        $header['grand_total'] = $header['subtotal'];
        $header['balance_due'] = $header['grand_total'];

        try {
            $header['invoice_number'] = (new NumberSequence())->next('invoice');
            $invoiceId = (new Invoice())->createWithItems($header, $items);
            (new ActivityLog())->log('create', 'Création de la facture ' . $header['invoice_number'], 'factures', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Facture créée', 'text' => 'La facture a été enregistrée en brouillon.']);
            $this->redirect('/invoices/show?id=' . $invoiceId);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible d’enregistrer cette facture.']);
            $this->redirect('/invoices/create');
        }
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->find($id);

        if (!$invoice) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Facture introuvable', 'text' => 'La facture demandée n’existe pas.']);
            $this->redirect('/invoices');
        }

        $this->render('invoices.show', [
            'pageTitle' => 'Détail facture',
            'invoice' => $invoice,
            'items' => $invoiceModel->items($id),
            'payments' => (new Payment())->byInvoice($id),
        ]);
    }

    public function validate(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);

        try {
            (new Invoice())->validateInvoice($id, (int) (Auth::id() ?? 0));
            (new ActivityLog())->log('validate', 'Validation de la facture #' . $id, 'factures', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Facture validée', 'text' => 'La facture a été validée et le stock a été ajusté.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Validation impossible', 'text' => $throwable->getMessage() ?: 'Impossible de valider cette facture.']);
        }

        $this->redirect('/invoices/show?id=' . $id);
    }

    public function cancel(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);

        try {
            (new Invoice())->cancelInvoice($id, (int) (Auth::id() ?? 0));
            (new ActivityLog())->log('cancel', 'Annulation de la facture #' . $id, 'factures', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Facture annulée', 'text' => 'La facture a été annulée et le stock restauré si nécessaire.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Annulation impossible', 'text' => 'Impossible d’annuler cette facture.']);
        }

        $this->redirect('/invoices/show?id=' . $id);
    }

    public function pdf(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->find($id);

        if (!$invoice) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Facture introuvable', 'text' => 'La facture demandée n’existe pas.']);
            $this->redirect('/invoices');
        }

        (new DocumentPdfService())->streamInvoice($invoice, $invoiceModel->items($id));
    }

    private function normalizeItems(array $input): array
    {
        $types = $input['item_type'] ?? [];
        $productIds = $input['product_id'] ?? [];
        $serviceIds = $input['service_id'] ?? [];
        $descriptions = $input['description'] ?? [];
        $quantities = $input['quantity'] ?? [];
        $unitPrices = $input['unit_price'] ?? [];
        $items = [];

        foreach ($types as $index => $type) {
            $itemType = (string) $type;
            $productId = (int) ($productIds[$index] ?? 0);
            $serviceId = (int) ($serviceIds[$index] ?? 0);
            $quantity = (float) ($quantities[$index] ?? 0);
            $unitPrice = (float) ($unitPrices[$index] ?? 0);
            $description = trim((string) ($descriptions[$index] ?? ''));

            if (!in_array($itemType, ['product', 'service'], true) || $quantity <= 0 || $unitPrice < 0) {
                continue;
            }

            if ($itemType === 'product' && $productId <= 0) {
                continue;
            }
            if ($itemType === 'service' && $serviceId <= 0) {
                continue;
            }

            $items[] = [
                'item_type' => $itemType,
                'product_id' => $itemType === 'product' ? $productId : null,
                'service_id' => $itemType === 'service' ? $serviceId : null,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'line_total' => $quantity * $unitPrice,
            ];
        }

        return $items;
    }
}
