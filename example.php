<?php
      // connect database
  require_once('config.php');
  $id = "1399659";
  $tid = "172169";
  $description ="ND:CT DEN:591978255731 ICB;104876503702;Tien ao quan; tai Napas";
  $amount =50000;
  $cusum_balance = 250000;
  $when = "2022-08-08 20:48:57";
  $bank_sub_acc_id = "104876503702";
  $subAccId = "104876503702";
  $virtualAccount ="";
  $virtualAccountName = "";
  $corresponsiveName = "";
  $corresponsiveAccount = "";
  $corresponsiveBankId = "";
  $corresponsiveBankName = "";
  $trangthai="";
  $insert = "INSERT INTO thu_chi(id, tid, description1, amount, cusum_balance, time1, bank_sub_acc_id, subAccId, virtualAccount, virtualAccountName, corresponsiveName, corresponsiveAccount, 	corresponsiveBankId, corresponsiveBankName, trang_thai) VALUES('$id', '$tid', '$description', $amount, $cusum_balance, '$when', '$bank_sub_acc_id', '$subAccId', '$virtualAccount', '$virtualAccountName', '$corresponsiveName', '$corresponsiveAccount', '$corresponsiveBankId', '$corresponsiveBankName', '$trangthai')";  
  mysqli_query($conn, $insert);
  echo "Tai thanh cong";
  ?>