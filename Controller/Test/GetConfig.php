<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Controller\Test;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use RicardoMartins\PagBank\Api\Connect\ConnectInterface;
use RicardoMartins\PagBank\Gateway\Config\Config;
use RicardoMartins\PagBank\Gateway\Config\ConfigBoleto;
use RicardoMartins\PagBank\Gateway\Config\ConfigCc;
use RicardoMartins\PagBank\Gateway\Config\ConfigQrCode;

class GetConfig implements HttpGetActionInterface
{
    public const XML_PATH_PAYMENT_PAGBANK_CC_ACTIVE = 'payment/' . ConfigCc::METHOD_CODE . '/active';

    public const XML_PATH_PAYMENT_PAGBANK_CC_INSTALLMENTS_OPTIONS = 'payment/' . ConfigCc::METHOD_CODE . '/installments_options';
    public const XML_PATH_PAYMENT_PAGBANK_CC_CC_3DS_ACTIVE = 'payment/' . ConfigCc::METHOD_CODE . '/cc_3ds';
    public const XML_PATH_PAYMENT_PAGBANK_CC_CC_3DS_ALLOW_CONTINUE = 'payment/' . ConfigCc::METHOD_CODE . '/cc_3ds_allow_continue';

    public const XML_PATH_PAYMENT_PAGBANK_BOLETO_ACTIVE = 'payment/' . ConfigBoleto::METHOD_CODE . '/active';

    public const XML_PATH_PAYMENT_PAGBANK_BOLETO_EXPIRATION = 'payment/' . ConfigBoleto::METHOD_CODE . '/expiration_time';

    public const XML_PATH_PAYMENT_PAGBANK_PIX_ACTIVE = 'payment/' . ConfigQrCode::METHOD_CODE . '/active';

    public const XML_PATH_PAYMENT_PAGBANK_PIX_EXPIRATION = 'payment/' . ConfigQrCode::METHOD_CODE . '/expiration_time';

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Curl
     */
    private Curl $curl;

    public function __construct(
        Config $config,
        JsonFactory $jsonFactory,
        ScopeConfigInterface $scopeConfig,
        Curl $curl
    ) {
        $this->config = $config;
        $this->resultJsonFactory = $jsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): ResultInterface|ResponseInterface
    {
        $resultJson = $this->resultJsonFactory->create();

        $info = array(
            'platform' => $this->config->getMagentoPlatform(),
            'platform_version' => substr($this->config->getMagentoVersion(), 0, 1),
            'module_version' => $this->config->getModuleVersion(),
            'connect_key' => strlen($this->config->getConnectKey()) == 40 ? 'Good' : 'Wrong size',
            'sandbox_active' => $this->config->isSandbox(),
            'key_validate'  => $this->validateKey(),
            'settings'      => $this->getConfig()
        );

        $resultJson->setData($info);

        return $resultJson;
    }

    /**
     * Validate public key
     *
     * @return string|null
     */
    private function validateKey(): ?string
    {
        $response = null;
        $pubKey = $this->config->getPublicKey();

        try {
            if (empty($pubKey)) {
                return 'Public Key is empty.';
            }

            $url = ConnectInterface::WS_ENDPOINT_PUBLIC_KEY_VALIDATE;
            if ($this->config->isSandbox()) {
                $url .= '?isSandbox=1';
            }
            $this->curl->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->getConnectKey()
            ]);
            $this->curl->get($url);
            $response = $this->curl->getBody();
        } catch (\Exception $e) {}

        if ($this->curl->getStatus() != 200) {
            return 'Error on request the public key.';
        }

        $response = json_decode($response, true);
        if (!isset($response['public_key'])) {
            return 'Error in the response of the public key.';
        }

        return $response['public_key'] == $pubKey ? 'Valid' : 'Invalid';
    }

    /**
     * @return array
     */
    private function getConfig(): array
    {
        return [
            'document_from' => $this->config->getDocumentFrom(),
            'debug' => $this->config->isDebugActived(),
            'cc' => [
                'enabled' => $this->scopeConfig->getValue(
                    self::XML_PATH_PAYMENT_PAGBANK_CC_ACTIVE,
                    ScopeInterface::SCOPE_STORE
                ),
                'cc_installments_options' => $this->scopeConfig->getValue(
                    self::XML_PATH_PAYMENT_PAGBANK_CC_INSTALLMENTS_OPTIONS,
                    ScopeInterface::SCOPE_STORE
                ),
                'cc_3ds' => $this->scopeConfig->getValue(
                    self::XML_PATH_PAYMENT_PAGBANK_CC_CC_3DS_ACTIVE,
                    ScopeInterface::SCOPE_STORE
                ),
                'cc_3ds_allow_continue' => $this->scopeConfig->getValue(
                    self::XML_PATH_PAYMENT_PAGBANK_CC_CC_3DS_ALLOW_CONTINUE,
                    ScopeInterface::SCOPE_STORE
                ),
            ],
            'boleto' => [
                'enabled' => $this->scopeConfig->getValue(
                    self::XML_PATH_PAYMENT_PAGBANK_BOLETO_ACTIVE,
                    ScopeInterface::SCOPE_STORE
                ),
                'boleto_expiration_time_days' => $this->scopeConfig->getValue(
                    self::XML_PATH_PAYMENT_PAGBANK_BOLETO_EXPIRATION,
                    ScopeInterface::SCOPE_STORE
                )
            ],
            'pix' => [
                'enabled' => $this->scopeConfig->getValue(
                    self::XML_PATH_PAYMENT_PAGBANK_PIX_ACTIVE,
                    ScopeInterface::SCOPE_STORE
                ),
                'pix_expiration_time_minutes' => $this->scopeConfig->getValue(
                    self::XML_PATH_PAYMENT_PAGBANK_PIX_EXPIRATION,
                    ScopeInterface::SCOPE_STORE
                )
            ]
        ];
    }
}
