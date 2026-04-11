<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\NumberSequence;
use App\Models\Product;
use App\Models\Unit;
use Throwable;

final class ProductController extends Controller
{
    public function index(): void
    {
        $productModel = new Product();

        $this->render('products.index', [
            'pageTitle' => 'Produits',
            'products' => $productModel->all(),
            'lowStockProducts' => $productModel->lowStock(),
        ]);
    }

    public function create(): void
    {
        $this->render('products.form', [
            'pageTitle' => 'Nouveau produit',
            'product' => null,
            'categories' => (new Category())->optionsByType(['product', 'mixed']),
            'units' => (new Unit())->options(),
            'formAction' => url('/products/store'),
            'submitLabel' => 'Enregistrer le produit',
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($payload['name'] === '' || $payload['category_id'] <= 0 || $payload['unit_id'] <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Nom, catégorie et unité sont obligatoires.']);
            $this->redirect('/products/create');
        }

        try {
            if ($payload['sku'] === '') {
                $payload['sku'] = (new NumberSequence())->next('product');
            }
            (new Product())->create($payload);
            (new ActivityLog())->log('create', 'Création du produit : ' . $payload['name'], 'produits', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Produit ajouté', 'text' => 'Le produit a été créé avec succès.']);
            $this->redirect('/products');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Création impossible', 'text' => 'Impossible de créer ce produit.']);
            $this->redirect('/products/create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $product = (new Product())->find($id);

        if (!$product) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Produit introuvable', 'text' => 'Le produit demandé n’existe pas.']);
            $this->redirect('/products');
        }

        $this->render('products.form', [
            'pageTitle' => 'Modifier un produit',
            'product' => $product,
            'categories' => (new Category())->optionsByType(['product', 'mixed']),
            'units' => (new Unit())->options(),
            'formAction' => url('/products/update'),
            'submitLabel' => 'Mettre à jour le produit',
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->payload();
        Session::set('old_input', $payload);

        if ($id <= 0 || $payload['name'] === '' || $payload['category_id'] <= 0 || $payload['unit_id'] <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Champs requis', 'text' => 'Nom, catégorie et unité sont obligatoires.']);
            $this->redirect('/products/edit?id=' . $id);
        }

        try {
            if ($payload['sku'] === '') {
                $payload['sku'] = (new NumberSequence())->next('product');
            }
            (new Product())->updateProduct($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour du produit #' . $id, 'produits', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', ['icon' => 'success', 'title' => 'Produit modifié', 'text' => 'Le produit a été mis à jour.']);
            $this->redirect('/products');
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Mise à jour impossible', 'text' => 'Impossible de mettre à jour ce produit.']);
            $this->redirect('/products/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Produit introuvable', 'text' => 'Identifiant produit invalide.']);
            $this->redirect('/products');
        }

        try {
            (new Product())->softDelete($id);
            (new ActivityLog())->log('delete', 'Suppression logique du produit #' . $id, 'produits', Auth::id());
            Session::flash('alert', ['icon' => 'success', 'title' => 'Produit archivé', 'text' => 'Le produit a été archivé.']);
        } catch (Throwable $throwable) {
            Session::flash('alert', ['icon' => 'error', 'title' => 'Suppression impossible', 'text' => 'Impossible d’archiver ce produit.']);
        }

        $this->redirect('/products');
    }

    private function payload(): array
    {
        return [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'unit_id' => (int) ($_POST['unit_id'] ?? 0),
            'sku' => strtoupper(trim((string) ($_POST['sku'] ?? ''))),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'barcode' => trim((string) ($_POST['barcode'] ?? '')),
            'cost_price' => (float) ($_POST['cost_price'] ?? 0),
            'sale_price' => (float) ($_POST['sale_price'] ?? 0),
            'minimum_stock' => (float) ($_POST['minimum_stock'] ?? 0),
            'current_stock' => (float) ($_POST['current_stock'] ?? 0),
            'image_path' => trim((string) ($_POST['image_path'] ?? '')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }
}
