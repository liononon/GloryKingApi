<?php
namespace GloryKing\Module;

use GloryKing\Base\ElementBase;
use Library\FormValidator\Admin\AddElement;
use Library\FormValidator\FormValidator;

/**
 * 素材模块
 *
 * Class ElementModule
 * @package GloryKing\Module
 * @author jiangxianli
 * @created_at 2017-04-20 16:55:39
 */
class ElementModule extends Module
{
    /**
     * 获取素材
     *
     * @param array $condition
     * @return array|mixed
     * @author jiangxianli
     * @created_at 2017-04-20 17:14:59
     */
    public static function getElements($condition = [])
    {
        $by = array_get($condition, 'by', '');
        switch ($by) {
            case 'hot':
                //根据热度获取素材
                return ElementBase::getHotElement($condition);
                break;
            case 'hero':
                //根据英雄获取素材
                return ElementBase::getElementByHero($condition);
                break;
            case 'all':
                return ElementBase::getAllElement($condition);
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * 素材操作
     *
     * @param $condition
     * @param string $operate
     * @return \Library\ErrorMessage\ErrorMessage|mixed
     * @author jiangxianli
     * @created_at 2017-04-24 18:33:10
     */
    public static function elementOperate($condition, $operate = '')
    {
        switch ($operate) {
            case 'add':
                \Log::info('add');
                $form_validator = new FormValidator(new AddElement(), $condition);
                if ($form_validator->isFailed()) {
                    return $form_validator->getError();
                }

                return self::dbTransaction(function () use ($condition) {
                    ElementBase::addElement($condition);
                });
                break;
        }
    }
}
