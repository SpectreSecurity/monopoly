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
		$gdeal = $gsession -> UserDealOpen($user_id, $deal_opponent_user_id, $deal_payment, $deal_holder_property_set, $deal_opponent_property_set);

		if ($gdeal != NULL) {
			echo "GDeal opened " . $gdeal -> GetDealId() . '<br>';
		} else {
			echo "GDeal open failed" . '<br>';
		}
		break;
	case 'acceptdeal' :
		$deal_id = us_GetDealId();
		$gdeal = & $gsession->getGDeal($deal_id);
		if ($gdeal !=NULL) {
			if ($gdeal -> UserAccept($user_id)) {
				echo "GDeal accepted " . $gdeal -> GetDealId() . '<br>';
			} else {
				echo "GDeal accept failed " . '<br>';
			}
		}
		break;
	case 'rejectdeal' :
		$deal_id = us_GetDealId();
		$gdeal = & $gsession->getGDeal($deal_id);
		if ($gdeal !=NULL) {
			if ($gdeal -> UserReject($user_id)) {
				echo "GDeal rejected " . $gdeal -> GetDealId() . '<br>';
			} else {
				echo "GDeal reject failed " . '<br>';
			}
		}
		break;
	case 'canceldeal' :
		$deal_id = us_GetDealId();
		$gdeal = & $gsession->getGDeal($deal_id);
		if ($gdeal !=NULL) {
			if ($gdeal -> UserCancel($user_id)) {
				echo "GDeal canceled " . $gdeal -> GetDealId() . '<br>';
			} else {
				echo "GDeal cancel failed " . '<br>';
			}
		}
		break;
}

//if (isset($deal_holder_property_set[0]))
//	echo "deal_holder_property_set[0]=" .$deal_holder_property_set[0]. '<br>';
//print_r($deal_holder_property_set);

//print_r($_GET);
?>