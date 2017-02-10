# Yii2 GraphQL

Данное расширение позволяет реализовать GraphQL API в framework Yii2. Данное расширение основано на следующем [расширении](https://github.com/webonyx/graphql-php). Более подробная информация о GraphQL - [GraphQL Introduction](http://facebook.github.io/react/blog/2015/05/01/graphql-introduction.html).


## Установка

#### Зависимости:

* [Базовое расширение GraphQL PHP](https://github.com/webonyx/graphql-php)


**1-** Установка пакета через Composer. Добавьте следующий код в файл `composer.json`.
```json
{
	"require": {
		"maxxxam/graphql": "~0.2"
	}
}
```

**2-** Запустите установку пакетов Composer

```bash
$ composer install
```

или обновите вместе с существующими пакетами

```bash
$ composer update
```

### Yii2

**1-** Добавьте компонент в `config/main.php` file
```php
'graphql' => [
    class' => 'GraphQLYii\GraphQL',
],
```

**2-** Создайте action Controller для обработки пользовательского запроса

```php
public function actionIndex(){
    Yii::$app->response->format = Response::FORMAT_JSON;

    $data = Yii::$app->request->post();

    $query = isset($data['query']) ? str_replace(["\r","\n"], "", $data['query']) : null;
    $params = isset($data['variables']) ? str_replace(["\r","\n"], "", $data['variables']) : null;

    /** @var GraphQL $GraphQL */
    $GraphQL = \Yii::$app->get('graphql');

    $result = $GraphQL->query($query, $params);

    if (!empty($result['errors'])){
        Yii::$app->response->setStatusCode(400);
    }

    Yii::$app->response->headers->add('Content-Length', strlen(json_encode($result)));
    Yii::$app->response->headers->add('Content-Type', 'application/json');

    $stream = fopen('php://memory','wb');
    fwrite($stream, json_encode($result));
    rewind($stream);

    Yii::$app->response->stream = $stream;
    Yii::$app->response->send();

    return true;

}
```

**3-** В качестве авторизации используется oAuth2 сервер [Filsh/yii2-oauth2-server](https://github.com/Filsh/yii2-oauth2-server)

**4-** Добавьте поведение в Controller

```php
public function behaviors()
{
    Yii::$app->controller->enableCsrfValidation = false;
    return ArrayHelper::merge(parent::behaviors(), [
        'authenticator' => [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                ['class' => HttpBearerAuth::className()],
                ['class' => QueryParamAuth::className(), 'tokenParam' => 'accessToken'],
            ]
        ],
        'exceptionFilter' => [
            'class' => ErrorToExceptionFilter::className()
        ],
    ]);
}
```

