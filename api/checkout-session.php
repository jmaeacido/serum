<?php
require __DIR__ . '/stripe.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') s72_json(405, ['error'=>'Method not allowed.']);
try { s72_json(201, s72_create_session(s72_body())); } catch (InvalidArgumentException $e) { s72_json(400,['error'=>$e->getMessage()]); } catch (Throwable $e) { s72_json(502,['error'=>$e->getMessage()]); }
