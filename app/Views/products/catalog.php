<?php
$productCount = count($products ?? []);
$catalogTitle = !empty($isShopCatalog) ? 'Catalogue de ' . ($currentShopName ?: 'votre boutique') : 'Catalogue produits';
?>

<div class="page-hero">
    <div>
        <h1 class="h3 mb-1"><?= e($catalogTitle); ?></h1>
        <p class="text-muted mb-0">Consultez rapidement les photos, noms, prix et disponibilités des produits.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= e(url('/dashboard')); ?>" class="btn btn-outline-secondary">Tableau de bord</a>
        <a href="<?= e(url('/invoices/create')); ?>" class="btn btn-primary">Nouvelle facture</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-8">
                <label class="form-label" for="catalogSearch">Recherche produit</label>
                <input type="search" class="form-control form-control-lg" id="catalogSearch" placeholder="Rechercher par nom, SKU, catégorie ou prix...">
            </div>
            <div class="col-lg-4">
                <div class="catalog-summary-card">
                    <span class="metric-icon primary"><i class="bi bi-images"></i></span>
                    <div>
                        <div class="muted-label">Produits affichés</div>
                        <div class="h4 mb-0"><?= e((string) $productCount); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (($products ?? []) === []): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center empty-state">
            <i class="bi bi-box-seam"></i>
            <div>Aucun produit disponible pour le moment.</div>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4 catalog-grid" id="productCatalogGrid">
        <?php foreach ($products as $product): ?>
            <?php
            $imageUrl = product_image_url($product['image_path'] ?? '');
            $stock = (float) ($product['current_stock'] ?? 0);
            $isAvailable = $stock > 0;
            $searchText = strtolower(trim((string) (($product['name'] ?? '') . ' ' . ($product['sku'] ?? '') . ' ' . ($product['category_name'] ?? '') . ' ' . ($product['sale_price'] ?? ''))));
            ?>
            <div class="col-sm-6 col-xl-4 col-xxl-3 catalog-product-col" data-search="<?= e($searchText); ?>">
                <div class="catalog-product-card h-100">
                    <div class="catalog-product-image-wrap">
                        <?php if ($imageUrl !== ''): ?>
                            <img src="<?= e($imageUrl); ?>" alt="<?= e($product['name']); ?>" class="catalog-product-image" loading="lazy">
                        <?php else: ?>
                            <div class="catalog-product-placeholder">
                                <i class="bi bi-image"></i>
                                <span><?= e(mb_strtoupper(mb_substr((string) $product['name'], 0, 1))); ?></span>
                            </div>
                        <?php endif; ?>
                        <span class="catalog-stock-badge <?= $isAvailable ? 'is-available' : 'is-empty'; ?>">
                            <?= $isAvailable ? 'Disponible' : 'Rupture'; ?>
                        </span>
                    </div>
                    <div class="catalog-product-body">
                        <div class="catalog-product-meta"><?= e($product['sku']); ?><?= !empty($product['category_name']) ? ' · ' . e($product['category_name']) : ''; ?></div>
                        <h2 class="catalog-product-title"><?= e($product['name']); ?></h2>
                        <div class="catalog-product-price"><?= e(format_money($product['sale_price'], 'USD')); ?></div>
                        <div class="catalog-product-footer">
                            <span>Stock : <strong><?= e(number_format($stock, 2, ',', ' ')); ?> <?= e($product['unit_symbol'] ?: ($product['unit_name'] ?? '')); ?></strong></span>
                            <?php if (!empty($product['barcode'])): ?><span><?= e($product['barcode']); ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="alert alert-light mt-4 d-none" id="catalogEmptySearch">Aucun produit ne correspond à cette recherche.</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('catalogSearch');
    const cards = Array.from(document.querySelectorAll('.catalog-product-col'));
    const empty = document.getElementById('catalogEmptySearch');

    if (!input || cards.length === 0) {
        return;
    }

    input.addEventListener('input', function () {
        const query = input.value.trim().toLowerCase();
        let visibleCount = 0;

        cards.forEach(function (card) {
            const matches = query === '' || (card.dataset.search || '').includes(query);
            card.classList.toggle('d-none', !matches);
            if (matches) {
                visibleCount++;
            }
        });

        if (empty) {
            empty.classList.toggle('d-none', visibleCount > 0);
        }
    });
});
</script>
