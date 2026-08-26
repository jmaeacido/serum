<?php
require __DIR__ . '/stripe.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') s72_json(405,['error'=>'Method not allowed.']);
$secret=(string)(getenv('STRIPE_WEBHOOK_SECRET')?:'');$payload=file_get_contents('php://input')?:'';$header=(string)($_SERVER['HTTP_STRIPE_SIGNATURE']??'');
if(!str_starts_with($secret,'whsec_'))s72_json(503,['error'=>'Stripe webhook is not configured.']);
if(!s72_verify_signature($payload,$header,$secret))s72_json(400,['error'=>'Invalid Stripe signature.']);
$event=json_decode($payload,true);$id=(string)($event['id']??'');$type=(string)($event['type']??'');if($id===''||$type==='')s72_json(400,['error'=>'Invalid Stripe event.']);$eventPath=s72_data_dir('stripe/events').'/'.preg_replace('/[^A-Za-z0-9_]/','',$id).'.json';
if(is_file($eventPath))s72_json(200,['received'=>true,'duplicate'=>true]);
try{if($type==='checkout.session.completed'){s72_complete_session((string)($event['data']['object']['id']??''));}elseif($type==='invoice.paid'){s72_record_paid_invoice((array)($event['data']['object']??[]));}file_put_contents($eventPath,json_encode(['id'=>$id,'type'=>$type,'processedAt'=>gmdate('c')]),LOCK_EX);s72_json(200,['received'=>true]);}catch(Throwable $e){s72_json(500,['error'=>$e->getMessage()]);}
