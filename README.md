## Start Project

You’ll start by editing this README file to learn how to edit a file in Bitbucket.


1. прописать composer install; (#composer install;php init;)
2. Прописать php init и выбрать 0 (если проект уже есть то делаем ставим 0 не перезиписиваем не чего)
3. Группируем composer install;php init;
4. В папке web переименовать _htaccess в .htaccess
5. настраеваем конфигурацию
6. Делаем php yii migrate
7. Раскомментировать в папке modules все namespace (DefaultQuery,I18nUrlManager,MyActiveRecord)
8. Сделать своего юзера по примере UserBack в models
---

## Настройка очередей
 
1. Установить очереди https://beanstalkd.github.io/download.html
2. Настраивать по этой доке https://www.yiiframework.com/extension/yiisoft/yii2-queue/doc/guide/2.0/ru/driver-beanstalk
3. Более расширенная дока по beanstalkd https://www.yiiframework.com/extension/yiisoft/yii2-queue/doc/guide/2.0/ru/usage
4. Иногда нужно запускать через php (`php yii queue/info`)
5. Запустить код php yii queue/run в **Supervisor** https://www.8host.com/blog/ustanovka-i-upravlenie-supervisor-na-servere-ubuntu-i-debian/