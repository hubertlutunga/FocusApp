<?php
$invoiceCurrency = normalize_currency_code($invoice['currency_code'] ?? 'USD');
$companyName = (string) ($company['company_name'] ?? config('app.name'));
$companyPhone = (string) ($company['phone'] ?? '');
$companyAddress = (string) ($company['address'] ?? '');
?>
<style>
    :root {
        --ticket-width: 75mm;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 16px;
        font-family: "Courier New", Courier, monospace;
        background: #f5f5f5;
        color: #111;
        display: flex;
        justify-content: center;
    }

    .pos-ticket {
        width: var(--ticket-width);
        max-width: 100%;
        background: #fff;
        padding: 10px 8px 14px;
        text-align: center;
        border: 1px dashed #bbb;
    }

    .pos-title {
        font-weight: 700;
        font-size: 15px;
        margin: 0 0 4px;
    }

    .pos-meta,
    .pos-line,
    .pos-item,
    .pos-total-row,
    .pos-footer {
        font-size: 11px;
        line-height: 1.35;
    }

    .pos-meta {
        margin-bottom: 6px;
    }

    .divider {
        border-top: 1px dashed #999;
        margin: 8px 0;
    }

    .pos-item,
    .pos-total-row {
        display: flex;
        justify-content: space-between;
        gap: 6px;
        text-align: left;
    }

    .pos-item + .pos-item,
    .pos-total-row + .pos-total-row {
        margin-top: 4px;
    }

    .pos-item-label {
        flex: 1;
        min-width: 0;
        text-align: left;
        word-break: break-word;
    }

    .pos-item-value {
        white-space: nowrap;
        text-align: right;
    }

    .pos-strong {
        font-weight: 700;
    }

    .pos-actions {
        margin-top: 10px;
    }

    .pos-actions button {
        border: 0;
        background: #0d6efd;
        color: #fff;
        font-size: 12px;
        border-radius: 6px;
        padding: 8px 12px;
        cursor: pointer;
    }

    @media print {
        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .pos-ticket {
            width: var(--ticket-width);
            border: 0;
            margin: 0 auto;
            padding: 6px 4px 8px;
        }

        .pos-actions {
            display: none;
        }
    }
</style>

<div class="pos-ticket">
    <h1 class="pos-title"><?= e($companyName); ?></h1>
    <?php if ($companyAddress !== ''): ?><div class="pos-meta"><?= e($companyAddress); ?></div><?php endif; ?>
    <?php if ($companyPhone !== ''): ?><div class="pos-meta">Tel: <?= e($companyPhone); ?></div><?php endif; ?>

    <div class="divider"></div>
    <div class="pos-line">Facture: <span class="pos-strong"><?= e($invoice['invoice_number']); ?></span></div>
    <div class="pos-line">Date: <?= e(date('d/m/Y H:i', strtotime((string) $invoice['invoice_date']))); ?></div>
    <div class="pos-line">Client: <?= e($invoice['client_name']); ?></div>
    <div class="divider"></div>

    <?php foreach ($items as $item): ?>
        <div class="pos-item">
            <div class="pos-item-label">
                <?= e($item['description']); ?>
                <div><?= e(number_format((float) $item['quantity'], 2, ',', ' ')); ?> x <?= e(format_money($item['unit_price'], $invoiceCurrency)); ?></div>
            </div>
            <div class="pos-item-value"><?= e(format_money($item['line_total'], $invoiceCurrency)); ?></div>
        </div>
    <?php endforeach; ?>

    <div class="divider"></div>

    <div class="pos-total-row">
        <span>Sous-total</span>
        <span><?= e(format_money($invoice['subtotal'], $invoiceCurrency)); ?></span>
    </div>
    <div class="pos-total-row">
        <span>Taxe</span>
        <span><?= e(format_money($invoice['tax_amount'], $invoiceCurrency)); ?></span>
    </div>
    <div class="pos-total-row pos-strong">
        <span>Total TTC</span>
        <span><?= e(format_money($invoice['grand_total'], $invoiceCurrency)); ?></span>
    </div>
    <div class="pos-total-row">
        <span>Paye</span>
        <span><?= e(format_money($invoice['amount_paid'], $invoiceCurrency)); ?></span>
    </div>
    <div class="pos-total-row pos-strong">
        <span>Reste</span>
        <span><?= e(format_money($invoice['balance_due'], $invoiceCurrency)); ?></span>
    </div>

    <div class="divider"></div>
    <div class="pos-footer">Merci pour votre confiance</div>

    <div class="pos-actions">
        <button type="button" onclick="window.print();">Imprimer le ticket</button>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>
