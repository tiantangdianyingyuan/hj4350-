<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/29
 * Time: 15:07
 */

namespace app\validators;


use app\models\CoreValidateCode;
use yii\validators\Validator;

class ValidateCodeValidator extends Validator
{
    public $validateCodeIdAttribute;
    public $mobileAttribute;

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $validateCodeIdAttribute = $this->validateCodeIdAttribute;
        $mobileAttribute = $this->mobileAttribute;
        $validateCodeId = $model->$validateCodeIdAttribute;
        $mobile = $model->$mobileAttribute;
        $coreValidateCode = CoreValidateCode::findOne([
            'id' => $validateCodeId,
            'target' => $mobile,
            'code' => $value,
            'is_validated' => CoreValidateCode::IS_VALIDATED_FALSE,
        ]);
        if (!$coreValidateCode) {
            $model->addError($attribute, "{$model->getAttributeLabel($attribute)}错误。");
        } else {
            $coreValidateCode->is_validated = CoreValidateCode::IS_VALIDATED_TRUE;
            $coreValidateCode->save();
        }
    }
}
