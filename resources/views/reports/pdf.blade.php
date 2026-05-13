<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Vocacional Individual</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
            margin: 24px;
        }

        .header {
            border-bottom: 3px solid #15803d;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: #14532d;
            font-size: 22px;
        }

        .header p {
            margin: 4px 0 0;
            color: #6b7280;
        }

        .section {
            margin-bottom: 18px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .section h2 {
            margin: 0 0 8px;
            font-size: 15px;
            color: #14532d;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
        }

        .grid td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .label {
            color: #6b7280;
            width: 35%;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: bold;
            color: #14532d;
            background: #dcfce7;
        }

        .preline {
            white-space: pre-line;
        }

        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Reporte Vocacional Individual</h1>
        <p>Orientador Vocacional IA - Instituto San José</p>
        <p>Documento generado con fines de orientación escolar.</p>
    </div>

    <div class="section">
        <h2>Datos generales</h2>

        <table class="grid">
            <tr>
                <td class="label">Estudiante</td>
                <td>{{ $report->student->name }}</td>
            </tr>
            <tr>
                <td class="label">Curso</td>
                <td>{{ $report->student->course }}</td>
            </tr>
            <tr>
                <td class="label">Colegio</td>
                <td>{{ $report->student->school }}</td>
            </tr>
            <tr>
                <td class="label">Fecha del reporte</td>
                <td>{{ $report->created_at->format('d-m-Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Ruta explorada</td>
                <td>{{ $report->explored_routes }}</td>
            </tr>
            <tr>
                <td class="label">Nivel de claridad vocacional</td>
                <td><span class="badge">{{ ucfirst($report->clarity_level) }}</span></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Intereses mencionados</h2>
        <div class="preline">{{ $report->interests }}</div>
    </div>

    <div class="section">
        <h2>Áreas detectadas</h2>
        <div>{{ $report->detected_areas }}</div>
    </div>

    <div class="section">
        <h2>Dudas principales</h2>
        <div class="preline">{{ $report->main_questions }}</div>
    </div>

    <div class="section">
        <h2>Recomendaciones sugeridas</h2>
        <div class="preline">{{ $report->recommendations }}</div>
    </div>

    <div class="section">
        <h2>Resumen para el estudiante</h2>
        <div class="preline">{{ $report->student_summary }}</div>
    </div>

    <div class="section">
        <h2>Sección técnica para el orientador</h2>
        <div class="preline">{{ $report->orientador_notes }}</div>
    </div>

    <div class="footer">
        Este reporte es orientativo. No reemplaza la entrevista con el orientador del colegio ni la revisión de fuentes oficiales sobre admisión, beneficios, acreditación, requisitos y fechas.
    </div>
</body>

</html>