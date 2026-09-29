<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
</head>
<body style="font-family: Arial, sans-serif; color: #222;">
    <p>Olá, {{ $notice->personName() }}!</p>
    <p>Sua hospedagem no evento <strong>{{ $notice->eventName() }}</strong> está definida.</p>
    <ul>
        <li><strong>Local:</strong> {{ $notice->fullAddress() }}</li>
        <li><strong>Entrada:</strong> {{ $notice->checkIn()->format('d/m/Y') }}</li>
        <li><strong>Saída:</strong> {{ $notice->checkOut()->format('d/m/Y') }}</li>
        @if ($notice->roomName() !== '')
        <li><strong>Quarto:</strong> {{ $notice->roomName() }}@if ($notice->roomTypeName() !== '') ({{ $notice->roomTypeName() }})@endif</li>
        @endif
        <li><strong>Reserva:</strong> {{ $notice->reservationNumber() }}</li>
    </ul>
    @if (!empty($notice->allocation->event->contact_name) || !empty($notice->allocation->event->contact_phone))
    <p>Dúvidas: {{ trim($notice->allocation->event->contact_name . ' ' . $notice->allocation->event->contact_phone) }}</p>
    @endif
    <p>O arquivo em anexo (hospedagem.ics) adiciona as datas à sua agenda.</p>
</body>
</html>
