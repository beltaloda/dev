<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12"> 
        <form id="add-form" action="<?=$APPLICATION->GetCurUri()?>" method="POST" onsubmit="city_add(this);return false;">
          <div class="form-group">
            <input type="text" class="form-control" name="city_name" value=""  placeholder="<?=GetMessage("PLACEHOLDER")?>">
            <input type="hidden" value="add_city" name="action">
            <input type="hidden" value="Y" name="ajax">
          </div>
          <input type="submit" class="btn btn-primary" value="<?=GetMessage("ADD")?>">
        </form>
    </div>
</div>


<div id="catalogList">
    <?if (!empty($arResult["ITEMS"])):?>
    <?
    if ($arParams["DISPLAY_TOP_PAGER"]){
    ?>
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12"> 
            <? echo $arResult["NAV_STRING"]; ?>
        </div>
    </div>
    <?
    }
    ?>  
    <div class="row">     
        <div class="col-md-6 col-sm-6 col-xs-12"> 
            <table class="table table-bordered" id="city_table">
                <?foreach($arResult["ITEMS"] as $arElement):?>
                
                <tr id="city_<?=$arElement["ID"]?>">
                    <td width="80%"><?=$arElement["NAME"]?></td>
                    <td width="20%"><a class="btn btn-primary" href="javascript:void(0);" onclick="city_del(this);return false;" data-city_id="<?=$arElement["ID"]?>"><?=GetMessage("DELETE")?></a></td>
                </tr>
                
                <?endforeach;?>
            </table>
        </div>
    </div>
    <?
    if ($arParams["DISPLAY_BOTTOM_PAGER"]){
    ?>
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12"> 
            <? echo $arResult["NAV_STRING"]; ?>
        </div>
    </div>
    <?
    }
    ?>    
    
<?else:?>
    <div class="col-lg-12"> 
        <p><?=GetMessage("EMPTY_TEXT")?></p>
    </div>
<?endif;?>
</div>