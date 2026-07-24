<?php
require __DIR__.'/config.php';
require __DIR__.'/utils.php';
$id_santri = intval($_POST['id_santri'] ?? 0);
$nominal = intval($_POST['nominal'] ?? 0);
if ($id_santri<=0 || $nominal<=0) json_err('params invalid');
$mysqli->begin_transaction();
try {
  $u = $mysqli->prepare("UPDATE santri SET saldo = saldo + ? WHERE id = ?");
  $u->bind_param('ii',$nominal,$id_santri); $u->execute();
  $ins = $mysqli->prepare("INSERT INTO topup (id_santri,nominal,metode,created_at) VALUES (?,?, 'tunai', NOW())");
  $ins->bind_param('ii',$id_santri,$nominal); $ins->execute();
  $mysqli->commit(); json_ok();
} catch (Exception $e) { $mysqli->rollback(); json_err($e->getMessage()); }
