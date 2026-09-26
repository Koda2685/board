<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->title }} - PDF Viewer</title>
    <style>
        html, body {
            margin: 0;
            height: 100%;
            background: #f3f4f6;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            flex-direction: column;
        }
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #111827;
            color: white;
            padding: 12px 16px;
            border-bottom: 1px solid #374151;
        }
        .toolbar h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .toolbar a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 6px;
            padding: 6px 10px;
        }
        .viewer-shell {
            flex: 1;
            min-height: 0;
            background: #e5e7eb;
            padding: 16px;
        }
        .viewer-frame {
            width: 100%;
            height: 100%;
            min-height: 72vh;
            border: 0;
            background: white;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>{{ $document->title }}</h1>
    </div>

    <div class="viewer-shell">
        <embed
            class="viewer-frame"
            src="{{ route('admin.governance-documents.download', $document) }}"
            type="application/pdf"
            title="PDF Viewer"
        ></embed>
    </div>
</body>
</html>
