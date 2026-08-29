<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @include('pdf.partials.badge-styles')
        body { padding: 0; }
    </style>
</head>
<body>
    @include('pdf.partials.badge-recto', ['chauffeur' => $chauffeur])
    @include('pdf.partials.badge-verso', ['chauffeur' => $chauffeur, 'qrDataUri' => $qrDataUri, 'standalone' => true])
</body>
</html>
