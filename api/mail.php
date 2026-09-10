<?php
declare(strict_types=1);

/**
 * Shared Serum 72 transactional email helpers (Brevo HTML + PHP mail fallback).
 */

function s72_app_url(): string
{
    $url = rtrim((string) (getenv('APP_URL') ?: ''), '/');
    if ($url !== '') return $url;
    return 'https://www.serum72.com';
}

function s72_mail_from(): array
{
    return [
        'email' => getenv('SERUM72_MAIL_FROM') ?: 'website@serum72.com',
        'name' => 'Serum 72',
    ];
}

function s72_contact_to(): string
{
    return getenv('SERUM72_CONTACT_TO') ?: 'info@serum72.com';
}

function s72_popup_code(): string
{
    $code = strtoupper(trim((string) (getenv('SERUM72_POPUP_CODE') ?: 'SERUM20')));
    return $code !== '' ? $code : 'SERUM20';
}

function s72_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Wrap inner HTML in a Serum 72 branded email shell.
 */
function s72_branded_email_html(string $eyebrow, string $heading, string $innerHtml, ?array $cta = null): string
{
    $site = s72_app_url();
    $logo = $site . '/assets/site/logo-nav-hi.png';
    $shop = $site . '/shop/';
    $year = gmdate('Y');
    $eyebrowSafe = s72_escape($eyebrow);
    $headingSafe = s72_escape($heading);
    $ctaHtml = '';
    if ($cta && !empty($cta['href']) && !empty($cta['label'])) {
        $ctaHtml = '<tr><td style="padding:28px 0 8px;text-align:center;">'
            . '<a href="' . s72_escape((string) $cta['href']) . '" style="display:inline-block;min-width:200px;padding:16px 28px;background:#111111;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;letter-spacing:.14em;text-decoration:none;text-transform:uppercase;">'
            . s72_escape((string) $cta['label'])
            . '</a></td></tr>';
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$headingSafe}</title>
</head>
<body style="margin:0;padding:0;background:#f3f3f3;color:#111111;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f3f3;margin:0;padding:32px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;background:#ffffff;border:1px solid #111111;">
        <tr>
          <td style="padding:28px 36px 18px;background:#111111;text-align:center;">
            <img src="{$logo}" alt="Native Ceuticals" width="160" style="display:block;margin:0 auto 14px;max-width:160px;height:auto;border:0;">
            <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:.28em;text-transform:uppercase;color:#ffffff;">Serum 72</p>
            <p style="margin:8px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#cfcfcf;">Science-Backed Skincare</p>
          </td>
        </tr>
        <tr>
          <td style="padding:36px 36px 12px;">
            <p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#666666;">{$eyebrowSafe}</p>
            <h1 style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:30px;line-height:1.2;font-weight:400;color:#111111;">{$headingSafe}</h1>
            <div style="width:42px;height:1px;margin:18px 0 22px;background:#d4c4b5;"></div>
            <div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#333333;">
              {$innerHtml}
            </div>
          </td>
        </tr>
        {$ctaHtml}
        <tr>
          <td style="padding:28px 36px 32px;text-align:center;">
            <p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;color:#777777;">
              Native Ceuticals · Serum 72<br>
              <a href="{$shop}" style="color:#111111;text-decoration:underline;">Shop the collection</a>
              &nbsp;·&nbsp;
              <a href="{$site}/contact/" style="color:#111111;text-decoration:underline;">Contact</a>
            </p>
            <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#999999;">© {$year} Native Ceuticals. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
}

function s72_send_with_brevo(string $apiKey, array $message): bool
{
    if (!function_exists('curl_init')) return false;
    $payload = [
        'sender' => [
            'name' => $message['fromName'],
            'email' => $message['fromEmail'],
        ],
        'to' => [[
            'email' => $message['toEmail'],
            'name' => $message['toName'] ?: $message['toEmail'],
        ]],
        'subject' => $message['subject'],
        'htmlContent' => $message['html'],
        'textContent' => $message['text'],
    ];
    if (!empty($message['replyToEmail'])) {
        $payload['replyTo'] = [
            'email' => $message['replyToEmail'],
            'name' => $message['replyToName'] ?: $message['replyToEmail'],
        ];
    }
    $request = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($request, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'api-key: ' . $apiKey],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
    ]);
    curl_exec($request);
    $status = (int) curl_getinfo($request, CURLINFO_HTTP_CODE);
    curl_close($request);
    return $status >= 200 && $status < 300;
}

