<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Throwable;

final class StockController extends Controller
{
    public function index(): void
    {
        $shopId = $this->currentShopId();

        $this->render('stock.index', [
            'pageTitle' => $shopId !== null ? 'Stock boutique' : 'Stock général',
            'products' => (new Product())->allForStock($shopId),
            'shops' => (new Shop())->options(),
            'currentShopId' => $shopId,
            'currentShopName' => (string) (Auth::user()['shop_name'] ?? ''),
            'recentTransfers' => (new StockTransfer())->recent($shopId),
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
        $shopId = $this->currentShopId();

        if (!$product) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Produit introuvable', 'text' => 'Le produit sélectionné n’existe pas.']);
            $this->redirect('/stock');
        }

        $before = $productModel->stockForLocation($productId, $shopId);
        $signedQuantity = $quantity;
        $after = $before + $signedQuantity;

        try {
            if ($shopId !== null) {
                $productModel->adjustShopStock($productId, $shopId, $after);
            } else {
                $productModel->adjustStock($productId, $after);
            }

            $movementModel->create([
                'product_id' => $productId,
                'movement_type' => 'entry',
                'quantity' => $signedQuantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'source_shop_id' => null,
                'destination_shop_id' => $shopId,
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

    public function transfer(): void
    {
        verify_csrf();

        if ($this->currentShopId() !== null) {
            Session::flash('alert', ['icon' => 'warning', 'title' => 'Action réservée', 'text' => 'Seul le gestionnaire du stock général peut transférer vers une boutique.']);
            $this->redirect('/stock');
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $destinationShopId = (int) ($_POST['destination_shop_id'] ?? 0);
        $quantity = (float) ($_POST['quantity'] ?? 0);
        $note = trim((string) ($_POST['note'] ?? ''));

        if ($productId <= 0 || $destinationShopId <= 0 || $quantity <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Produit, boutique et quantité strictement positive sont obligatoires.']);
            $this->redirect('/stock');
        }

        try {
            (new StockTransfer())->transferToShop($productId, $destinationShopId, $quantity, $note, Auth::id());
            (new ActivityLog())->log('transfer', 'Transfert de stock vers boutique', 'stock', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Transfert effectué', 'text' => 'Le stock a été transféré vers la boutique sélectionnée.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Transfert impossible', 'text' => $throwable->getMessage() ?: 'Impossible de transférer ce stock.']);
        }

        $this->redirect('/stock');
    }

    private function currentShopId(): ?int
    {
        $shopId = Auth::user()['shop_id'] ?? null;

        if ($shopId === null || $shopId === '') {
            return null;
        }

        return (int) $shopId;
    }
}
