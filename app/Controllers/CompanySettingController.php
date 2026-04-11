<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\CompanySetting;
use Throwable;

final class CompanySettingController extends Controller
{
    public function edit(): void
    {
        $settings = (new CompanySetting())->first();

        $this->render('settings.company', [
            'pageTitle' => 'Paramètres entreprise',
            'settings' => $settings,
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $payload = [
            'company_name' => trim((string) ($_POST['company_name'] ?? '')),
            'legal_name' => trim((string) ($_POST['legal_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'whatsapp' => trim((string) ($_POST['whatsapp'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'country' => trim((string) ($_POST['country'] ?? '')),
            'website' => trim((string) ($_POST['website'] ?? '')),
            'tax_id' => trim((string) ($_POST['tax_id'] ?? '')),
            'idnat' => trim((string) ($_POST['idnat'] ?? '')),
            'commerce_register' => trim((string) ($_POST['commerce_register'] ?? '')),
            'currency_code' => strtoupper(trim((string) ($_POST['currency_code'] ?? 'USD'))),
            'quote_prefix' => strtoupper(trim((string) ($_POST['quote_prefix'] ?? 'DEV'))),
            'invoice_prefix' => strtoupper(trim((string) ($_POST['invoice_prefix'] ?? 'FAC'))),
            'payment_prefix' => strtoupper(trim((string) ($_POST['payment_prefix'] ?? 'PAY'))),
            'procurement_prefix' => strtoupper(trim((string) ($_POST['procurement_prefix'] ?? 'APP'))),
            'expense_prefix' => strtoupper(trim((string) ($_POST['expense_prefix'] ?? 'DEP'))),
        ];

        Session::set('old_input', $payload);

        if ($id <= 0 || $payload['company_name'] === '') {
            Session::flash('alert', [
                'icon' => 'error',
                'title' => 'Champs requis',
                'text' => 'Le nom de l’entreprise est obligatoire.',
            ]);
            $this->redirect('/settings/company');
        }

        if ($payload['email'] !== '' && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('alert', [
                'icon' => 'error',
                'title' => 'Email invalide',
                'text' => 'Veuillez saisir une adresse email valide.',
            ]);
            $this->redirect('/settings/company');
        }

        try {
            (new CompanySetting())->updateSettings($id, $payload);
            (new ActivityLog())->log('update', 'Mise à jour des paramètres entreprise', 'parametres_entreprise', Auth::id());
            Session::forget('old_input');
            Session::flash('alert', [
                'icon' => 'success',
                'title' => 'Paramètres enregistrés',
                'text' => 'Les informations de Focus Group ont été mises à jour.',
            ]);
        } catch (Throwable $throwable) {
            Session::flash('alert', [
                'icon' => 'error',
                'title' => 'Échec de mise à jour',
                'text' => 'Impossible d’enregistrer les paramètres entreprise.',
            ]);
        }

        $this->redirect('/settings/company');
    }
}
