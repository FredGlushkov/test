<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (CModule::IncludeModule("sale") && CModule::IncludeModule("main")):
	$tm = ConvertTimeStamp(time()-2592000); // Дата 30 дней назад
	$dbBasketItems = CSaleBasket::GetList(array("NAME" => "ASC"), array("LID" =>"s1", "ORDER_ID"=>NULL, ">=DATE_UPDATE" => $tm, "DELAY"=>"Y"));
	/* 
	  Здесь возникают уточняющие вопросы - отложенные товары - это вы имеете в виду как я понял именно товары со статусом "Отложен"
	  "При этом нужно проверить, чтобы в список не попали изделия, которые присутствуют в заказах пользователя за последний месяц" - 
	  если я правильно понял задание, это решается установкой "ORDER_ID"=>NULL и здесь вы не имеете в виду, что ВООБЩЕ не нужно выводить наименование товара, 
	  если таковой был заказан за данный период
	*/
	$ar_users = array(); // Массив Пользователь=>Список товаров
	while ($arItems = $dbBasketItems->Fetch())
	{
		// echo $arItems["USER_ID"]." - ".$arItems["NAME"]."<br/>";  
		if(!isset($ar_users[$arItems["USER_ID"]])) $ar_users[$arItems["USER_ID"]] = $arItems["NAME"].PHP_EOL; // формируем список товаров для юзеря
			else $ar_users[$arItems["USER_ID"]].=$arItems["NAME"].PHP_EOL;
	}	
	// Отправим извещения пользователям
	// Был создан тпп почтового события и соответствующий почтовый шаблон
	foreach($ar_users as $u => $goods) {
		$rsUser = CUser::GetByID($u);
		$arUser = $rsUser->Fetch();
		CEvent::SendImmediate("BASKET_NOTICE", "s1", array("USER_MAIL"=>$arUser["EMAIL"], "NAME"=>$arUser["NAME"]." ".$arUser["LAST_NAME"], "GOODS"=>$goods));
	}
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>