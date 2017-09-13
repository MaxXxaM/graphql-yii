# GraphQL Yii2

This extension allows you to implement GraphQL API in Yii2. This extension based on [extension GraphQL webonyx](https://github.com/webonyx/graphql-php). More information about GraphQL - [GraphQL Introduction](http://facebook.github.io/react/blog/2015/05/01/graphql-introduction.html).


## Install

#### Dependency:

* [webonyx/graphql-php](https://github.com/webonyx/graphql-php)
* [filsh/yii2-oauth2-server](https://github.com/Filsh/yii2-oauth2-server)


**1** Install by Composer. Add this config into `composer.json`.
```json
{
	"require": {
		"maxxxam/graphql": "~0.2"
	}
}
```

**2-** Start composer to install new dependency

```bash
$ composer install
```

or update it

```bash
$ composer update
```

### Config Yii2

**1-** Add component graphql into `config.php`
```php
'graphql' => [
    'graphql' => require(__DIR__ . DIRECTORY_SEPARATOR . 'require' . DIRECTORY_SEPARATOR . 'graphql.php')
],
```

**2-** Переместите config `./src/config/require` в `common/config/require`. Отредактируйте конфигурацию согласно вашему окружению.
Объекты Types, Queries, Mutations могут быть загружены вручную (заданием в конфигурации), либо автоматически из заданных директорий. 
Пример конфигурации `graphql.php` приведен ниже: 
```php

$appNamespace = 'app\modules\graphql';
$graphqlDir = dirname(dirname(__DIR__)) . '/modules/graphql';

return [
    'class' => 'GraphQLYii\GraphQL',
    'namespace' => $appNamespace,
    'graphqlDir' => $graphqlDir,
    'types' => [
        'UserType' => $appNamespace . '\types\UserType',
        'AccessType' => $appNamespace . '\types\AccessType',
        'ImageType' => $appNamespace . '\types\ImageType',
        'EventType' => $appNamespace . '\types\EventType',
        'TrophyType' => $appNamespace . '\types\TrophyType',
        'TeamType' => $appNamespace . '\types\TeamType',
        'AlbumType' => $appNamespace . '\types\AlbumType',
        'FriendType' => $appNamespace . '\types\FriendType',
        'SportType' => $appNamespace . '\types\SportType',
    ],
    'queries' => [
        'UsersQuery' => $appNamespace . '\query\user\UsersQuery',
        'UserQuery' => $appNamespace . '\query\user\UserQuery'
    ],
    'mutations' => [
        'AddFriendMutation' => $appNamespace . '\mutation\user\AddFriendMutation',
        'RemoveFriendMutation' => $appNamespace . '\mutation\user\RemoveFriendMutation',
        'SignupMutation' => $appNamespace . '\mutation\user\SignupMutation',
    ], 
    'typesPath' => '/types',
    'queriesPath' => '/queries',
    'mutationsPath' => '/mutations',
    'subscriptionPath' => '/subscription',
];
``` 

**3-** Создайте новый action для обработки пользовательского запроса

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

**4-** В качестве авторизации используется oAuth2 сервер [Filsh/yii2-oauth2-server](https://github.com/Filsh/yii2-oauth2-server)

**5-** Добавьте поведение в Controller

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

