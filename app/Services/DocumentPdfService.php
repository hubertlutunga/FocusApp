<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CompanySetting;

final class DocumentPdfService
{
    private const BRAND_BLUE = [13, 110, 253];
    private const BRAND_SOFT = [237, 244, 255];
    private const BRAND_DARK = [15, 23, 42];

    public function streamQuote(array $quote, array $items): never
    {
        $company = (new CompanySetting())->first();
        $pdf = $this->makePdf($this->buildCompanyFooterLines($company));
        $this->renderHeader($pdf, $company, 'DEVIS', $quote['quote_number']);
        $this->renderParty($pdf, $quote['client_name'], $quote['client_address'] ?? '', $quote['client_phone'] ?? '', $quote['client_email'] ?? '');
        $this->renderMeta($pdf, [
            'Date devis' => $quote['quote_date'],
        ]);
        $this->renderItems($pdf, $items);
        $this->renderTotals($pdf, (float) $quote['subtotal'], (float) $quote['discount_amount'], tax_rate_label($quote['tax_rate'] ?? 0), (float) $quote['tax_amount'], (float) $quote['grand_total']);
        $pdf->Output('I', $quote['quote_number'] . '.pdf');
        exit;
    }

    public function streamInvoice(array $invoice, array $items): never
    {
        $company = (new CompanySetting())->first();
        $pdf = $this->makePdf($this->buildCompanyFooterLines($company));
        $this->renderHeader($pdf, $company, 'FACTURE', $invoice['invoice_number']);
        $this->renderParty($pdf, $invoice['client_name'], $invoice['client_address'] ?? '', $invoice['client_phone'] ?? '', $invoice['client_email'] ?? '');
        $this->renderMeta($pdf, [
            'Date facture' => $invoice['invoice_date'],
            'Échéance' => $invoice['due_date'] ?: '—',
            'Solde' => number_format((float) $invoice['balance_due'], 2, ',', ' ') . ' ' . (($company['currency_code'] ?? 'USD')),
        ]);
        $this->renderItems($pdf, $items);
        $this->renderTotals($pdf, (float) $invoice['subtotal'], (float) $invoice['discount_amount'], tax_rate_label($invoice['tax_rate'] ?? 0), (float) $invoice['tax_amount'], (float) $invoice['grand_total']);
        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, $this->pdfText('Montant payé : ') . number_format((float) $invoice['amount_paid'], 2, ',', ' ') . ' ' . (($company['currency_code'] ?? 'USD')), 0, 1, 'R');
        $pdf->Output('I', $invoice['invoice_number'] . '.pdf');
        exit;
    }

    private function makePdf(array $footerLines): \FPDF
    {
        require_once base_path('pages/pdf/fpdf.php');
        $pdf = new class() extends \FPDF {
            private array $footerLines = [];

            public function setFooterLines(array $footerLines): void
            {
                $this->footerLines = $footerLines;
            }

            public function Footer(): void
            {
                if ($this->footerLines === []) {
                    return;
                }

                $this->SetY(-22);
                $this->SetDrawColor(220, 230, 244);
                $this->SetLineWidth(0.35);
                $this->Line(12, $this->GetY(), 198, $this->GetY());
                $this->Ln(2.5);
                $this->SetTextColor(100, 116, 139);
                $this->SetFont('Arial', '', 8.5);

                foreach ($this->footerLines as $line) {
                    $this->Cell(0, 4, $line, 0, 1, 'C');
                }
            }
        };
        $pdf->setFooterLines($footerLines);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 30);
        $pdf->SetMargins(12, 12, 12);
        return $pdf;
    }

    private function renderHeader(\FPDF $pdf, ?array $company, string $title, string $number): void
    {
        $logoPath = $this->resolveLogoPath();

        if ($logoPath !== null) {
            $pdf->Image($logoPath, 12, 10, 46);
            $topY = 12;
        } else {
            $topY = 12;
        }

        $rightX = 150;
        $rightWidth = 48;

        $pdf->SetXY($rightX, $topY);
        $pdf->SetFillColor(...self::BRAND_BLUE);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell($rightWidth, 10, $this->pdfText($title), 0, 1, 'C', true);

        $pdf->SetXY($rightX, $topY + 13);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(...self::BRAND_BLUE);
        $pdf->Cell($rightWidth, 6, $this->pdfText('N° ' . $number), 0, 1, 'R');
        $pdf->Ln(16);
    }

    private function renderParty(\FPDF $pdf, string $name, string $address, string $phone, string $email): void
    {
        $startY = $pdf->GetY();
        $pdf->SetFillColor(...self::BRAND_SOFT);
        $pdf->SetDrawColor(220, 230, 244);
        $pdf->Rect(12, $startY, 186, 30, 'FD');
        $pdf->SetXY(16, $startY + 3);
        $pdf->SetTextColor(...self::BRAND_BLUE);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, $this->pdfText('Client'), 0, 1);
        $pdf->SetX(16);
        $pdf->SetTextColor(...self::BRAND_DARK);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, $this->pdfText($name), 0, 1);
        $pdf->SetX(16);
        $pdf->SetFont('Arial', '', 9.5);
        if ($address !== '') {
            $pdf->Cell(0, 5, $this->pdfText($address), 0, 1);
            $pdf->SetX(16);
        }
        $contact = trim($phone . '   ' . $email);
        if ($contact !== '') {
            $pdf->Cell(0, 5, $this->pdfText($contact), 0, 1);
        }
        $pdf->SetY($startY + 38);
        $pdf->Ln(5);
    }

    private function renderMeta(\FPDF $pdf, array $meta): void
    {
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->SetFont('Arial', '', 10);
        foreach ($meta as $label => $value) {
            $pdf->SetTextColor(...self::BRAND_BLUE);
            $pdf->Cell(45, 8, $this->pdfText($label . ' :'), 1, 0, 'L', true);
            $pdf->SetTextColor(...self::BRAND_DARK);
            $pdf->Cell(0, 8, $this->pdfText((string) $value), 1, 1, 'L', false);
        }
        $pdf->Ln(3);
    }

    private function renderItems(\FPDF $pdf, array $items): void
    {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(...self::BRAND_BLUE);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(80, 8, $this->pdfText('Description'), 1, 0, 'L', true);
        $pdf->Cell(20, 8, $this->pdfText('Type'), 1, 0, 'C', true);
        $pdf->Cell(20, 8, $this->pdfText('Qté'), 1, 0, 'R', true);
        $pdf->Cell(35, 8, $this->pdfText('Prix unitaire'), 1, 0, 'R', true);
        $pdf->Cell(35, 8, $this->pdfText('Total'), 1, 1, 'R', true);

        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetFont('Arial', '', 10);
        $fill = false;
        foreach ($items as $item) {
            $description = (string) $item['description'];
            $type = $item['item_type'] === 'product' ? 'Produit' : 'Service';
            $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
            $pdf->Cell(80, 8, $this->pdfText(mb_strimwidth($description, 0, 40, '...')), 1, 0, 'L', true);
            $pdf->Cell(20, 8, $this->pdfText($type), 1, 0, 'C', true);
            $pdf->Cell(20, 8, number_format((float) $item['quantity'], 2, ',', ' '), 1, 0, 'R', true);
            $pdf->Cell(35, 8, number_format((float) $item['unit_price'], 2, ',', ' '), 1, 0, 'R', true);
            $pdf->Cell(35, 8, number_format((float) $item['line_total'], 2, ',', ' '), 1, 1, 'R', true);
            $fill = !$fill;
        }
    }

    private function renderTotals(\FPDF $pdf, float $subtotal, float $discount, string $taxLabel, float $tax, float $grandTotal): void
    {
        $pdf->Ln(4);
        $startX = 132;
        $startY = $pdf->GetY();
        $pdf->SetFillColor(...self::BRAND_SOFT);
        $pdf->SetDrawColor(220, 230, 244);
        $pdf->Rect($startX, $startY, 66, 34, 'FD');
        $pdf->SetXY($startX + 4, $startY + 4);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(30, 6, $this->pdfText('Sous-total'), 0, 0, 'L');
        $pdf->Cell(28, 6, number_format($subtotal, 2, ',', ' '), 0, 1, 'R');
        $pdf->SetX($startX + 4);
        $pdf->Cell(30, 6, $this->pdfText('Remise'), 0, 0, 'L');
        $pdf->Cell(28, 6, number_format($discount, 2, ',', ' '), 0, 1, 'R');
        $pdf->SetX($startX + 4);
        $pdf->Cell(30, 6, $this->pdfText($taxLabel), 0, 0, 'L');
        $pdf->Cell(28, 6, number_format($tax, 2, ',', ' '), 0, 1, 'R');
        $pdf->SetX($startX + 4);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(...self::BRAND_BLUE);
        $pdf->Cell(30, 8, $this->pdfText('Total TTC'), 0, 0, 'L');
        $pdf->Cell(28, 8, number_format($grandTotal, 2, ',', ' '), 0, 1, 'R');
        $pdf->SetTextColor(...self::BRAND_DARK);
        $pdf->Ln(6);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->MultiCell(0, 5, $this->pdfText('Merci pour votre confiance. Document généré par Focus Group ERP.'));
    }

    private function pdfText(?string $text): string
    {
        $value = (string) ($text ?? '');
        $converted = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $value);

        if ($converted !== false) {
            return $converted;
        }

        return utf8_decode($value);
    }

    private function buildCompanyFooterLines(?array $company): array
    {
        if ($company === null) {
            return [];
        }

        $lines = [];
        $companyName = trim((string) ($company['company_name'] ?? ''));
        if ($companyName !== '') {
            $lines[] = $this->pdfText($companyName);
        }

        $contactParts = [];
        $address = trim((string) ($company['address'] ?? ''));
        if ($address !== '') {
            $contactParts[] = $address;
        }

        $phone = trim((string) ($company['phone'] ?? ''));
        if ($phone !== '') {
            $contactParts[] = 'Tél: ' . $phone;
        }

        $email = trim((string) ($company['email'] ?? ''));
        if ($email !== '') {
            $contactParts[] = $email;
        }

        if ($contactParts !== []) {
            $lines[] = $this->pdfText(implode('  |  ', $contactParts));
        }

        $identifiers = [];
        if (!empty($company['tax_id'])) {
            $identifiers[] = 'NIF: ' . (string) $company['tax_id'];
        }
        if (!empty($company['idnat'])) {
            $identifiers[] = 'IDNAT: ' . (string) $company['idnat'];
        }
        if (!empty($company['commerce_register'])) {
            $identifiers[] = 'RCCM: ' . (string) $company['commerce_register'];
        }

        if ($identifiers !== []) {
            $lines[] = $this->pdfText(implode('  |  ', $identifiers));
        }

        return $lines;
    }

    private function resolveLogoPath(): ?string
    {
        $paths = [
            base_path('images/logo_focusprojet_bleu_pdf.jpg'),
            base_path('images/logo_focusprojet_bleu.png'),
            base_path('images/logo_focusprojet_1.png'),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
