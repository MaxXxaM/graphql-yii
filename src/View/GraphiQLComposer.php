<?php

namespace GraphQLYii\View;

class GraphiQLComposer
{
    public function compose($view)
    {
        $view->graphqlPath = route('graphql.query');
    }
}
