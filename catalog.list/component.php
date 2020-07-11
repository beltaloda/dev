<?
    if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
        die();


    use Bitrix\Main,
        Bitrix\Main\Loader,
        Bitrix\Iblock;

    if(!\Bitrix\Main\Loader::includeModule("iblock")){
        ShowError("modules not installed!");
        return 0;
    }  
    
    global $APPLICATION, $USER;


    $arParams["DISPLAY_TOP_PAGER"] = $arParams["DISPLAY_TOP_PAGER"] == "Y";
    $arParams["DISPLAY_BOTTOM_PAGER"] = $arParams["DISPLAY_BOTTOM_PAGER"] != "N";
    $arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
    $arParams["PAGER_SHOW_ALWAYS"] = $arParams["PAGER_SHOW_ALWAYS"] == "Y";
    $arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"]);
    $arParams["PAGER_DESC_NUMBERING"] = $arParams["PAGER_DESC_NUMBERING"] == "Y";
    $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] = intval($arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]);
    $arParams["PAGER_SHOW_ALL"] = $arParams["PAGER_SHOW_ALL"] == "Y";
    $arParams["PAGE_ELEMENT_COUNT"] = !empty($arParams["PAGE_ELEMENT_COUNT"]) ? $arParams["PAGE_ELEMENT_COUNT"] : 30;


    if($arParams["DISPLAY_TOP_PAGER"] || $arParams["DISPLAY_BOTTOM_PAGER"]){

        CPageOption::SetOptionString("main", "nav_page_in_session", "N");

        $arNavParams = array(
            "nPageSize" => $arParams["PAGE_ELEMENT_COUNT"],
            "bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"],
            "bShowAll" => $arParams["PAGER_SHOW_ALL"],
        );

        $arNavigation = CDBResult::GetNavParams($arNavParams);

        if($arNavigation["PAGEN"] == 0 && $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] > 0){
            $arParams["CACHE_TIME"] = $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"];
        }

    }

    else{
        $arNavParams = array(
            "nTopCount" => $arParams["PAGE_ELEMENT_COUNT"],
            "bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"],
        );
        $arNavigation = false;

    }

    if (empty($arParams["PAGER_PARAMS_NAME"]) || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PAGER_PARAMS_NAME"])){
        $pagerParameters = array();
    }
    else
    {
        $pagerParameters = $GLOBALS[$arParams["PAGER_PARAMS_NAME"]];
        if (!is_array($pagerParameters)){
            $pagerParameters = array();
        }
    }


    $arParams["CACHE_GROUPS"] = trim($arParams["CACHE_GROUPS"]);
    if ($arParams["CACHE_GROUPS"] != "N"){
        $arParams["CACHE_GROUPS"] = "Y";
    }


    if (empty($arParams["ELEMENT_SORT_FIELD"]))
        $arParams["ELEMENT_SORT_FIELD"] = "sort";
    if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["ELEMENT_SORT_ORDER"]))
        $arParams["ELEMENT_SORT_ORDER"] = "asc";
    if (empty($arParams["ELEMENT_SORT_FIELD2"]))
        $arParams["ELEMENT_SORT_FIELD2"] = "id";
    if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["ELEMENT_SORT_ORDER2"]))
        $arParams["ELEMENT_SORT_ORDER2"] = "desc";

    $arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);

    if($arParams["IBLOCK_ID"] <= 0){

        ShowError(GetMessage('IBLOCK_NOT_SELECTED'));

        return false;

    }

    if($_POST["ajax"] == "Y" && isset($_POST['action']) && $_POST['action']=='add_city'){
        $el = new CIBlockElement;
        $APPLICATION->RestartBuffer(); 
        
        if(!$_POST['city_name']){      
            echo json_encode(array('type'=>'error','msg'=>GetMessage('ERR_ADD')));
            die();
        }
        
        $fields=array(
            "NAME"=>$_POST['city_name'],
            "ACTIVE"=>"Y",
            "IBLOCK_ID"=>$arParams["IBLOCK_ID"]
        );
        
        if(!$id=$el->Add($fields)){
            echo json_encode(array('type'=>'error','msg'=>$el->LAST_ERROR));
        }else{
            $rsElements = CIBlockElement::GetList(array(), array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],"=ID"=>$id), false, false, array('*'));
            $arElem=$rsElements->Fetch();
            
            echo json_encode(array('type'=>'success','msg'=>GetMessage("SUCCESS_ADD"),'html'=>"<tr id=\"city_{$arElem["ID"]}\" class=\"table-info\">
                        <td width=\"80%\">{$arElem["NAME"]}</td>
                        <td width=\"20%\"><a class=\"btn btn-primary\" href=\"javascript:void(0);\" onclick=\"city_del(this);return false;\" data-city_id=\"{$arElem["ID"]}\">".GetMessage("DELETE")."</a></td></tr>"));
        }
        die();    
    }
 
    if($_POST["ajax"] == "Y" && isset($_POST['action']) && $_POST['action']=='delete_city' && !empty($_POST['city_id'])){
        $APPLICATION->RestartBuffer();
        $id=intval($_POST['city_id']);

        if(CIBlockElement::Delete($id)){
            echo json_encode(array('type'=>'success'));
        }else{
            echo json_encode(array('type'=>'error','msg'=>GetMessage('DEL_ERR')));
        }
        
        die();
    }
    
    if (!isset($arParams["CACHE_TIME"])){
        $arParams["CACHE_TIME"] = 1285912;
    }

    $cacheID = array(
        "PAGER_PARAMS" => $pagerParameters,
        "NAVIGATION" => $arNavigation,
        "SITE_ID" => SITE_ID,
        "IBLOCK_ID"=>$arParams["IBLOCK_ID"],
    );

    $cacheDir = "/cities_list";


    $obCache = new CPHPCache();
    if($arParams["CACHE_TYPE"] != "N" && $obCache->InitCache($arParams["CACHE_TIME"], serialize($cacheID), $cacheDir)){
        $arResult = $obCache->GetVars();
        $arResult["CACHED"] = "Y";
    }

    elseif($obCache->StartDataCache()){
        
        $arResult = array();      
        $arSort = array();

        if(!empty($arParams["ELEMENT_SORT_FIELD"])){
            $arSort[$arParams["ELEMENT_SORT_FIELD"]] = $arParams["ELEMENT_SORT_ORDER"];
        }

        if(!empty($arParams["ELEMENT_SORT_FIELD2"])){
            $arSort[$arParams["ELEMENT_SORT_FIELD2"]] = $arParams["ELEMENT_SORT_ORDER2"];
        }

        $arFilter = array(
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "ACTIVE" => "Y",
        );

        $arSelect = array(
            "ID",
            "SORT",
            "NAME",
            "IBLOCK_ID"
        );
     
        $arResult["ITEMS"] = array();
        $usePageNavigation = true;

        $getListParams=array(
            'order' => $arSort, 
            'select' => $arSelect, 
            'filter' => $arFilter,
        );
        
        $totalPages = 0;
        $totalCount = 0;
        
        if ($arNavigation['SHOW_ALL'])
        {
            $usePageNavigation = false;
        }
        else
        {
            $arNavigation['PAGEN'] = (int)$arNavigation['PAGEN'];
            $arNavigation['SIZEN'] = (int)$arNavigation['SIZEN'];

            $getListParams['limit'] = $arNavigation['SIZEN'];
            $getListParams['offset'] = $arNavigation['SIZEN']*($arNavigation['PAGEN']-1);

            $countParams = [
                "filter"=>$getListParams['filter'],
                "select"=> [new Main\ORM\Fields\ExpressionField('CNT', 'COUNT(1)')]
            ];

            $countQuery = \Bitrix\Iblock\ElementTable::getList($countParams);

            $totalCount = $countQuery->fetch();
            $totalCount = (int)$totalCount['CNT'];
            unset($countQuery);

            if ($totalCount > 0)
            {
                $totalPages = ceil($totalCount/$arNavigation['SIZEN']);

                if ($arNavigation['PAGEN'] > $totalPages)
                    $arNavigation['PAGEN'] = $totalPages;

                $getListParams['limit'] = $arNavigation['SIZEN'];
                $getListParams['offset'] = $arNavigation['SIZEN']*($arNavigation['PAGEN']-1);
            }
            else
            {
                $arNavigation['PAGEN'] = 1;
                $getListParams['limit'] = $arNavigation['SIZEN'];
                $getListParams['offset'] = 0;
            }
        }

        global $CACHE_MANAGER;
        $CACHE_MANAGER->StartTagCache($cacheDir);
                
        $rsElements = new CDBResult(\Bitrix\Iblock\ElementTable::getList($getListParams));   

        while($arItem = $rsElements->Fetch()){
            $arResult["ITEMS"][$arItem["ID"]] = $arItem;                       
        }

        $CACHE_MANAGER->RegisterTag('iblock_id_'.$arParams["IBLOCK_ID"]);
        $CACHE_MANAGER->EndTagCache(); 
        
        if(!empty($arResult['ITEMS'])){
            
            $rsElements = new CDBResult();
            if ($usePageNavigation)
            {
                $rsElements->NavStart($arNavigation,$arNavigation['SHOW_ALL'],$arNavigation['PAGEN']);
                $rsElements->NavRecordCount = $totalCount;
                $rsElements->NavPageSize = $arNavParams['nPageSize'];
                $rsElements->bShowAll = $arNavParams['bShowAll'];
                $rsElements->NavPageCount = $totalPages;
                $rsElements->NavPageNomer = $arNavigation['PAGEN'];
                $arResult["NAV_STRING"] = $rsElements->GetPageNavStringEx(
                        $navComponentObject,
                        $arParams["PAGER_TITLE"],
                        $arParams["PAGER_TEMPLATE"],
                        $arParams["PAGER_SHOW_ALWAYS"]
                    );

            }else{
                
                if ((int)($arParams["PAGE_ELEMENT_COUNT"]))
                {
                    $rsElements->NavStart($arParams["PAGE_ELEMENT_COUNT"], false);
                }   
                         
            }
            
            $obCache->EndDataCache($arResult);
            unset($arResult);
        }else{

            $obCache->AbortDataCache();   
            
        }      
                
    }
    
$this->IncludeComponentTemplate();



        