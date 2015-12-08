<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (CModule::IncludeModule("sale") && CModule::IncludeModule("main")):
	$tm = ConvertTimeStamp(time()-2592000); // ���� 30 ���� �����
	$dbBasketItems = CSaleBasket::GetList(array("NAME" => "ASC"), array("LID" =>"s1", "ORDER_ID"=>NULL, ">=DATE_UPDATE" => $tm, "DELAY"=>"Y"));
	/* 
	  ����� ��������� ���������� ������� - ���������� ������ - ��� �� ������ � ���� ��� � ����� ������ ������ �� �������� "�������"
	  "��� ���� ����� ���������, ����� � ������ �� ������ �������, ������� ������������ � ������� ������������ �� ��������� �����" - 
	  ���� � ��������� ����� �������, ��� �������� ���������� "ORDER_ID"=>NULL � ����� �� �� ������ � ����, ��� ������ �� ����� �������� ������������ ������, 
	  ���� ������� ��� ������� �� ������ ������
	*/
	$ar_users = array(); // ������ ������������=>������ �������
	while ($arItems = $dbBasketItems->Fetch())
	{
		// echo $arItems["USER_ID"]." - ".$arItems["NAME"]."<br/>";  
		if(!isset($ar_users[$arItems["USER_ID"]])) $ar_users[$arItems["USER_ID"]] = $arItems["NAME"].PHP_EOL; // ��������� ������ ������� ��� �����
			else $ar_users[$arItems["USER_ID"]].=$arItems["NAME"].PHP_EOL;
	}	
	// �������� ��������� �������������
	// ��� ������ ��� ��������� ������� � ��������������� �������� ������
	foreach($ar_users as $u => $goods) {
		$rsUser = CUser::GetByID($u);
		$arUser = $rsUser->Fetch();
		CEvent::SendImmediate("BASKET_NOTICE", "s1", array("USER_MAIL"=>$arUser["EMAIL"], "NAME"=>$arUser["NAME"]." ".$arUser["LAST_NAME"], "GOODS"=>$goods));
	}
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>