<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockMovement;
use Throwable;

final class StockController extends Controller
{
    public function index(): void
    {
        $productModel = new Product();

        $this->render('stock.index', [
            'pageTitle' => 'Stock & mouvements',
            'movements' => (new StockMovement())->all(),
            'products' => $productModel->options(),
            'lowStockProducts' => $productModel->lowStock(),
        ]);
    }

    public function adjust(): void
    {
        verify_csrf();

        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (float) ($_POST['quantity'] ?? 0);
        $note = trim((string) ($_POST['note'] ?? ''));

        Session::set('old_input', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'note' => $note,
        ]);

        if ($productId <= 0 || $quantity <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Produit et quantité strictement positive sont obligatoires.']);
            $this->redirect('/stock');
        }

        if (isset($_POST['direction']) && (string) $_POST['direction'] === 'exit') {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Sortie interdite', 'text' => 'Les sorties de stock sont générées automatiquement par les factures validées en caisse.']);
            $this->redirect('/stock');
        }

        $productModel = new Product();
        $movementModel = new StockMovement();
        $product = $productModel->find($productId);

        if (!$product) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Produit introuvable', 'text' => 'Le produit sélectionné n’existe pas.']);
            $this->redirect('/stock');
        }

        $before = (float) $product['current_stock'];
        $signedQuantity = $quantity;
        $after = $before + $signedQuantity;

        try {
            $productModel->adjustStock($productId, $after);
            $movementModel->create([
                'product_id' => $productId,
                'movement_type' => 'entry',
                'quantity' => $signedQuantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_type' => 'manual',
                'reference_id' => null,
                'note' => $note !== '' ? $note : 'Entrée manuelle de stock',
                'movement_date' => date('Y-m-d H:i:s'),
                'created_by' => Auth::id(),
            ]);
            (new ActivityLog())->log('adjust', 'Entrée manuelle de stock du produit ' . $product['name'], 'stock', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Entrée enregistrée', 'text' => 'L’entrée de stock a été enregistrée.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Entrée impossible', 'text' => 'Impossible d’enregistrer cette entrée de stock.']);
        }

        $this->redirect('/stock');
    }
}
