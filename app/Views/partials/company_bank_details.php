<?php
$company = $company ?? [];
$bankDetails = array_filter([
    'Banque' => trim((string) ($company['bank_name'] ?? '')),
    'Compte USD' => trim((string) ($company['bank_account_usd'] ?? '')),
    'Compte CDF' => trim((string) ($company['bank_account_cdf'] ?? '')),
    'SWIFT' => trim((string) ($company['swift_code'] ?? '')),
], static fn (string $value): bool => $value !== '');
?>

<?php if ($bankDetails !== []): ?>
    <div class="document-note mt-4">
        <div class="document-section-label mb-2">Coordonnées bancaires</div>
        <dl class="document-info-list mb-0">
            <?php foreach ($bankDetails as $label => $value): ?>
                <div>
                    <dt><?= e($label); ?></dt>
                    <dd><?= e($value); ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>
<?php endif; ?>
