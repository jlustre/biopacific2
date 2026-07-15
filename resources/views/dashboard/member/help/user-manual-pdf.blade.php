<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bio-Pacific {{ $documentTitle }}</title>
    <style>
        @page {
            margin: 0.65in 0.7in 0.7in;
        }

        body {
            color: #1e293b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5pt;
            line-height: 1.45;
        }

        .document-header {
            border-bottom: 2px solid #0f766e;
            margin-bottom: 22px;
            padding-bottom: 12px;
        }

        .brand {
            color: #0f766e;
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .document-title {
            color: #0f172a;
            font-size: 20pt;
            margin: 4px 0 2px;
        }

        .document-meta {
            color: #64748b;
            font-size: 8.5pt;
        }

        h1 {
            border-bottom: 1px solid #99f6e4;
            color: #0f766e;
            font-size: 18pt;
            margin: 24px 0 10px;
            padding-bottom: 5px;
        }

        h2 {
            color: #115e59;
            font-size: 14pt;
            margin: 20px 0 8px;
        }

        h3 {
            color: #0f172a;
            font-size: 11.5pt;
            margin: 16px 0 6px;
        }

        h4 {
            color: #334155;
            font-size: 10pt;
            margin: 12px 0 5px;
        }

        p {
            margin: 0 0 8px;
        }

        ul,
        ol {
            margin: 4px 0 10px 20px;
            padding: 0;
        }

        li {
            margin-bottom: 4px;
        }

        strong {
            color: #0f172a;
        }

        blockquote {
            background: #f0fdfa;
            border-left: 4px solid #14b8a6;
            color: #334155;
            margin: 10px 0;
            padding: 8px 12px;
        }

        code {
            background: #f1f5f9;
            color: #0f172a;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 8.5pt;
            padding: 1px 3px;
        }

        pre {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px;
            white-space: pre-wrap;
        }

        table {
            border-collapse: collapse;
            margin: 10px 0 14px;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f0fdfa;
            color: #115e59;
            font-weight: bold;
        }

        a {
            color: #0f766e;
            text-decoration: none;
        }

        hr {
            border: 0;
            border-top: 1px solid #cbd5e1;
            margin: 18px 0;
        }
    </style>
</head>
<body>
    <header class="document-header">
        <div class="brand">Bio-Pacific · HR Employee Portal</div>
        <div class="document-title">{{ $documentTitle }}</div>
        <div class="document-meta">Generated from the maintained portal documentation · Updated {{ $updatedAt }}</div>
    </header>

    <main>
        {!! $content !!}
    </main>
</body>
</html>
