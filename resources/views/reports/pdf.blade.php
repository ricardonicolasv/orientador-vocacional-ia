<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Vocacional - {{ $report->student->name }}</title>

    <style>
        @page {
            margin: 28px 34px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.45;
        }

        .header {
            border-bottom: 3px solid #15803d;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-title {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
            margin: 0;
        }

        .header-subtitle {
            font-size: 12px;
            color: #4b5563;
            margin-top: 4px;
        }

        .header-meta {
            text-align: right;
            font-size: 11px;
            color: #6b7280;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-low {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .badge-medium {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-high {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-default {
            background-color: #e5e7eb;
            color: #374151;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .summary-grid td {
            width: 25%;
            vertical-align: top;
            padding: 10px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 4px;
        }

        .value {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
        }

        .section {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 6px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 11px;
            background-color: #ffffff;
            white-space: pre-line;
        }

        .box-muted {
            background-color: #f9fafb;
        }

        .box-green {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
        }

        .box-blue {
            background-color: #eff6ff;
            border-color: #bfdbfe;
        }

        .box-yellow {
            background-color: #fffbeb;
            border-color: #fde68a;
        }

        .box-orientador {
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            border-left: 5px solid #15803d;
        }

        .two-columns {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .two-columns td {
            width: 50%;
            vertical-align: top;
        }

        .left-col {
            padding-right: 7px;
        }

        .right-col {
            padding-left: 7px;
        }

        .footer {
            margin-top: 22px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }

        .page-break {
            page-break-before: always;
        }

        .small-text {
            font-size: 11px;
            color: #4b5563;
        }

        .notice {
            margin-top: 12px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            padding: 10px;
            font-size: 10.5px;
            color: #4b5563;
        }
    </style>
</head>

<body>
    @php
    $clarityLabels = [
    'bajo' => 'Bajo',
    'medio' => 'Medio',
    'alto' => 'Alto',
    ];

    $clarityClass = match ($report->clarity_level) {
    'bajo' => 'badge-low',
    'medio' => 'badge-medium',
    'alto' => 'badge-high',
    default => 'badge-default',
    };

    $clarityLabel = $clarityLabels[$report->clarity_level] ?? ucfirst($report->clarity_level);

    $student = $report->student;
    $conversation = $report->conversation;
    @endphp

    {{-- Encabezado --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="header-title">Reporte Vocacional</h1>
                    <div class="header-subtitle">
                        Instituto San José · Orientación vocacional asistida por IA
                    </div>
                </td>

                <td class="header-meta">
                    <div><strong>Fecha:</strong> {{ $report->created_at->format('d-m-Y H:i') }}</div>
                    @if($conversation)
                    <div><strong>Conversación:</strong> #{{ $conversation->id }}</div>
                    @endif
                    <div><strong>Reporte:</strong> #{{ $report->id }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Resumen superior --}}
    <table class="summary-grid">
        <tr>
            <td>
                <div class="label">Estudiante</div>
                <div class="value">{{ $student->name }}</div>
            </td>

            <td>
                <div class="label">Curso</div>
                <div class="value">{{ $student->course }}</div>
            </td>

            <td>
                <div class="label">Colegio</div>
                <div class="value">{{ $student->school }}</div>
            </td>

            <td>
                <div class="label">Claridad vocacional</div>
                <div>
                    <span class="badge {{ $clarityClass }}">
                        {{ $clarityLabel }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Ruta explorada --}}
    <div class="section">
        <div class="section-title">Ruta explorada</div>
        <div class="box box-muted">
            {{ $report->explored_routes }}
        </div>
    </div>

    {{-- Intereses --}}
    <div class="section">
        <div class="section-title">Intereses y antecedentes mencionados</div>
        <div class="box box-muted">
            {{ $report->interests }}
        </div>
    </div>

    {{-- Dos columnas: áreas y dudas --}}
    <table class="two-columns">
        <tr>
            <td class="left-col">
                <div class="section">
                    <div class="section-title">Áreas detectadas</div>
                    <div class="box box-blue">
                        {{ $report->detected_areas }}
                    </div>
                </div>
            </td>

            <td class="right-col">
                <div class="section">
                    <div class="section-title">Dudas principales</div>
                    <div class="box box-yellow">
                        {{ $report->main_questions }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Recomendaciones --}}
    <div class="section">
        <div class="section-title">Recomendaciones sugeridas</div>
        <div class="box box-green">
            {{ $report->recommendations }}
        </div>
    </div>

    {{-- Resumen estudiante --}}
    <div class="section">
        <div class="section-title">Resumen para el estudiante</div>
        <div class="box box-blue">
            {{ $report->student_summary }}
        </div>
    </div>

    {{-- Orientador --}}
    <div class="section">
        <div class="section-title">Sección técnica para el orientador</div>
        <div class="box box-orientador">
            {{ $report->orientador_notes }}
        </div>
    </div>

    {{-- Nota final --}}
    <div class="notice">
        <strong>Nota:</strong> Este reporte tiene carácter orientativo y preliminar. No reemplaza la entrevista con el orientador del colegio ni la revisión directa de fuentes oficiales. La información sobre admisión, beneficios, puntajes, ponderaciones, carreras, sedes, aranceles y requisitos puede cambiar y debe verificarse en los sitios oficiales correspondientes.
    </div>

    <div class="footer">
        Reporte generado por Orientador Vocacional IA · Instituto San José
    </div>
</body>

</html>