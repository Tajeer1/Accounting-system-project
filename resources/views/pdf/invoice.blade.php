<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة {{ $invoice->number }}</title>
    <style>
        body {
            font-family: xbriyaz, sans-serif;
            color: #0A0A0A;
            font-size: 11pt;
            line-height: 1.55;
        }

        .header {
            width: 100%;
            border-bottom: 2pt solid #0A0A0A;
            padding-bottom: 12pt;
            margin-bottom: 18pt;
        }
        .header td {
            vertical-align: top;
            padding: 0;
        }
        .header .right { width: 55%; text-align: right; }
        .header .left { width: 45%; text-align: left; }

        .company-name { font-size: 18pt; font-weight: bold; margin: 0 0 4pt 0; }
        .company-tagline { font-size: 9pt; color: #6B7280; margin: 0 0 8pt 0; }
        .company-details { font-size: 8.5pt; color: #6B7280; line-height: 1.6; }

        .invoice-title {
            font-size: 26pt; font-weight: bold; margin: 0 0 4pt 0;
        }
        .invoice-number { font-size: 11pt; color: #6B7280; margin-bottom: 6pt; }

        .type-pill {
            display: inline-block;
            padding: 3pt 10pt;
            border-radius: 12pt;
            font-size: 9pt;
            font-weight: bold;
        }
        .type-sales { background: #D4F5DC; color: #0F8A3C; }
        .type-purchase { background: #FDE8E8; color: #C53030; }

        .status-badge {
            display: inline-block;
            padding: 3pt 10pt;
            border-radius: 12pt;
            font-size: 9pt;
            font-weight: bold;
        }
        .status-paid { background: #D4F5DC; color: #0F8A3C; }
        .status-sent { background: #D9EBFF; color: #1E6FD9; }
        .status-draft { background: #EFEDE8; color: #6B7280; }
        .status-overdue { background: #FDE8E8; color: #C53030; }
        .status-cancelled { background: #EFEDE8; color: #6B7280; }

        .meta-box {
            background: #F8F6F1;
            border-radius: 8pt;
            padding: 10pt;
            margin-bottom: 16pt;
            width: 100%;
        }
        .meta-box td {
            width: 25%;
            vertical-align: top;
            padding: 2pt 6pt;
        }
        .meta-label { font-size: 8pt; color: #6B7280; margin-bottom: 2pt; }
        .meta-value { font-size: 10pt; font-weight: bold; }

        .party-box {
            border: 1pt solid #ECEAE4;
            border-radius: 8pt;
            padding: 12pt;
            margin-bottom: 16pt;
        }
        .party-label { font-size: 9pt; color: #6B7280; margin-bottom: 4pt; }
        .party-name { font-size: 14pt; font-weight: bold; }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16pt;
        }
        table.items thead th {
            background: #0A0A0A;
            color: #fff;
            padding: 8pt;
            font-weight: bold;
            font-size: 10pt;
            text-align: right;
        }
        table.items thead th.num { text-align: left; }
        table.items tbody td {
            padding: 10pt 8pt;
            border-bottom: 1pt solid #ECEAE4;
            font-size: 10pt;
        }
        table.items tbody td.num {
            text-align: left;
            font-weight: bold;
            direction: ltr;
        }

        .totals-box {
            background: #0A0A0A;
            color: #fff;
            border-radius: 8pt;
            padding: 12pt;
            margin-top: 6pt;
            width: 45%;
            margin-right: 0;
            margin-left: 55%;
        }
        .totals-box .row { width: 100%; }
        .totals-box .row td { padding: 3pt 0; }
        .totals-box .lbl { font-size: 9pt; color: #fff; opacity: 0.7; }
        .totals-box .val {
            text-align: left;
            font-size: 11pt;
            font-weight: bold;
            direction: ltr;
        }
        .totals-box .final {
            border-top: 0.5pt solid #fff;
        }
        .totals-box .final .lbl { font-size: 10pt; opacity: 1; }
        .totals-box .final .val { font-size: 16pt; }

        .footer {
            margin-top: 28pt;
            padding-top: 12pt;
            border-top: 1pt solid #ECEAE4;
            font-size: 8.5pt;
            color: #6B7280;
            text-align: center;
            line-height: 1.8;
        }
        .footer .thank {
            font-size: 11pt;
            color: #0A0A0A;
            font-weight: bold;
            margin-bottom: 5pt;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header">
        <tr>
            <td class="right">
                <div class="company-name">{{ $company['name'] ?? 'Event Plus' }}</div>
                <div class="company-tagline">نظام إداري محاسبي</div>
                <div class="company-details">
                    @if (!empty($company['email']))
                        البريد: {{ $company['email'] }}<br>
                    @endif
                    @if (!empty($company['phone']))
                        الهاتف: {{ $company['phone'] }}<br>
                    @endif
                    @if (!empty($company['address']))
                        {{ $company['address'] }}
                    @endif
                </div>
            </td>
            <td class="left">
                <div class="invoice-title">فاتورة</div>
                <div class="invoice-number">{{ $invoice->number }}</div>
                <span class="type-pill type-{{ $invoice->type }}">{{ $invoice->typeLabel() }}</span>
            </td>
        </tr>
    </table>

    <!-- Meta box -->
    <table class="meta-box" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="meta-label">تاريخ الإصدار</div>
                <div class="meta-value">{{ $invoice->issue_date->translatedFormat('d M Y') }}</div>
            </td>
            <td>
                <div class="meta-label">تاريخ الاستحقاق</div>
                <div class="meta-value">{{ $invoice->due_date?->translatedFormat('d M Y') ?? '—' }}</div>
            </td>
            <td>
                <div class="meta-label">المشروع</div>
                <div class="meta-value">{{ $invoice->project?->name ?? '—' }}</div>
            </td>
            <td>
                <div class="meta-label">الحالة</div>
                <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->statusLabel() }}</span>
            </td>
        </tr>
    </table>

    <!-- Party -->
    <div class="party-box">
        <div class="party-label">{{ $invoice->type === 'sales' ? 'فاتورة إلى' : 'فاتورة من' }}</div>
        <div class="party-name">{{ $invoice->party_name }}</div>
    </div>

    <!-- Items -->
    <table class="items">
        <thead>
            <tr>
                <th style="width: 60%;">الوصف</th>
                <th style="width: 20%;">المشروع</th>
                <th class="num" style="width: 20%;">المبلغ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->description ?? ($invoice->type === 'sales' ? 'خدمات تنظيم معارض' : 'مشتريات وتجهيزات') }}</td>
                <td>{{ $invoice->project?->code ?? '—' }}</td>
                <td class="num">{{ money($invoice->amount) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals-box" cellpadding="0" cellspacing="0">
        <tr class="row">
            <td class="lbl">المجموع الفرعي</td>
            <td class="val">{{ money($invoice->amount) }}</td>
        </tr>
        <tr class="row">
            <td class="lbl">الضريبة</td>
            <td class="val">0.000</td>
        </tr>
        <tr class="row final">
            <td class="lbl">الإجمالي النهائي</td>
            <td class="val">{{ money($invoice->amount) }}</td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="thank">شكرًا لتعاملكم معنا</div>
        @if (!empty($settings['invoice_notes']))
            {{ $settings['invoice_notes'] }}<br>
        @endif
        تم إصدار هذه الفاتورة إلكترونيًا من نظام {{ $company['name'] ?? 'Event Plus' }}
    </div>

</body>
</html>
