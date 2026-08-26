<?php
require __DIR__ . '/stripe.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') s72_json(405, ['error'=>'Method not allowed.']);
try { $body=s72_body();s72_json(200,['order'=>s72_complete_session(trim((string)($body['sessionId']??'')))]); } catch (InvalidArgumentException $e) { s72_json(400,['error'=>$e->getMessage()]); } catch (Throwable $e) { s72_json(409,['error'=>$e->getMessage()]); }
