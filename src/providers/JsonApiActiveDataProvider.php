<?php

namespace insolita\fractal\providers;

use insolita\fractal\actions\HasResourceTransformer;
use insolita\fractal\pagination\JsonApiPaginator;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceInterface;
use Yii;
use yii\base\InvalidArgumentException;
use yii\data\ActiveDataProvider;

/**
 * The wrapper around ActiveDataProvider that helps to return valid League\Fractal\Resource\Collection
 */
class JsonApiActiveDataProvider extends ActiveDataProvider implements JsonApiDataProviderInterface
{
    use HasResourceTransformer;

    private $_pagination;

    public function init():void
    {
        parent::init();
        $this->initResourceTransformer();
    }

    /**
     * @return JsonApiPaginator|false
     * @throws \yii\base\InvalidConfigException
     */
    public function getPagination()
    {
        if ($this->_pagination === null) {
            $this->setPagination(['class' => JsonApiPaginator::class]);
        }

        return $this->_pagination;
    }

    /**
     * @param array|bool|JsonApiPaginator $value
     * @throws \yii\base\InvalidConfigException
     */
    public function setPagination($value):void
    {
        if (is_array($value)) {
            $config = ['class' => JsonApiPaginator::class];
            $this->_pagination = Yii::createObject(array_merge($config, $value));
            if (! $this->_pagination instanceof JsonApiPaginator) {
                throw new InvalidArgumentException('Only JsonApiPaginator instance or false allowed');
            }
        } elseif ($value instanceof JsonApiPaginator || $value === false) {
            $this->_pagination = $value;
        } else {
            throw new InvalidArgumentException('Only JsonApiPaginator instance, configuration array or false is allowed.');
        }
    }

    /**
     * @return \League\Fractal\Resource\ResourceInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function toCollection():ResourceInterface
    {
        $resource = new Collection($this->getModels(), $this->transformer, $this->resourceKey);
        $paginator = $this->getPagination();
        if ($paginator !== false) {
            $paginator->setItemsCount($this->getCount());
            $resource->setPaginator($this->getPagination());
        }
        return $resource;
    }
}
