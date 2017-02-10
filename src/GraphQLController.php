<?php namespace GraphQLYii;

use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

class GraphQLController extends Controller
{
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * @return mixed
     */
    public function actionIndex()
    {
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
}
