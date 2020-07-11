<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<div class="row">
<div class="col-md-6 col-sm-6 col-xs-12"> 
<form id="add-form" action="<?=$APPLICATION->GetCurPage(false)?>" method="POST">
  <div class="form-group">
    <input type="text" class="form-control" name="city_name" value=""  placeholder="<?=GetMessage("PLACEHOLDER")?>">
    <input type="hidden" value="add_city" name="action">
  </div>
  <input type="submit" class="btn btn-primary" value="<?=GetMessage("ADD")?>">
</form>
</div>
</div>
<?if (!empty($arResult["ITEMS"])):?>
<?
    if ($arParams["DISPLAY_TOP_PAGER"]){
        ?><? echo $arResult["NAV_STRING"]; ?><?
    }
?>

    <div id="catalogList">
    <div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12"> 
    <table class="table table-bordered">
        <?foreach($arResult["ITEMS"] as $arElement):?>
        
        <tr>
        <td><?=$arElement["NAME"]?></td>
        <td><a class="btn btn-primary" href="<?=$APPLICATION->GetCurPage(false)?>?action=delete_city&city_id=<?=$arElement["ID"]?>"><?=GetMessage("DELETE")?></a></td>
        </tr>
        
        <?endforeach;?>
    </table>
    </div>
    </div>
    </div>
<?
    if ($arParams["DISPLAY_BOTTOM_PAGER"]){
        ?><? echo $arResult["NAV_STRING"]; ?><?
    }
?>

<?else:?>

<p><?=GetMessage("EMPTY_TEXT")?></p>

<?endif;?>
