<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Exportação de Questões — Provora</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
            @bottom-right {
                content: counter(page) " / " counter(pages);
                font-family: sans-serif;
                font-size: 9pt;
                color: #666;
            }
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.5;
            font-size: 11pt;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 18pt;
            margin: 0 0 6px 0;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-meta {
            font-size: 9.5pt;
            color: #475569;
            display: table;
            width: 100%;
        }
        .header-meta-left {
            display: table-cell;
            text-align: left;
        }
        .header-meta-right {
            display: table-cell;
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-transform: capitalize;
        }
        .question-card {
            margin-bottom: 24px;
            page-break-inside: avoid;
        }
        .question-number {
            font-size: 11pt;
            font-weight: 700;
            color: #0284c7;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .question-statement {
            font-size: 11pt;
            color: #0f172a;
            margin-bottom: 12px;
            text-align: justify;
            white-space: pre-line;
        }
        .alternatives {
            list-style-type: none;
            padding-left: 0;
            margin: 0;
        }
        .alternative-item {
            margin-bottom: 6px;
            padding: 6px 10px;
            border-radius: 4px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 10.5pt;
        }
        .alternative-letter {
            font-weight: 700;
            color: #0f172a;
            margin-right: 6px;
        }
        .footer-note {
            margin-top: 30px;
            border-top: 1px solid #cbd5e1;
            padding-top: 12px;
            text-align: center;
            font-size: 8.5pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Provora — Avaliação de Questões</h1>
        <div class="header-meta">
            <div class="header-meta-left">
                Área: <span class="badge">{{ ucfirst($batch->knowledge_area ?? 'Geral') }}</span> &nbsp;|&nbsp;
                Dificuldade: <span class="badge">{{ ucfirst($batch->difficulty ?? 'Média') }}</span>
            </div>
            <div class="header-meta-right">
                Total de Questões: <strong>{{ $questions->count() }}</strong> &nbsp;|&nbsp;
                Data: {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    @foreach($questions as $index => $question)
        <div class="question-card">
            <div class="question-number">QUESTÃO {{ sprintf('%02d', $index + 1) }}</div>
            <div class="question-statement">{{ $question->statement }}</div>

            @if(!empty($question->alternatives) && is_array($question->alternatives))
                <ul class="alternatives">
                    @foreach($question->alternatives as $altKey => $altValue)
                        @php
                            if (is_array($altValue)) {
                                $letter = $altValue['letter'] ?? (is_numeric($altKey) ? chr(65 + (int)$altKey) : strtoupper((string)$altKey));
                                $text = $altValue['text'] ?? implode(' ', array_filter($altValue, 'is_string'));
                            } else {
                                $letter = is_numeric($altKey) ? chr(65 + (int)$altKey) : strtoupper((string)$altKey);
                                $text = $altValue;
                            }
                        @endphp
                        <li class="alternative-item">
                            <span class="alternative-letter">({{ $letter }})</span> {{ $text }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach

    <div class="footer-note">
        Documento gerado automaticamente pela plataforma Provora.
    </div>

</body>
</html>
