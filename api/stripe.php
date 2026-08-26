<?php
declare(strict_types=1);
require_once __DIR__ . '/commerce.php';

function s72_stripe_request(string $method, string $path, array $params = []): array {
    $key = (string)(getenv('STRIPE_SECRET_KEY') ?: '');
    if (!str_starts_with($key, 'sk_test_')) throw new RuntimeException('Stripe sandbox is not configured.');
    $url = 'https://api.stripe.com/v1/' . ltrim($path, '/');
    if ($method === 'GET' && $params) $url .= '?' . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_USERPWD=>$key . ':',CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],CURLOPT_TIMEOUT=>30]);
    if ($method !== 'GET') curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    $raw = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE); $error = curl_error($ch); curl_close($ch);
    if ($raw === false) throw new RuntimeException('Stripe connection failed: ' . $error);
    $data = json_decode($raw, true);
    if (!is_array($data) || $status < 200 || $status >= 300) throw new RuntimeException((string)($data['error']['message'] ?? 'Stripe request failed.'));
    return $data;
}
function s72_checkout_path(string $token): string { return s72_data_dir('stripe/checkouts') . '/' . $token . '.json'; }
function s72_order_path(string $id): string { return s72_data_dir('orders') . '/' . $id . '.json'; }
function s72_subscription_path(string $id): string { return s72_data_dir('stripe/subscriptions') . '/' . preg_replace('/[^A-Za-z0-9_]/', '', $id) . '.json'; }
function s72_build_cart(array $items): array {
    $catalog = s72_catalog(); $cart = [];
    foreach ($items as $raw) {
        $slug = (string)($raw['slug'] ?? ''); $quantity = (int)($raw['quantity'] ?? 0); $type = ($raw['purchaseType'] ?? 'once') === 'subscribe' ? 'subscribe' : 'once';
        if (!isset($catalog[$slug]) || $quantity < 1 || $quantity > 25) throw new InvalidArgumentException('Your cart contains an invalid item or quantity.');
        $product = $catalog[$slug]; $unit = $type === 'subscribe' ? $product['subscriptionPrice'] : $product['price'];
        $cart[] = ['slug'=>$slug,'name'=>$product['name'],'quantity'=>$quantity,'purchaseType'=>$type,'unitAmount'=>$unit];
    }
    if (!$cart) throw new InvalidArgumentException('Your cart is empty.');
    return $cart;
}
function s72_create_session(array $payload): array {
    $cart = s72_build_cart(is_array($payload['items'] ?? null) ? $payload['items'] : []);
    $hasSubscription = (bool)array_filter($cart, fn($item) => $item['purchaseType'] === 'subscribe');
    $token = bin2hex(random_bytes(20)); $expected = 0;
    $params = ['mode'=>$hasSubscription?'subscription':'payment','success_url'=>s72_site_url().'/order/?session_id={CHECKOUT_SESSION_ID}','cancel_url'=>s72_site_url().'/checkout/?payment=cancelled','client_reference_id'=>$token,'metadata'=>['checkout_token'=>$token],'billing_address_collection'=>'required','shipping_address_collection'=>['allowed_countries'=>['US']],'phone_number_collection'=>['enabled'=>'true'],'payment_method_types'=>['card']];
    foreach ($cart as $i=>$item) {
        $price = ['currency'=>'usd','unit_amount'=>$item['unitAmount'],'product_data'=>['name'=>$item['name'],'metadata'=>['slug'=>$item['slug'],'purchase_type'=>$item['purchaseType']]]];
        if ($item['purchaseType'] === 'subscribe') $price['recurring'] = ['interval'=>'month'];
        $params['line_items'][$i] = ['quantity'=>$item['quantity'],'price_data'=>$price];
        $expected += $item['unitAmount'] * $item['quantity'];
    }
    $record = ['createdAt'=>gmdate('c'),'cart'=>$cart,'expectedAmount'=>$expected,'stripeSessionId'=>null,'orderId'=>null];
    file_put_contents(s72_checkout_path($token), json_encode($record, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), LOCK_EX);
    try { $session = s72_stripe_request('POST', 'checkout/sessions', $params); } catch (Throwable $e) { @unlink(s72_checkout_path($token)); throw $e; }
    $record['stripeSessionId'] = $session['id']; file_put_contents(s72_checkout_path($token), json_encode($record, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), LOCK_EX);
    return ['url'=>$session['url'],'sessionId'=>$session['id']];
}
function s72_complete_session(string $sessionId): array {
    if (!preg_match('/^cs_(test_|live_)?[A-Za-z0-9_]+$/', $sessionId)) throw new InvalidArgumentException('Invalid checkout session.');
    $session = s72_stripe_request('GET', 'checkout/sessions/' . rawurlencode($sessionId), ['expand'=>['payment_intent.payment_method']]);
    if (($session['status'] ?? '') !== 'complete' || ($session['payment_status'] ?? '') !== 'paid') throw new RuntimeException('Payment has not been completed.');
    $token = (string)($session['metadata']['checkout_token'] ?? ''); $path = s72_checkout_path($token);
    if (!preg_match('/^[a-f0-9]{40}$/', $token) || !is_file($path)) throw new RuntimeException('Checkout record not found.');
    $fp = fopen($path, 'c+'); if (!$fp || !flock($fp, LOCK_EX)) throw new RuntimeException('Unable to finalize checkout.');
    $record = json_decode(stream_get_contents($fp) ?: '', true);
    if (!empty($record['orderId']) && is_file(s72_order_path($record['orderId']))) {
        $orderPath=s72_order_path($record['orderId']);$order=json_decode(file_get_contents($orderPath),true);
        if(empty($order['shipping'])&&!empty($session['collected_information']['shipping_details']['address'])){$order['shipping']=$session['collected_information']['shipping_details']['address'];file_put_contents($orderPath,json_encode($order,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX);}
        flock($fp,LOCK_UN);fclose($fp);return $order;
    }
    if ((int)($session['amount_total'] ?? -1) !== (int)($record['expectedAmount'] ?? 0)) { flock($fp,LOCK_UN);fclose($fp);throw new RuntimeException('Paid amount does not match the checkout total.'); }
    $id = 'S72-' . strtoupper(bin2hex(random_bytes(5))); $details = $session['customer_details'] ?? []; $shipping = $session['collected_information']['shipping_details'] ?? $session['shipping_details'] ?? [];
    $order = ['id'=>$id,'createdAt'=>gmdate('c'),'status'=>'paid','items'=>$record['cart'],'total'=>(int)$session['amount_total']/100,'currency'=>strtoupper((string)($session['currency']??'usd')),'customer'=>['name'=>$details['name']??$shipping['name']??'','email'=>$details['email']??'','phone'=>$details['phone']??''],'shipping'=>$shipping['address']??null,'stripeSessionId'=>$sessionId,'stripeSubscriptionId'=>is_array($session['subscription']??null)?($session['subscription']['id']??null):($session['subscription']??null)];
    file_put_contents(s72_order_path($id), json_encode($order, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), LOCK_EX);
    if (!empty($order['stripeSubscriptionId'])) file_put_contents(s72_subscription_path((string)$order['stripeSubscriptionId']), json_encode(['orderId'=>$id], JSON_UNESCAPED_SLASHES), LOCK_EX);
    $record['orderId']=$id; ftruncate($fp,0);rewind($fp);fwrite($fp,json_encode($record,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));fflush($fp);flock($fp,LOCK_UN);fclose($fp);
    return $order;
}
function s72_record_paid_invoice(array $invoice): void {
    $invoiceId=(string)($invoice['id']??'');$subscription=$invoice['subscription']??null;if(is_array($subscription))$subscription=$subscription['id']??'';$subscription=(string)$subscription;
    if($invoiceId===''||$subscription===''||($invoice['status']??'')!=='paid'||($invoice['billing_reason']??'')==='subscription_create')return;
    $binding=s72_subscription_path($subscription);if(!is_file($binding))return;$index=json_decode(file_get_contents($binding),true);$orderPath=s72_order_path((string)($index['orderId']??''));if(!is_file($orderPath))return;
    $fp=fopen($orderPath,'c+');if(!$fp||!flock($fp,LOCK_EX))throw new RuntimeException('Unable to record subscription renewal.');$order=json_decode(stream_get_contents($fp)?:'',true);$renewals=$order['renewals']??[];
    foreach($renewals as $renewal){if(($renewal['invoiceId']??'')===$invoiceId){flock($fp,LOCK_UN);fclose($fp);return;}}
    $renewals[]=['invoiceId'=>$invoiceId,'paidAt'=>gmdate('c'),'amount'=>(int)($invoice['amount_paid']??0)/100,'status'=>'paid'];$order['renewals']=$renewals;ftruncate($fp,0);rewind($fp);fwrite($fp,json_encode($order,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));fflush($fp);flock($fp,LOCK_UN);fclose($fp);
}
function s72_verify_signature(string $payload, string $header, string $secret): bool {
    $parts=[];foreach(explode(',',$header) as $part){[$k,$v]=array_pad(explode('=',trim($part),2),2,'');$parts[$k][]=$v;}$timestamp=$parts['t'][0]??'';
    if(!preg_match('/^\d+$/',$timestamp)||abs(time()-(int)$timestamp)>300)return false;$expected=hash_hmac('sha256',$timestamp.'.'.$payload,$secret);
    foreach($parts['v1']??[] as $signature)if(hash_equals($expected,$signature))return true;return false;
}
