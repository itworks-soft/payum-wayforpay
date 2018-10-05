<?php
namespace Payum\WayForPay;

use Payum\WayForPay\Action\CaptureAction;
use Payum\WayForPay\Action\ConvertPaymentAction;
use Payum\WayForPay\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class WayForPayGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (!class_exists(\WayForPay::class)) {
            throw new \LogicException('You must install "wayforpay/php" library.');
        }

        $config->defaults([
            'payum.factory_name' => 'wayforpay',
            'payum.factory_title' => 'WayForPay',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'merchantAccount' => '',
                'merchantSecretKey' => ''
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['merchantAccount', 'merchantSecretKey'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $api = new \WayForPay($config['merchantAccount'], $config['merchantSecretKey']);

                return $api;
            };
        }
    }
}
