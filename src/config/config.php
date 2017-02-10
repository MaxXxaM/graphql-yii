<?php

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
