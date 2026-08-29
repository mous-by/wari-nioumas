<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @include('pdf.partials.badge-styles')
        body { padding: 18pt; }
        table.sheet { width: 100%; border-collapse: separate; border-spacing: 14pt; page-break-inside: avoid; }
        table.sheet td { width: 25%; text-align: center; vertical-align: top; }
        table.sheet .card { border: 0.75pt dashed #9ca3af; }
    </style>
</head>
<body>
    @foreach ($sheets as $sheet)
        <table class="sheet" @if (!$loop->first) style="page-break-before: always;" @endif>
            @foreach ($sheet['chauffeurs']->chunk(4) as $row)
                <tr>
                    @foreach ($row as $chauffeur)
                        <td>
                            @if ($sheet['type'] === 'recto')
                                @include('pdf.partials.badge-recto', ['chauffeur' => $chauffeur])
                            @else
                                @include('pdf.partials.badge-verso', ['chauffeur' => $chauffeur, 'qrDataUri' => $qrByChauffeur[$chauffeur->id]])
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    @endforeach
</body>
</html>
