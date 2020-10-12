## add for Schema.org

1. сгенерить модели `php yii schema/models/generate`
2. Добавить в хедер
2.1 add use simialbi\yii2\schemaorg\helpers\JsonLDHelper;
2.2 <?php JsonLDHelper::render(); ?>

## В нужной части кода использовать по примеру
use simialbi\yii2\schemaorg\models\Person;
$person = new Person();
$person->name = 'George Bush';
$person->disambiguatingDescription = '41st President of the United States';
JsonLDHelper::add($person);

---