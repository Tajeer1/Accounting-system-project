<?php

namespace App\Services;

use Mpdf\Mpdf;

class PdfService
{
    public function make(string $html, string $title = 'Document'): Mpdf
    {
        $tempDir = storage_path('app/mpdf');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 14,
            'margin_bottom' => 14,
            'default_font' => 'xbriyaz',
            'default_font_size' => 11,
            'directionality' => 'rtl',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->SetTitle($title);
        $mpdf->SetAuthor(\App\Models\Setting::get('company_name', 'Event Plus'));
        $mpdf->SetCreator('Event Plus Accounting');
        $mpdf->WriteHTML($html);

        return $mpdf;
    }

    public function download(string $html, string $filename): \Illuminate\Http\Response
    {
        $mpdf = $this->make($html, $filename);
        $content = $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
        ]);
    }

    public function stream(string $html, string $filename): \Illuminate\Http\Response
    {
        $mpdf = $this->make($html, $filename);
        $content = $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '.pdf"',
        ]);
    }
}
