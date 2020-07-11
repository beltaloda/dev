<?
    if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
        die();

    use Bitrix\Main,
        Bitrix\Main\Loader;

    global $APPLICATION, $DB;

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

        //clear session nav
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

    //catalog items sort params
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

    if (!isset($arParams["INCLUDE_SUBSECTIONS"]) || !in_array($arParams["INCLUDE_SUBSECTIONS"], array("Y", "A", "N"))){
        $arParams["INCLUDE_SUBSECTIONS"] = "Y";
    }

    $arParams["USE_MAIN_ELEMENT_SECTION"] = $arParams["USE_MAIN_ELEMENT_SECTION"] === "Y";
    $arParams["SHOW_ALL_WO_SECTION"] = $arParams["SHOW_ALL_WO_SECTION"] === "Y";
    $arParams["SET_LAST_MODIFIED"] = $arParams["SET_LAST_MODIFIED"] === "Y";

    
    if(isset($_POST['action']) && $_POST['action']=='add_city'){
        $el = new CIBlockElement;
        $fields=array(
            "NAME"=>$_POST['city_name'],
            "ACTIVE"=>"Y",
            "IBLOCK_ID"=>$arParams["IBLOCK_ID"]
        );
        
        if($id=$el->Add($fields)){
           ShowMessage(GetMessage('SUCCESS_ADD')); 
        }else{
           ShowError($el->LAST_ERROR); 
        }
    }
 
    if(isset($_REQUEST['action']) && $_REQUEST['action']=='delete_city' && !empty($_REQUEST['city_id'])){
        $id=intval($_REQUEST['city_id']);
        ///$res = \Bitrix\Iblock\ElementTable::delete($id);
        $res=CIBlockElement::Delete($id);
        var_dump($res);
       /* if (!$res->isSuccess())
        {
            ShowError(GetMessage('DEL_ERR'));  
        }*/

    }
       
    if (!isset($arParams["CACHE_TIME"])){
        $arParams["CACHE_TIME"] = 1285912;
    }

    $cacheID = array(
        "PAGER_PARAMS" => $pagerParameters,
        "NAVIGATION" => $arNavigation,
        "SITE_ID" => SITE_ID
    );

    $cacheDir = "/";

    $obExtraCache = new CPHPCache();
    if($arParams["CACHE_TYPE"] != "N" && $obExtraCache->InitCache($arParams["CACHE_TIME"], serialize($cacheID), $cacheDir)){

        $arResult = $obExtraCache->GetVars();
        $arResult["CACHED"] = "Y";
    }

    elseif($obExtraCache->StartDataCache()){

        if(!\Bitrix\Main\Loader::includeModule("iblock")){

            $obExtraCache->AbortDataCache(); 
            ShowError("modules not installed!");
            return 0;

        }
        
        $arResult = array();        
        $woSection = false;
        $arParams["SHOW_ALL_WO_SECTION"] = "Y";

        $arSort = array();

        if(!empty($arParams["ELEMENT_SORT_FIELD"])){
            $arSort[$arParams["ELEMENT_SORT_FIELD"]] = $arParams["ELEMENT_SORT_ORDER"];
        }

        if(!empty($arParams["ELEMENT_SORT_FIELD2"])){
            $arSort[$arParams["ELEMENT_SORT_FIELD2"]] = $arParams["ELEMENT_SORT_ORDER2"];
        }

        $arFilter = array(
            "INCLUDE_SUBSECTIONS" => ($arParams["INCLUDE_SUBSECTIONS"] == "N" ? "N" : "Y"),
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "CHECK_PERMISSIONS" => "N",
            "MIN_PERMISSION" => "R",
            "IBLOCK_LID" => SITE_ID,
            "ACTIVE" => "Y",
        );
        
        if ($arParams["INCLUDE_SUBSECTIONS"] == "A"){
            $arFilter["SECTION_GLOBAL_ACTIVE"] = "Y";
        }

        elseif(!$arParams["SHOW_ALL_WO_SECTION"]){
            $arFilter["SECTION_ID"] = 0;
        }

        $arSelect = array(
            "ID",
            "SORT",
            "NAME",
            "IBLOCK_ID"
        );


        global $CACHE_MANAGER;
        $CACHE_MANAGER->StartTagCache($cacheDir);
                    
        $arResult["ITEMS"] = array();
        $rsElements = CIBlockElement::GetList($arSort, $arFilter, false, $arNavParams, $arSelect);
        
        while($arItem = $rsElements->GetNext()){
            
            $arResult["ITEMS"][$arItem["ID"]] = $arItem;
            $CACHE_MANAGER->RegisterTag("element_".$arItem["IBLOCK_ID"]);
            unset($arItem);
        }
        
        $CACHE_MANAGER->RegisterTag('catalog_'.$arParams["IBLOCK_ID"]);
        $CACHE_MANAGER->EndTagCache();

        if(!empty($arResult['ITEMS'])){

            $arResult["NAV_STRING"] = $rsElements->GetPageNavStringEx(
                $navComponentObject,
                $arParams["PAGER_TITLE"],
                $arParams["PAGER_TEMPLATE"],
                $arParams["PAGER_SHOW_ALWAYS"],
                $this
            );

            $arResult["NAV_CACHED_DATA"] = null;
            $arResult["NAV_NUM_PAGE"] = $rsElements->NavNum;
            $arResult["NAV_PARAM"] = $navComponentParameters;

            $obExtraCache->EndDataCache($arResult);
            unset($obExtraCache);
        
        }else{
            
            $obExtraCache->AbortDataCache();  
            
        }
        
                   
     }
     
$this->IncludeComponentTemplate();


        