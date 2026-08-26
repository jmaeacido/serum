<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-bootstrap.php';
s72_require_admin();

function s72_orders(): array {
    $orders=[]; foreach (glob(dirname(__DIR__).'/data/orders/*.json') ?: [] as $file) { $order=json_decode((string)file_get_contents($file),true); if(is_array($order))$orders[]=$order; }
    usort($orders,fn(array $a,array $b):int=>strcmp((string)($b['createdAt']??''),(string)($a['createdAt']??''))); return $orders;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $body=s72_body();$id=preg_replace('/[^A-Z0-9-]/','',strtoupper((string)($body['id']??'')));$status=(string)($body['status']??'');$allowed=['paid','processing','fulfilled','cancelled','refunded'];
    if($id===''||!in_array($status,$allowed,true))s72_json(['error'=>'Invalid order update.'],422);$path=dirname(__DIR__).'/data/orders/'.$id.'.json';if(!is_file($path))s72_json(['error'=>'Order not found.'],404);
    $order=json_decode((string)file_get_contents($path),true);$order['status']=$status;$order['updatedAt']=gmdate('c');file_put_contents($path,json_encode($order,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX);s72_json(['order'=>$order]);
}
if($_SERVER['REQUEST_METHOD']!=='GET')s72_json(['error'=>'Method not allowed.'],405);
$orders=s72_orders();$messages=s72_email_records();$catalog=[
 ['slug'=>'night-cream','name'=>'Night Cream','price'=>69.99,'subscriptionPrice'=>63.99,'image'=>'/assets/products/night-cream.png','stock'=>42],['slug'=>'day-cream','name'=>'Day Cream','price'=>49.99,'subscriptionPrice'=>44.99,'image'=>'/assets/products/day-cream.png','stock'=>38],['slug'=>'serum','name'=>'Serum','price'=>59.99,'subscriptionPrice'=>53.99,'image'=>'/assets/products/serum.png','stock'=>24],['slug'=>'mineral-sunscreen','name'=>'Mineral Sunscreen','price'=>39.99,'subscriptionPrice'=>35.99,'image'=>'/assets/products/mineral-sunscreen.png','stock'=>51],['slug'=>'dual-action-cleanser','name'=>'Dual Action Cleanser','price'=>34.99,'subscriptionPrice'=>31.49,'image'=>'/assets/products/dual-action-cleanser.webp','stock'=>47]];
$revenue=0.0;$units=0;$subscribers=0;$customers=[];foreach($orders as $order){if(!in_array($order['status']??'',['cancelled','refunded'],true))$revenue+=(float)($order['total']??0);if(!empty($order['stripeSubscriptionId']))$subscribers++;foreach($order['items']??[] as $item)$units+=(int)($item['quantity']??0);$email=strtolower(trim((string)($order['customer']['email']??'')));if($email!=='')$customers[$email]=true;}
s72_json(['metrics'=>['revenue'=>$revenue,'orders'=>count($orders),'customers'=>count($customers),'subscribers'=>$subscribers,'units'=>$units,'messages'=>count($messages)],'orders'=>$orders,'products'=>$catalog,'messages'=>array_slice($messages,0,5)]);
