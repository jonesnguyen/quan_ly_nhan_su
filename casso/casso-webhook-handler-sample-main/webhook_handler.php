<?php
      // connect database
  
	//GIÁ TIỀN TỔNG CỘNG CỦA ĐƠN HÀNG GIẢ ĐỊNH.
	$ORDER_MONEY = 100000;

	//Số tiền chuyển thiếu tối đa mà hệ thống vẫn chấp nhận để xác nhận đã thanh toán
	$ACCEPTABLE_DIFFERENCE = 10000;

	//Tiền tố điền trước mã đơn hàng để tạo mã cho khách hàng chuyển tiền
	//Không phân biệt hoa thường  : DH123, dh123, Dh123, dH123 đều dc.
	$MEMO_PREFIX = 'DH';

	//Key bảo mật đã cấu hình bên Casso để chứng thực request
	$HEADER_SECURE_TOKEN = 'Hieunguyen';

    payment_handler();

	function payment_handler(){
		global $ORDER_MONEY,$ACCEPTABLE_DIFFERENCE,$MEMO_PREFIX,$HEADER_SECURE_TOKEN;
		$txtBody = file_get_contents('php://input');
		$jsonBody = json_decode($txtBody); //convert JSON into array
		if (!$txtBody || !$jsonBody){
			echo "Request thiếu body";
			die();
		}
		if ($jsonBody->error != 0){
			echo "Có lỗi xay ra ở phía Casso";
			die();
		}

		$headers = getHeader();

		if ( $headers['Secure-Token'] != $HEADER_SECURE_TOKEN ) {
			echo("Thiếu Secure Token hoặc secure token không khớp");
			die(); 
		}

		foreach ($jsonBody->data as $key => $transaction) {
			$id = $transaction ->id;
			$tid = $transaction ->tid;
			$description = $transaction ->description;
			$amount = $transaction ->amount;
			$cusum_balance = $transaction ->cusum_balance;
			$when = $transaction ->when;
			$bank_sub_acc_id = $transaction ->bank_sub_acc_id;
			$subAccId = $transaction ->subAccId;
			$virtualAccount = $transaction ->virtualAccount;
			$virtualAccountName = $transaction ->virtualAccountName;
			$corresponsiveName = $transaction ->corresponsiveName;
			$corresponsiveAccount = $transaction ->corresponsiveAccount;
			$corresponsiveBankId = $transaction ->corresponsiveBankId;
			$corresponsiveBankName = $transaction ->corresponsiveBankName;
			$order_id =parse_order_id($description);
			
			if (is_null($order_id)) {
				echo ("<div>Không nhận dạng được order_id từ nội dung chuyển tiền : " . $transaction ->description. "</div>");
				$trangthai="Không nhận dạng được order_id từ nội dung chuyển tiền : " . $transaction ->description;
				continue;
			}
			echo ("<div>Nhận dạng order_id là " . $order_id. "</div>");

			$paid = $transaction->amount;
			$total=number_format($transaction->amount, 0);
			$order_note = "Casso thông báo nhận <b>{$total}</b> VND, nội dung <B>{$description}</B> chuyển vào <b>STK {$transaction->bank_sub_acc_id}</b>";
			$ACCEPTABLE_DIFFERENCE = abs($ACCEPTABLE_DIFFERENCE);

			if ( $paid < $ORDER_MONEY  - $ACCEPTABLE_DIFFERENCE ){
				//echo($order_note.'. Trạng thái đơn hàng đã được chuyển từ Tạm giữ sang Thanh toán thiếu.');
				$trangthai=$order_note.'. Trạng thái đơn hàng đã được chuyển từ Tạm giữ sang Thanh toán thiếu.';

			} else if ($paid <= $ORDER_MONEY + $ACCEPTABLE_DIFFERENCE){
				// $order->payment_complete();//
				// wc_reduce_stock_levels($order_id);
				// $order->update_status('paid', $order_note); // order note is optional, if you want to  add a note to order
				//echo($order_note.'. Trạng thái đơn hàng đã được chuyển từ Tạm giữ sang Đã thanh toán.');
				$trangthai=$order_note.'. Trạng thái đơn hàng đã được chuyển từ Tạm giữ sang Đã thanh toán.';

			} else {
				//echo($order_note.'. Trạng thái đơn hàng đã được chuyển từ Tạm giữ sang Thanh toán dư.');
				$trangthai=$order_note.'. Trạng thái đơn hàng đã được chuyển từ Tạm giữ sang Thanh toán dư.';
				// $order->payment_complete();
				// wc_reduce_stock_levels($order_id);//final
				// $order->update_status('overpaid', $order_note); // order note is optional, if you want to  add a note to order

			}
		}
  
  echo "Thanh cong";
  require_once('config.php');
  $insert = "INSERT INTO thu_chi(id, tid, description1, amount, cusum_balance, time1, bank_sub_acc_id, subAccId, virtualAccount, virtualAccountName, corresponsiveName, corresponsiveAccount, corresponsiveBankId, corresponsiveBankName, trang_thai) VALUES('$id', '$tid', '$description', '$amount', '$cusum_balance', '$when', '$bank_sub_acc_id', '$subAccId', '$virtualAccount', '$virtualAccountName', '$corresponsiveName', '$corresponsiveAccount', '$corresponsiveBankId', '$corresponsiveBankName', '$trangthai')";  
  mysqli_query($conn, $insert);
  die();
	}

	function parse_order_id($des){
		global $MEMO_PREFIX;
		$re = '/'.$MEMO_PREFIX.'\d+/mi';
		preg_match_all($re, $des, $matches, PREG_SET_ORDER, 0);

		if (count($matches) == 0 )
			return null;
		// Print the entire match result
		$orderCode = $matches[0][0];
		
		$prefixLength = strlen($MEMO_PREFIX);

		$orderId = intval(substr($orderCode, $prefixLength ));
		return $orderId ;

	}
	function getHeader(){
		$headers = array();

        $copy_server = array(
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        );

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
	}

?>