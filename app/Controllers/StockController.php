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

        $destinationShopId = (int) ($_POST['destination_shop_id'] ?? 0);
        $items = $this->normalizeTransferItems($_POST['items'] ?? []);
        $note = trim((string) ($_POST['note'] ?? ''));

        if ($destinationShopId <= 0 || $items === []) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Boutique, produit(s) et quantité(s) strictement positives sont obligatoires.']);
            $this->redirect('/stock');
        }

        try {
            $count = (new StockTransfer())->transferManyToShop($destinationShopId, $items, $note, Auth::id());
            (new ActivityLog())->log('transfer', 'Transfert multi-produits vers boutique', 'stock', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Transfert effectué', 'text' => $count . ' produit(s) transféré(s) vers la boutique sélectionnée.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Transfert impossible', 'text' => $throwable->getMessage() ?: 'Impossible de transférer ce stock.']);
        }

        $this->redirect('/stock');
    }

    public function returnStock(): void
    {
        verify_csrf();

        $shopId = $this->currentShopId();
        if ($shopId === null) {
            Session::flash('alert', ['icon' => 'warning', 'title' => 'Action réservée', 'text' => 'Cette action est réservée aux extensions/boutiques.']);
            $this->redirect('/stock');
        }

        $items = $this->normalizeTransferItems($_POST['items'] ?? []);
        $note = trim((string) ($_POST['note'] ?? ''));

        if ($items === []) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Veuillez sélectionner au moins un produit avec une quantité positive.']);
            $this->redirect('/stock');
        }

        try {
            $count = (new StockTransfer())->returnManyToCentral($shopId, $items, $note, Auth::id());
            (new ActivityLog())->log('return', 'Retour multi-produits vers stock général', 'stock', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Retour enregistré', 'text' => $count . ' produit(s) retourné(s) au stock général.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Retour impossible', 'text' => $throwable->getMessage() ?: 'Impossible de retourner ce stock.']);
        }

        $this->redirect('/stock');
    }

    private function normalizeTransferItems(array $input): array
    {
        $productIds = $input['product_id'] ?? [];
        $quantities = $input['quantity'] ?? [];
        $items = [];

        foreach ($productIds as $index => $productId) {
            $productId = (int) $productId;
            $quantity = (float) ($quantities[$index] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        }

        return $items;
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
