# OAuth2.0 Library

参考引用（鸣谢）：https://github.com/bshaffer/oauth2-server-php

说明：
利用php的可传递callable类型参数的特性，将storage部分提取为函数绑定的机制。


示例代码：



```
public function auth()
    {
        $requests = [
            'response_type' => I('get.response_type'),
            'client_id' => I('get.client_id'),
            'redirect_uri' => I('get.redirect_uri'),
            'scope' => I('get.scope'),
            'state' => I('get.state'),
        ];

        $AuthServer = new AuthServer();
        try {
            $handler = $AuthServer->authorize($requests);
        } catch (\Exception $e) {
            $AuthServer->responseException($e);
            die;
        }

        $model = new OauthClientsModel();
        $clientDetails = $model->where([
            'client_id' => $requests['client_id'],
        ])->find();
        if (!$clientDetails) {
            $AuthServer->setError(403, "unrecognized client.");
        }

        $handler->registerCallback(
            'createAuthCode',
            function ($requests, $code) use ($user_id) {
                $model = new OauthAuthCodeModel();
                $expire = $model->where([
                    'user_id' => $user_id,
                    'client_id' => $requests['client_id'],
                    'expires' => ['egt', self::getCompareDatetime(self::AUTH_CODE_EXPIRE)]
                ])->field('authorization_code')->find();
                if ($expire) {
                    $code = $expire['authorization_code'];
                }
                $data = [
                    'authorization_code' => $code,
                    'client_id' => $requests['client_id'],
                    'redirect_uri' => $requests['redirect_uri'],
                    'scope' => $requests['scope'],
                    'user_id' => $user_id,
                ];
                return $model->add($data, [], true) ? $code : false;
            });

        $response = $AuthServer->handleAuthorizeRequest(
            $handler,
            $clientDetails
        );

        $response->send();
    }
```