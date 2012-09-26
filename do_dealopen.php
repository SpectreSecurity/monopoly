<?php
require_once ('core/core.php');
ConnectDB();

$user_id = GetCurrentUserId();
$gsession = GetCurrentGSession();
$gsession_id = $gsession -> gsession_id;
$action = us_GetCurrentAction();

switch ($action) {
	case 'opendeal' :
		$deal_opponent_user_id = us_GetDealOpponentUserId();
		$deal_payment = us_GetDealPayment();
		$deal_holder_property_set = us_GetDealHolderPropertySet();
		$deal_opponent_property_set = us_GetDealOpponentPropertySet();
		$deal = $gsession -> UserDealOpen($user_id, $deal_opponent_user_id, $deal_payment, $deal_holder_property_set, $deal_opponent_property_set);

		if ($deal != NULL) {
			echo "GDeal created " . $deal -> GetDealId() . '<br>';
		} else {
			echo "GDeal failed" . '<br>';
		}
		break;
	case 'acceptdeal' :
		echo "i equals 1";
		break;
	case 'rejectdeal' :
		echo "i equals 2";
		break;
}

//if (isset($deal_holder_property_set[0]))
//	echo "deal_holder_property_set[0]=" .$deal_holder_property_set[0]. '<br>';
//print_r($deal_holder_property_set);

//print_r($_GET);
?>