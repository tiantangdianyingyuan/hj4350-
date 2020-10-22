<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\common;


use app\forms\mall\topic\CommonTopicForm;
use app\models\Model;
use app\models\TopicType;

class DiyTopicsForm extends Model
{
    public function getTopicsData($data, $topicTypes)
    {
        $topicIds = [];
        // 列表模式
        if ($data['style'] == 'list') {
            if ($data['cat_show']) {
                // 显示分类
                foreach ($data['list'] as $lItem) {
                    // 自定义专题
                    if ($lItem['custom']) {
                        foreach ($lItem['children'] as $child) {
                            $topicIds[] = $child['id'];
                        }
                    } else {
                        $sign = true;
                        foreach ($topicTypes as $cKey => $topicType) {
                            if ($topicType['cat_id'] == $lItem['cat_id']) {
                                $sign = false;
                                if ($topicType['number'] < $lItem['number']) {
                                    $topicTypes[$cKey]['number'] = $lItem['number'];
                                }
                            }
                        }
                        if ($sign) {
                            $arr['cat_id'] = $lItem['cat_id'];
                            $arr['number'] = $lItem['number'];
                            $topicTypes[] = $arr;
                        }
                    }
                }
            } else {
                // 不显示分类 自定义
                foreach ($data['topic_list'] as $tItem) {
                    $topicIds[] = $tItem['id'];
                }
            }
        }

        return [
            'topicIds' => $topicIds,
            'topicTypes' => $topicTypes
        ];
    }

    /**
     * 专题简易模式下的 默认列表
     * @throws \Exception
     */
    public function getNormalList()
    {
        if ($this->normalList) {
            return $this->normalList;
        }
        $form = new CommonTopicForm();
        $form->page_size = 10;
        $res = $form->getList();
        $this->normalList = $form->resetTopicList($res['list']);
        return $this->normalList;
    }

    public function getTopicById($topicIds)
    {
        if (!$topicIds) {
            return [];
        }

        $form = new CommonTopicForm();
        $form->topics_ids = $topicIds;
        $res = $form->getList();
        $newTopics = $form->resetTopicList($res['list']);

        return $newTopics;
    }

    public function getTopicByType($topicTypes)
    {
        foreach ($topicTypes as &$topicType) {
            $form = new CommonTopicForm();
            $form->type = $topicType['cat_id'];
            $res = $form->getList();
            $topicType['list'] = $form->resetTopicList($res['list']);
        }

        return $topicTypes;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function getNewTopics($data)
    {
        if ($data['style'] == 'normal') {
            // 普通模式
            $data['topic_list'] = $this->getNormalList();
        } elseif ($data['style'] == 'list') {
            // 列表模式
            if ($data['cat_show']) {
                // 显示分类
                foreach ($data['list'] as &$lItem) {
                    if ($lItem['custom']) {
                        // 自定义专题
                        $this->setTopicList($lItem['children'], $this->getListById());
                    } else {
                        // 默认列表专题
                        foreach ($this->getListByType() as $key => $topicType) {
                            if ($key == $lItem['cat_id']) {
                                $lItem['children'] = array_slice($topicType, 0, $lItem['number']);
                            }
                        }
                    }
                }
                unset($lItem);
            } else {
                // 不显示分类
                $this->setTopicList($data['topic_list'], $this->getListById());
            }
        } else {
            throw new \Exception('专题样式参数错误:' . $data['style']);
        }

        return $data;
    }

    private function setTopicList(&$topicList, $diyTopics)
    {
        // 不显示分类
        foreach ($topicList as &$item) {
            foreach ($diyTopics as $topic) {
                if ($topic['id'] == $item['id']) {
                    $item = $topic;
                    break;
                }
            }
        }
        unset($item);
    }

    public static $instance;
    public $idList = [];
    public $typeList = [];
    public $normalList;
    public $listById;
    public $listByType;
    

    public static function getInstance(array $config = [])
    {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new self($config);
        return self::$instance;
    }

    public function setIdList($id)
    {
        if (!in_array($id, $this->idList)) {
            array_push($this->idList, $id);
        }
    }

    public function setTypeList($type)
    {
        if (!in_array($type, $this->typeList)) {
            array_push($this->typeList, $type);
        }
    }

    /**
     * @return array
     * @throws \Exception
     * 根据id获取专题列表
     */
    public function getListById()
    {
        if ($this->listById) {
            return $this->listById;
        }
        if (count($this->idList) <= 0) {
            $this->listById = [];
            return [];
        }
        $form = new CommonTopicForm();
        $form->topics_ids = $this->idList;
        $form->page_size = count($this->idList);
        $res = $form->getList();
        $this->listById = $form->resetTopicList($res['list']);

        return $this->listById;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getListByType()
    {
        if ($this->listByType) {
            return $this->listByType;
        }
        if (count($this->typeList) <= 0) {
            return [];
        }
        $form = new CommonTopicForm();
        $form->type = $this->typeList;
        $form->page_size = count($this->typeList);
        $result = $form->getListByType();
        /* @var TopicType[] $typeList */
        $typeList = $result['list'];
        $newList = [];
        foreach ($typeList as $type) {
            $newList[$type->id] = $form->resetTopicList($type->topics);
        }
        $this->listByType = $newList;
        return $newList;
    }
}