function s72_send_with_php_mail(array $message): bool
{
    $boundary = '=_s72_' . bin2hex(random_bytes(8));
    $from = $message['fromName'] . ' <' . $message['fromEmail'] . '>';
    $headers = [
        'MIME-Version: 1.0',
        'From: ' . $from,
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    if (!empty($message['replyToEmail'])) {
        $replyName = $message['replyToName'] ?: $message['replyToEmail'];
        $headers[] = 'Reply-To: ' . $replyName . ' <' . $message['replyToEmail'] . '>';
    }
    $body = '--' . $boundary . "\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . $message['text'] . "\r\n"
        . '--' . $boundary . "\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . $message['html'] . "\r\n"
        . '--' . $boundary . "--\r\n";
    return @mail(
        $message['toEmail'],
        '=?UTF-8?B?' . base64_encode($message['subject']) . '?=',
        $body,
        implode("\r\n", $headers)
    );
}

/**
 * Send one branded transactional email. Returns true on success.
 *
 * @param array{toEmail:string,toName?:string,subject:string,html:string,text:string,replyToEmail?:string,replyToName?:string} $message
 */
function s72_send_email(array $message): bool
{
    $from = s72_mail_from();
    $message['fromEmail'] = $from['email'];
    $message['fromName'] = $from['name'];
    $message['toName'] = $message['toName'] ?? '';
    $brevoKey = getenv('SERUM72_BREVO_API_KEY') ?: '';
    if ($brevoKey !== '') {
        return s72_send_with_brevo($brevoKey, $message);
    }
    return s72_send_with_php_mail($message);
}

function s72_rows_html(array $rows): string
{
    $html = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 8px;border-collapse:collapse;">';
    foreach ($rows as $label => $value) {
        $html .= '<tr>'
            . '<td style="padding:8px 0;border-bottom:1px solid #ececec;width:34%;font-family:Arial,Helvetica,sans-serif;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#777777;vertical-align:top;">'
            . s72_escape((string) $label)
            . '</td>'
            . '<td style="padding:8px 0;border-bottom:1px solid #ececec;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#111111;vertical-align:top;">'
            . nl2br(s72_escape((string) $value))
            . '</td>'
            . '</tr>';
    }
    $html .= '</table>';
    return $html;
}

function s72_lead_notify_email(string $source, string $name, string $email, string $phone, string $message, ?string $code = null): array
{
    $sourceLabel = match ($source) {
        'chat' => 'Website chat',
        'popup' => '20% off popup',
        default => 'Contact form',
    };
    $subject = match ($source) {
        'chat' => 'New website chat lead — ' . $name,
        'popup' => 'New 20% off lead — ' . $name,
        default => 'New website contact — ' . $name,
    };
    $rows = [
        'Source' => $sourceLabel,
        'Name' => $name,
        'Email' => $email,
    ];
    if ($phone !== '') $rows['Phone'] = $phone;
    if ($code !== null) $rows['Offer code'] = $code;
    $rows['Message'] = $message !== '' ? $message : 'No message supplied.';

    $inner = '<p style="margin:0 0 18px;">A new lead just came in from the Serum 72 website.</p>'
        . s72_rows_html($rows);
    $html = s72_branded_email_html('New lead', 'Better skin starts here.', $inner, [
        'href' => 'mailto:' . $email,
        'label' => 'Reply to lead',
    ]);
    $text = "New Serum 72 lead ({$sourceLabel})\n"
        . "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n"
        . ($code ? "Offer code: {$code}\n" : '')
        . "Message:\n" . ($message !== '' ? $message : 'No message supplied.');
    return compact('subject', 'html', 'text');
}

function s72_popup_welcome_email(string $firstName, string $code): array
{
    $site = s72_app_url();
    $shop = $site . '/shop/';
    $greeting = $firstName !== '' ? $firstName : 'there';
    $subject = 'Your 20% off code — Serum 72';
    $inner = '<p style="margin:0 0 16px;">Hi ' . s72_escape($greeting) . ',</p>'
        . '<p style="margin:0 0 16px;">Welcome to Serum 72. Here is your exclusive code for <strong>20% off your first order</strong>:</p>'
        . '<p style="margin:0 0 22px;padding:18px 16px;background:#f5f5f5;border:1px solid #111111;text-align:center;font-family:Georgia,\'Times New Roman\',serif;font-size:28px;letter-spacing:.12em;color:#111111;">'
        . s72_escape($code)
        . '</p>'
        . '<p style="margin:0 0 16px;">Enter this code at checkout. One use per customer on your first order.</p>'
        . '<p style="margin:0;">Science-backed formulas. Clean ingredients. Skin that starts here.</p>';
    $html = s72_branded_email_html('Welcome offer', 'Your 20% off awaits.', $inner, [
        'href' => $shop,
        'label' => 'Shop now',
    ]);
    $text = "Hi {$greeting},\n\n"
        . "Welcome to Serum 72. Your 20% off code for your first order is:\n\n"
        . "{$code}\n\n"
        . "Enter this code at checkout. Shop: {$shop}\n\n"
        . "— Serum 72 / Native Ceuticals";
    return compact('subject', 'html', 'text');
}
