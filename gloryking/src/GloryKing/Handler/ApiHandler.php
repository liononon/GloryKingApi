<?php
namespace GloryKing\Handler;

use GloryKing\Module\CommonModule;
use GloryKing\Module\ElementModule;
use GloryKing\Module\HeroModule;
use GloryKing\Module\ThemeModule;
use Illuminate\Support\Collection;
use Library\ErrorMessage\ErrorMessage;
use Library\Helper;

/**
 * Api接口处理器
 *
 * Class ApiHandler
 * @package GloryKing\Handler
 * @author jiangxianli
 * @created_at 2017-04-20 15:36:10
 */
class ApiHandler extends Handler
{
    /**
     * 获取元素列表
     *
     * @param array $condition
     * @return array
     * @author jiangxianli
     * @created_at 2017-04-20 17:56:46
     */
    public static function getElementList($condition = [])
    {
        $by = array_get($condition, 'by', '');
        switch ($by) {
            case 'type':
            case 'hot':
            case 'hero':
            case 'recommend':
            case 'all':
                $response = ElementModule::getElements($condition);
                if (ErrorMessage::isError($response)) {
                    return $response;
                }
                $response->transform(function ($item) {
                    return [
                        'unique_id' => $item->unique_id,
                        'hero_id'   => $item->hero_id,
                        'url'       => $item->url,
                        'title'     => $item->title,
                        'poster'    => $item->image ? Helper::fullUrl($item->image->url) : '',
                        'play_num'  => $item->play_num,
                        'raise_num' => $item->raise_num,
                        'duration'  => Helper::formatDurationTime($item->duration)
                    ];
                });

                break;
            case 'detail':
                $response = ElementModule::getElements([
                    'by'        => 'detail',
                    'unique_id' => array_get($condition, 'unique_id', 0)
                ]);
                if (ErrorMessage::isError($response)) {
                    return $response;
                }
                if ($response) {
                    $response = [
                        'unique_id' => $response->unique_id,
                        'url'       => $response->url,
                        'hero_id'   => $response->hero_id,
                        'title'     => $response->title,
                        'poster'    => $response->image ? Helper::fullUrl($response->image->url) : '',
                        'play_num'  => $response->play_num,
                        'raise_num' => $response->raise_num,
                        'duration'  => Helper::formatDurationTime($response->duration)
                    ];
                }
                break;
            default:
                $response = new ErrorMessage('2003');
                break;
        }

        return self::apiResponse($response);
    }

    /**
     * 获取英雄列表
     *
     * @param array $condition
     * @return array
     * @author jiangxianli
     * @created_at 2017-04-20 18:03:21
     */
    public static function getHeroList($condition = [])
    {
        $response = HeroModule::getHeroList($condition);

        if (ErrorMessage::isError($response)) {
            return self::apiResponse($response);
        }

        $by = array_get($condition, 'by', '');
        switch ($by) {
            case 'type_id':
                $response->transform(function ($item) {
                    return [
                        'hero_id'   => $item->id,
                        'hero_name' => $item->name,
                        'image_url' => Helper::fullUrl($item->getImageSrc()),
                    ];
                });
                break;
            case 'type_hero':
                $response->transform(function ($hero_type) {
                    $hero = $hero_type->hero->map(function ($hero) {
                        return [
                            'hero_id'   => $hero->id,
                            'hero_name' => $hero->name,
                            'image_url' => Helper::fullUrl($hero->getImageSrc()),
                        ];
                    });

                    return [
                        'name' => $hero_type->name,
                        'hero' => $hero
                    ];
                });
                break;

        }

        return self::apiResponse($response);
    }

    /**
     * 获取所有的英雄类型
     *
     * @param array $condition
     * @return array
     * @author jiangxianli
     * @created_at 2017-04-25 14:19:12
     */
    public static function getHeroTypeList($condition = [])
    {
        $response = HeroModule::getAllHeroType($condition);

        return self::apiResponse($response);
    }

    /**
     * 英雄类型操作
     *
     * @param array $condition
     * @param string $operate
     * @return array
     * @author jiangxianli
     * @created_at 2017-04-21 16:01:27
     */
    public static function heroTypeOperate($condition = [], $operate = '')
    {
        $response = HeroModule::heroTypeOperate($condition, $operate);

        return self::apiResponse($response);
    }

    /**
     * 上传图片
     *
     * @param $file
     * @return mixed
     * @author jiangxianli
     * @created_at 2017-04-21 17:44:35
     */
    public static function uploadImage($file)
    {
        $response = CommonModule::uploadImage($file);

        return self::apiResponse($response);
    }

    /**
     * 英雄操作
     *
     * @param array $condition
     * @param string $operate
     * @return array
     * @author jiangxianli
     * @created_at 2017-04-24 14:59:41
     */
    public static function heroOperate($condition = [], $operate = '')
    {
        $response = HeroModule::heroOperate($condition, $operate);

        return self::apiResponse($response);
    }

    /**
     * 解析视频地址
     *
     * @param $from_url
     * @return array
     * @author jiangxianli
     * @created_at 2017-04-25 14:43:31
     */
    public static function parseVideoUrl($from_url)
    {
        $response = CommonModule::parseVideoUrl($from_url);

        return self::apiResponse($response);
    }

    /**
     * 素材操作
     *
     * @param array $condition
     * @return array
     * @author jiangxianli
     * @created_at 2017-04-26 16:05:24
     */
    public static function elementOperate($condition = [])
    {
        $by = array_get($condition, 'by', '');

        switch ($by) {
            case 'add_play_num':
            case 'add_raise_num':
                $response = ElementModule::elementOperate($condition, $by);
                break;
            default:
                $response = new ErrorMessage('2003');
                break;
        }

        return self::apiResponse($response);
    }

    /**
     * 获取专题列表
     *
     * @param array $condition
     * @return array|ErrorMessage|mixed
     * @author jiangxianli
     * @created_at 2017-05-02 11:35:06
     */
    public static function getThemeList($condition = [])
    {
        $by = array_get($condition, 'by', '');
        switch ($by) {
            case 'enabled':
                $response = ThemeModule::getThemeList($condition);
                if (ErrorMessage::isError($response)) {
                    return self::apiResponse($response);
                }
                $response->transform(function ($item) {
                    return [
                        'theme_id'        => $item->id,
                        'name'            => $item->name,
                        'theme_image_url' => $item->image ? Helper::fullUrl($item->image->url) : '',
                        'elements'        => $item->element->map(function ($item) {
                            return [
                                'unique_id' => $item->unique_id,
                                'url'       => $item->url,
                                'hero_id'   => $item->hero_id,
                                'title'     => $item->title,
                                'poster'    => $item->image ? Helper::fullUrl($item->image->url) : '',
                                'play_num'  => $item->play_num,
                                'raise_num' => $item->raise_num,
                                'duration'  => Helper::formatDurationTime($item->duration)
                            ];
                        })
                    ];
                });

                break;
            default:
                $response = new ErrorMessage('2003');
                break;
        }

        return self::apiResponse($response);
    }
}
