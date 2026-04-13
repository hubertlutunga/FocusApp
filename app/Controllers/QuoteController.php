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
use App\Models\Product;
use App\Models\Quote;
use App\Models\Service;
use App\Services\DocumentPdfService;
use Throwable;

final class QuoteController extends Controller
{
    public function index(): void
    {
        $this->render('quotes.index', [
            'pageTitle' => 'Devis',
            'quotes' => (new Quote())->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('quotes.form', [
            'pageTitle' => 'Nouveau devis',
            'clients' => (new Client())->options(),
            'products' => (new Product())->options(),
            'services' => (new Service())->options(),
            'formAction' => url('/quotes/store'),
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $header = [
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'quote_number' => '',
            'quote_date' => (string) ($_POST['quote_date'] ?? date('Y-m-d')),
            'valid_until' => ($_POST['valid_until'] ?? '') !== '' ? (string) $_POST['valid_until'] : null,
            'status' => (string) ($_POST['status'] ?? 'draft'),
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_rate' => normalize_tax_rate($_POST['tax_rate'] ?? 0),
            'tax_amount' => 0,
            'grand_total' => 0,
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'created_by' => (int) (Auth::id() ?? 0),
        ];

        $items = $this->normalizeItems($_POST['items'] ?? []);
        Session::set('old_input', [
            'client_id' => $header['client_id'],
            'quote_date' => $header['quote_date'],
            'valid_until' => $header['valid_until'],
            'status' => $header['status'],
            'tax_rate' => $header['tax_rate'],
            'notes' => $header['notes'],
            'items' => $items,
        ]);

        if ($header['client_id'] <= 0 || $items === []) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Veuillez sélectionner un client et au moins une ligne.']);
            $this->redirect('/quotes/create');
        }

        foreach ($items as &$item) {
            $item['line_total'] = $item['quantity'] * $item['unit_price'];
            $item['tax_amount'] = round($item['line_total'] * ($header['tax_rate'] / 100), 2);
            $header['subtotal'] += $item['line_total'];
        }
        unset($item);
        $header['tax_amount'] = round($header['subtotal'] * ($header['tax_rate'] / 100), 2);
        $header['grand_total'] = $header['subtotal'] + $header['tax_amount'];

        try {
            $header['quote_number'] = (new NumberSequence())->next('quote');
            $quoteId = (new Quote())->createWithItems($header, $items);
            (new ActivityLog())->log('create', 'Création du devis ' . $header['quote_number'], 'devis', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Devis créé', 'text' => 'Le devis a été enregistré avec succès.']);
            $this->redirect('/quotes/show?id=' . $quoteId);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible d’enregistrer ce devis.']);
            $this->redirect('/quotes/create');
        }
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $quoteModel = new Quote();
        $quote = $quoteModel->find($id);

        if (!$quote) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Devis introuvable', 'text' => 'Le devis demandé n’existe pas.']);
            $this->redirect('/quotes');
        }

        $this->render('quotes.show', [
            'pageTitle' => 'Détail devis',
            'quote' => $quote,
            'items' => $quoteModel->items($id),
        ]);
    }

    public function convert(): void
    {
        verify_csrf();
        $quoteId = (int) ($_POST['id'] ?? 0);
        if ($quoteId <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Devis introuvable', 'text' => 'Identifiant devis invalide.']);
            $this->redirect('/quotes');
        }

        try {
            $invoiceNumber = (new NumberSequence())->next('invoice');
            $invoiceId = (new Invoice())->createFromQuote($quoteId, (int) (Auth::id() ?? 0), $invoiceNumber, date('Y-m-d'), date('Y-m-d', strtotime('+7 days')));
            (new Quote())->markConverted($quoteId);
            (new ActivityLog())->log('convert', 'Conversion du devis #' . $quoteId . ' en facture ' . $invoiceNumber, 'devis', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Devis converti', 'text' => 'La facture a été générée à partir du devis.']);
            $this->redirect('/invoices/show?id=' . $invoiceId);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Conversion impossible', 'text' => 'Impossible de convertir ce devis en facture.']);
            $this->redirect('/quotes/show?id=' . $quoteId);
        }
    }

    public function cancel(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);

        try {
            (new Quote())->softCancel($id);
            (new ActivityLog())->log('cancel', 'Annulation du devis #' . $id, 'devis', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Devis annulé', 'text' => 'Le devis a été annulé.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Annulation impossible', 'text' => 'Impossible d’annuler ce devis.']);
        }

        $this->redirect('/quotes');
    }

    public function pdf(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $quoteModel = new Quote();
        $quote = $quoteModel->find($id);

        if (!$quote) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Devis introuvable', 'text' => 'Le devis demandé n’existe pas.']);
            $this->redirect('/quotes');
        }

        (new DocumentPdfService())->streamQuote($quote, $quoteModel->items($id));
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
