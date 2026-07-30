<?php $settings = $settings ?? []; ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-1">Informations de l’entreprise</h3>
            <p class="text-muted mb-0">Coordonnées, identité légale et préfixes de numérotation.</p>
        </div>
    </div>
    <div class="card-body px-4 pb-4">
        <form method="post" action="<?= e(url('/settings/company/update')); ?>" class="row g-3">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" value="<?= e((string) ($settings['id'] ?? 0)); ?>">

            <div class="col-md-6">
                <label class="form-label" for="company_name">Nom commercial</label>
                <input class="form-control" id="company_name" name="company_name" value="<?= e(old('company_name', (string) ($settings['company_name'] ?? ''))); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="legal_name">Raison sociale</label>
                <input class="form-control" id="legal_name" name="legal_name" value="<?= e(old('legal_name', (string) ($settings['legal_name'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email', (string) ($settings['email'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="phone">Téléphone</label>
                <input class="form-control" id="phone" name="phone" value="<?= e(old('phone', (string) ($settings['phone'] ?? ''))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="whatsapp">WhatsApp</label>
                <input class="form-control" id="whatsapp" name="whatsapp" value="<?= e(old('whatsapp', (string) ($settings['whatsapp'] ?? ''))); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="city">Ville</label>
                <input class="form-control" id="city" name="city" value="<?= e(old('city', (string) ($settings['city'] ?? ''))); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="country">Pays</label>
                <input class="form-control" id="country" name="country" value="<?= e(old('country', (string) ($settings['country'] ?? ''))); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="website">Site web</label>
                <input class="form-control" id="website" name="website" value="<?= e(old('website', (string) ($settings['website'] ?? ''))); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="tax_id">NIF / ID fiscal</label>
                <input class="form-control" id="tax_id" name="tax_id" value="<?= e(old('tax_id', (string) ($settings['tax_id'] ?? ''))); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="idnat">IDNAT</label>
                <input class="form-control" id="idnat" name="idnat" value="<?= e(old('idnat', (string) ($settings['idnat'] ?? ''))); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="commerce_register">RCCM</label>
                <input class="form-control" id="commerce_register" name="commerce_register" value="<?= e(old('commerce_register', (string) ($settings['commerce_register'] ?? ''))); ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="address">Adresse</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?= e(old('address', (string) ($settings['address'] ?? ''))); ?></textarea>
            </div>

            <div class="col-12 pt-3">
                <div class="border-top pt-4">
                    <h4 class="h6 mb-1">Coordonnées bancaires</h4>
                    <p class="text-muted mb-0">Ces informations seront affichées sur les devis et factures.</p>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="bank_name">Nom de la banque</label>
                <input class="form-control" id="bank_name" name="bank_name" value="<?= e(old('bank_name', (string) ($settings['bank_name'] ?? ''))); ?>" placeholder="Ex. Rawbank, EquityBCDC...">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="swift_code">Code SWIFT</label>
                <input class="form-control" id="swift_code" name="swift_code" value="<?= e(old('swift_code', (string) ($settings['swift_code'] ?? ''))); ?>" placeholder="Ex. RAWBCDKI">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="iban">IBAN</label>
                <input class="form-control" id="iban" name="iban" value="<?= e(old('iban', (string) ($settings['iban'] ?? ''))); ?>" placeholder="Ex. CD00 0000 0000 0000 0000">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="bank_account_usd">Numéro de compte USD</label>
                <input class="form-control" id="bank_account_usd" name="bank_account_usd" value="<?= e(old('bank_account_usd', (string) ($settings['bank_account_usd'] ?? ''))); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="bank_account_cdf">Numéro de compte CDF</label>
                <input class="form-control" id="bank_account_cdf" name="bank_account_cdf" value="<?= e(old('bank_account_cdf', (string) ($settings['bank_account_cdf'] ?? ''))); ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label" for="currency_code">Devise</label>
                <input class="form-control" id="currency_code" name="currency_code" maxlength="10" value="<?= e(old('currency_code', (string) ($settings['currency_code'] ?? 'USD'))); ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="exchange_rate">Taux USD → CDF</label>
                <input type="number" step="0.0001" min="1" class="form-control" id="exchange_rate" name="exchange_rate" value="<?= e(old('exchange_rate', (string) ($settings['exchange_rate'] ?? '1'))); ?>" required>
                <div class="form-text">Utilisé pour convertir les prix en CDF.</div>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="quote_prefix">Préfixe devis</label>
                <input class="form-control" id="quote_prefix" name="quote_prefix" maxlength="20" value="<?= e(old('quote_prefix', (string) ($settings['quote_prefix'] ?? 'DEV'))); ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="invoice_prefix">Préfixe facture</label>
                <input class="form-control" id="invoice_prefix" name="invoice_prefix" maxlength="20" value="<?= e(old('invoice_prefix', (string) ($settings['invoice_prefix'] ?? 'FAC'))); ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="payment_prefix">Préfixe paiement</label>
                <input class="form-control" id="payment_prefix" name="payment_prefix" maxlength="20" value="<?= e(old('payment_prefix', (string) ($settings['payment_prefix'] ?? 'PAY'))); ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="procurement_prefix">Préfixe appro.</label>
                <input class="form-control" id="procurement_prefix" name="procurement_prefix" maxlength="20" value="<?= e(old('procurement_prefix', (string) ($settings['procurement_prefix'] ?? 'APP'))); ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="expense_prefix">Préfixe dépense</label>
                <input class="form-control" id="expense_prefix" name="expense_prefix" maxlength="20" value="<?= e(old('expense_prefix', (string) ($settings['expense_prefix'] ?? 'DEP'))); ?>" required>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Enregistrer les paramètres</button>
            </div>
        </form>
    </div>
</div>
