<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Atualizacao do pedido</title>
</head>
<body style="margin:0;background:#f5f5f7;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f5f7;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:28px 32px;background:#111827;color:#ffffff;">
                            <p style="margin:0 0 8px;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#fca5a5;">SenaiStock</p>
                            <h1 style="margin:0;font-size:24px;line-height:1.25;">Pedido {{ $teacherRequest->protocol }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 20px;font-size:16px;line-height:1.6;">Olá, {{ $teacherRequest->teacher_name }}.</p>
                            <p style="margin:0 0 24px;font-size:16px;line-height:1.6;">{{ $messageBody }}</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f9fafb;border-radius:14px;overflow:hidden;">
                                <tr>
                                    <td style="padding:14px 16px;border-bottom:1px solid #e5e7eb;color:#6b7280;">Material</td>
                                    <td style="padding:14px 16px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:700;">{{ $teacherRequest->title }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px;border-bottom:1px solid #e5e7eb;color:#6b7280;">Quantidade</td>
                                    <td style="padding:14px 16px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:700;">{{ $teacherRequest->quantity }} un</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px;color:#6b7280;">Status</td>
                                    <td style="padding:14px 16px;text-align:right;font-weight:700;text-transform:capitalize;">{{ $teacherRequest->status }}</td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;font-size:13px;line-height:1.6;color:#6b7280;">Guarde o protocolo para acompanhar o pedido pela área pública do sistema.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
