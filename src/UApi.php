<?php

namespace Laradocs\Uapi;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Laradocs\Uapi\Exceptions\HttpException;
use Laradocs\Uapi\Exceptions\MissingArgumentException;
use Laradocs\Uapi\Traits\HasSignature;
use Exception;
use Laradocs\Uapi\Utils\Json;
use GuzzleHttp\Exception\GuzzleException;

class UApi
{
    use HasSignature;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var string
     */
    protected $baseUri = 'http://uapis.ten.sdream.top/apis/api';

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected function client(): Client
    {
        return new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 1.5,
        ]);
    }

    /**
     * 发送短信
     *
     * @param  string  $mobile
     * @param  string  $content
     * @return array
     * @throws HttpException
     * @throws MissingArgumentException
     * @throws GuzzleException
     */
    public function sms(array $params): array
    {
        $this->checkRequireParameters(['mobile', 'content'], $params);
        try {
            $json = $this->client()
                ->post('sms', [
                    RequestOptions::JSON => [
                        'biz_content' => [
                            'mobile' => $params['mobile'],
                            'content' => $params['content'],
                        ],
                        'agent_id' => $this->config->getAgentId(),
                        'sign' => $this->sign($params),
                    ]
                ])->getBody()
                ->getContents();
            $data = Json::decode($json);

            return $data['code'] ? $data['data'] : throw new Exception($data['msg'], $data['code']);
        } catch (Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 实名认证
     *
     * @param  string  $cardno  身份证号码
     * @param  string  $name  姓名
     * @return array
     * @throws HttpException
     * @throws MissingArgumentException
     * @throws GuzzleException
     */
    public function idcard(array $params): array
    {
        $this->checkRequireParameters(['cardno', 'name'], $params);
        try {
            $json = $this->client()
                ->post('idcard', [
                        RequestOptions::JSON => [
                            'biz_content' => [
                                'cardno' => $params['cardno'],
                                'name' => $params['name'],
                            ],
                            'agent_id' => $this->config->getAgentId(),
                            'sign' => $this->sign($params),
                        ]
                    ]
                )->getBody()
                ->getContents();
            $data = Json::decode($json);

            return $data['code'] ? $data['data'] : throw new Exception($data['msg'], $data['code']);
        } catch (Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 联行号查询
     *
     * @param  string  $card  银行卡号
     * @param  string  $province  省份
     * @param  string  $city  城市名
     * @param  string  $key  关键字(非必填)
     * @return array
     * @throws GuzzleException
     * @throws HttpException
     * @throws MissingArgumentException
     */
    public function bankaps(array $params): array
    {
        $this->checkRequireParameters(['province', 'city', 'card'], $params);
        try {
            $json = $this->client()
                ->post(
                    'bankaps',
                    [
                        RequestOptions::JSON => [
                            'biz_content' => [
                                'card' => $params['card'],
                                'city' => $params['city'],
                                'province' => $params['province'],
                                'key' => $params['key'] ?? '',
                            ],
                            'agent_id' => $this->config->getAgentId(),
                            'sign' => $this->sign($params),
                        ]
                    ]
                )->getBody()
                ->getContents();
            $data = Json::decode($json);

            return $data['code'] ? $data['data'] : throw new Exception($data['msg'], $data['code']);
        } catch (Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 银行卡三要素认证
     *
     * @param string $accountNo 银行卡号
     * @param string $idCard 身份证号
     * @param string $idCard 姓名
     * @return array
     * @throws GuzzleException
     * @throws HttpException
     * @throws MissingArgumentException
     */

    public function bank3Check(array $params)
    {
        $this->checkRequireParameters(['accountNo','idCard','idCard'],$params);
        try {
            $json=$this->client()
                ->post(
                    'bank3Check',
            [
                RequestOptions::JSON=>[
                    'biz_content' =>[
                        'accountNo'=>$params['accountNo'],
                        'idCard'=>$params['idCard'],
                        'name'=>$params['name'],
                    ],
                    'agent_id' => $this->config->getAgentId(),
                    'sign' => $this->sign($params),
                ]
            ]
                )->getBody()
                ->getContents();
            $data=Json::decode($json);

            return $data['code'] ? $data['data'] : throw new Exception($data['msg'],$data['code']);
        }catch (Exception $e){
            throw new HttpException($e->getMessage(),$e->getCode(),$e);
        }
    }

    /**
     * 验证参数
     *
     * @param  array  $required
     * @param  array  $params
     * @return void
     * @throws MissingArgumentException
     */
    protected function checkRequireParameters(array $required, array $params): void
    {
        foreach ($required as $req) {
            if (! isset($params[$req])) {
                throw new MissingArgumentException(
                    sprintf('The %s parameter is required.', $req)
                );
            }
        }
    }
}
