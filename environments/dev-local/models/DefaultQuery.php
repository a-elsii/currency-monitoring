<?php

// namespace app\models;

use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * This is the ActiveQuery class for All classes.
 *
 * @see AppInt
 * @see User
 */
class DefaultQuery extends \yii\db\ActiveQuery
{
    public $isAdmin = null;

    public function init()
    {
        parent::init();

        if(false) {
            $tn = $this->getPrimaryTableName();
            if($this->isAdmin === false) $this->andWhere(["$tn.status_del" => 0]);
        }
    }

    public function published() {
        $tn = $this->getPrimaryTableName();
        return $this->andWhere(["$tn.status_del" => 0]);
    }

    public function byId($id)
    {
        if($id === false) return $this;
        $tn = $this->getPrimaryTableName();
        return $this->andWhere(["$tn.id" => $id]);
    }


    /**
     * @inheritdoc
     * @return ActiveRecord[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ActiveRecord|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function getPrimaryTablePrimaryKey ()
    {
        /** @var ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        $schema = $modelClass::getTableSchema();
        return $schema->primaryKey;
    }

    public function alias ( $alias )
    {
        throw new Exception("Использование этой функции сломано.");
    }
}
