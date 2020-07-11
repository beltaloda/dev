<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var string $componentPath
 * @var string $componentName
 * @var array $arCurrentValues
 * @global CUserTypeManager $USER_FIELD_MANAGER
 */

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock;
use Bitrix\Currency;

global $USER_FIELD_MANAGER;

if (!Loader::includeModule('iblock'))
	return;

$catalogIncluded = Loader::includeModule('catalog');
CBitrixComponent::includeComponentClass($componentName);
$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$iblockFilter = !empty($arCurrentValues['IBLOCK_TYPE'])
	? array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
	: array('ACTIVE' => 'Y');

$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch())
{
	$arIBlock[$arr['ID']] = '['.$arr['ID'].'] '.$arr['NAME'];
}
unset($arr, $rsIBlock, $iblockFilter);

$defaultValue = array('-' => GetMessage('CP_BCS_EMPTY'));


$arSort = CIBlockParameters::GetElementSortFields(
	array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
	array('KEY_LOWERCASE' => 'Y')
);


$arOfferSort = $arSort;


$arAscDesc = array(
	'asc' => GetMessage('IBLOCK_SORT_ASC'),
	'desc' => GetMessage('IBLOCK_SORT_DESC'),
);

$arComponentParameters = array(
	'GROUPS' => array(
		'SORT_SETTINGS' => array(
			'NAME' => GetMessage('SORT_SETTINGS'),
			'SORT' => 210
		),
	),
	'PARAMETERS' => array(
		'IBLOCK_TYPE' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('IBLOCK_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $arIBlockType,
			'REFRESH' => 'Y',
		),
		'IBLOCK_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('IBLOCK_IBLOCK'),
			'TYPE' => 'LIST',
			'ADDITIONAL_VALUES' => 'Y',
			'VALUES' => $arIBlock,
			'REFRESH' => 'Y',
		),
		'ELEMENT_SORT_FIELD' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ELEMENT_SORT_FIELD'),
			'TYPE' => 'LIST',
			'VALUES' => $arSort,
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => 'sort',
		),
		'ELEMENT_SORT_ORDER' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ELEMENT_SORT_ORDER'),
			'TYPE' => 'LIST',
			'VALUES' => $arAscDesc,
			'DEFAULT' => 'asc',
			'ADDITIONAL_VALUES' => 'Y',
		),
		'ELEMENT_SORT_FIELD2' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ELEMENT_SORT_FIELD2'),
			'TYPE' => 'LIST',
			'VALUES' => $arSort,
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => 'id',
		),
		'ELEMENT_SORT_ORDER2' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ELEMENT_SORT_ORDER2'),
			'TYPE' => 'LIST',
			'VALUES' => $arAscDesc,
			'DEFAULT' => 'desc',
			'ADDITIONAL_VALUES' => 'Y',
		),
		'INCLUDE_SUBSECTIONS' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS'),
			'TYPE' => 'LIST',
			'VALUES' => array(
				'Y' => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS_ALL'),
				'A' => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS_ACTIVE'),
				'N' => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS_NO'),
			),
			'DEFAULT' => 'Y',
		),
		'PAGE_ELEMENT_COUNT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('IBLOCK_PAGE_ELEMENT_COUNT'),
			'TYPE' => 'STRING',
			'HIDDEN' => isset($templateProperties['PRODUCT_ROW_VARIANTS']) ? 'Y' : 'N',
			'DEFAULT' => '18'
		),
		'LINE_ELEMENT_COUNT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('IBLOCK_LINE_ELEMENT_COUNT'),
			'TYPE' => 'STRING',
			'HIDDEN' => isset($templateProperties['PRODUCT_ROW_VARIANTS']) ? 'Y' : 'N',
			'DEFAULT' => '3'
		),
		'CACHE_TIME' => array('DEFAULT' => 36000000),
	),
);

CIBlockParameters::AddPagerSettings(
    $arComponentParameters,
    GetMessage('T_IBLOCK_DESC_PAGER_CATALOG'), //$pager_title
    true, //$bDescNumbering
    true, //$bShowAllParam
    true, //$bBaseLink
    $arCurrentValues['PAGER_BASE_LINK_ENABLE'] === 'Y' //$bBaseLinkEnabled
);
