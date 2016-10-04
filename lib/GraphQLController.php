<?php namespace GraphQLYii;

use yii\base\Controller;

class GraphQLController extends Controller {
    
    public function query($request)
    {
        $query = $request->get('query');
        $params = $request->get('params');
        
        if(is_string($params))
        {
            $params = json_decode($params, true);
        }
        
        return app('graphql')->query($query, $params);
    }
    
}
